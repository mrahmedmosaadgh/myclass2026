<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationPortalController extends Controller
{
    /**
     * Display the documentation portal
     */
    public function index(Request $request)
    {
        // Get database documentation
        $dbDocs = Documentation::with('author')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get file-based documentation
        $fileDocs = $this->scanDocumentationFiles();

        // Get statistics
        $stats = $this->getDocumentationStats($dbDocs, $fileDocs);

        // Get categories
        $categories = $this->getCategories($dbDocs, $fileDocs);

        return Inertia::render('DocumentationPortal/Index', [
            'dbDocs' => $dbDocs,
            'fileDocs' => $fileDocs,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'type' => $request->input('type'),
            ],
        ]);
    }

    /**
     * Search documentation
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $category = $request->input('category', '');
        $type = $request->input('type', '');

        // Search database docs
        $dbQuery = Documentation::with('author')->where('status', 'published');

        if ($searchTerm) {
            $dbQuery->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('tags', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($type) {
            $dbQuery->where('type', $type);
        }

        $dbDocs = $dbQuery->get();

        // Search file docs
        $fileDocs = $this->scanDocumentationFiles();
        if ($searchTerm) {
            $fileDocs = collect($fileDocs)->filter(function($doc) use ($searchTerm) {
                return Str::contains(strtolower($doc['title']), strtolower($searchTerm)) ||
                       Str::contains(strtolower($doc['content']), strtolower($searchTerm));
            })->values()->all();
        }

        return response()->json([
            'dbDocs' => $dbDocs,
            'fileDocs' => $fileDocs,
        ]);
    }

    /**
     * Get file content
     */
    public function getFileContent(Request $request)
    {
        $filePath = $request->input('path');
        $fullPath = base_path($filePath);

        if (!File::exists($fullPath) || !Str::startsWith($filePath, 'docs/')) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $content = File::get($fullPath);

        return response()->json([
            'content' => $content,
            'path' => $filePath,
        ]);
    }

    /**
     * Scan documentation files in docs folder
     */
    private function scanDocumentationFiles()
    {
        $docsPath = base_path('docs');
        $files = [];

        if (!File::exists($docsPath)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($docsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);

                $content = File::get($file->getPathname());
                $title = $this->extractTitleFromMarkdown($content, $file->getFilename());
                $category = $this->getCategoryFromPath($relativePath);

                $files[] = [
                    'id' => md5($relativePath),
                    'title' => $title,
                    'path' => $relativePath,
                    'category' => $category,
                    'type' => 'file',
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'content' => Str::limit($content, 500),
                    'excerpt' => $this->extractExcerpt($content),
                ];
            }
        }

        return $files;
    }

    /**
     * Extract title from markdown content
     */
    private function extractTitleFromMarkdown($content, $filename)
    {
        // Look for first # heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback to filename without extension
        return Str::title(str_replace(['-', '_', '.md'], [' ', ' ', ''], $filename));
    }

    /**
     * Get category from file path
     */
    private function getCategoryFromPath($path)
    {
        $parts = explode('/', $path);
        if (count($parts) > 2) {
            return Str::title($parts[1]); // docs/offline/file.md -> Offline
        }
        return 'General';
    }

    /**
     * Extract excerpt from content
     */
    private function extractExcerpt($content)
    {
        // Remove markdown headers and get first paragraph
        $content = preg_replace('/^#+\s+.+$/m', '', $content);
        $content = trim($content);

        $lines = explode("\n", $content);
        $excerpt = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !Str::startsWith($line, ['```', '---', '|'])) {
                $excerpt = $line;
                break;
            }
        }

        return Str::limit($excerpt, 200);
    }

    /**
     * Get documentation statistics
     */
    private function getDocumentationStats($dbDocs, $fileDocs)
    {
        return [
            'total' => count($dbDocs) + count($fileDocs),
            'database' => count($dbDocs),
            'files' => count($fileDocs),
            'categories' => count($this->getCategories($dbDocs, $fileDocs)),
            'recent' => $dbDocs->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Get all categories
     */
    private function getCategories($dbDocs, $fileDocs)
    {
        $categories = [];

        // Database doc categories
        foreach ($dbDocs as $doc) {
            $type = $doc->type ?? 'general';
            if (!isset($categories[$type])) {
                $categories[$type] = ['name' => Str::title($type), 'count' => 0, 'type' => 'database'];
            }
            $categories[$type]['count']++;
        }

        // File doc categories
        foreach ($fileDocs as $doc) {
            $category = strtolower($doc['category']);
            if (!isset($categories[$category])) {
                $categories[$category] = ['name' => $doc['category'], 'count' => 0, 'type' => 'file'];
            }
            $categories[$category]['count']++;
        }

        return array_values($categories);
    }
}
