<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    public function index(Request $request)
    {
        $query = Documentation::with('author');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('type', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = 20;
        $docs = $query->paginate($perPage);

        // Transform pagination links to the correct format
        $links = array_map(function ($link) {
            return [
                'url' => $link['url'],
                'label' => $link['label'],
                'active' => $link['active']
            ];
        }, $docs->linkCollection()->toArray());

        return Inertia::render('Documentation/Index', [
            'records' => [
                'data' => $docs->items(),
                'links' => $links,
                'current_page' => $docs->currentPage(),
                'last_page' => $docs->lastPage(),
                'total' => $docs->total(),
            ],
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|json',
            'type' => 'required|string',
            'status' => 'required|string',
            'tags' => 'nullable|string|json' // Change to accept JSON string
        ]);

        $validated['author_id'] = auth()->id();

        $documentation = Documentation::create($validated);

        return response()->json([
            'message' => 'Documentation created successfully',
            'record' => $documentation
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|json', // Validates that content is valid JSON
            'type' => 'required|string',
            'status' => 'required|string',
            'tags' => 'nullable|string'
        ]);

        $documentation = Documentation::findOrFail($id);
        $documentation->update($validated);

        return response()->json([
            'message' => 'Documentation updated successfully',
            'record' => $documentation
        ]);
    }

    public function destroy($id)
    {
        $documentation = Documentation::findOrFail($id);
        $documentation->delete();

        return response()->json([
            'message' => 'Documentation deleted successfully'
        ]);
    }
}















