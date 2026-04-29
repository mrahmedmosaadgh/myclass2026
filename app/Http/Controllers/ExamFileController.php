<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Services\PuppeteerPdfService;
use App\Services\ExamPaginationService;

class ExamFileController extends Controller
{
    /**
     * Save exam data to database
     */
    public function saveExam(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'questions' => 'required|array',
            'settings' => 'required|array',
            'sections' => 'nullable|array',
            'questionSectionMap' => 'nullable|array',
            'pageBreaks' => 'nullable|array',
            'component_version' => 'nullable|string',
        ]);

        // Debug logging
        \Log::info('saveExam received data', [
            'questions_count' => count($validated['questions']),
            'sections_count' => count($validated['sections'] ?? []),
            'questionSectionMap_count' => count($validated['questionSectionMap'] ?? []),
            'questions_sample' => array_slice($validated['questions'], 0, 2),
        ]);

        $userId = Auth::id();

        // Create or update exam
        $exam = Exam::updateOrCreate(
            [
                'user_id' => $userId,
                'slug' => $request->input('exam_id') ?? null,
            ],
            [
                'name' => $validated['name'],
                'slug' => $request->input('exam_id') ?? uniqid('exam_', true),
                'component_version' => $validated['component_version'] ?? null,
                'settings' => $validated['settings'],
                'metadata' => [
                    'sections' => $validated['sections'] ?? [],
                    'questionSectionMap' => $validated['questionSectionMap'] ?? [],
                    'pageBreaks' => $validated['pageBreaks'] ?? [],
                ],
            ]
        );

        // Delete existing questions for this exam
        ExamQuestion::where('exam_id', $exam->id)->delete();

        // Create questions
        foreach ($validated['questions'] as $index => $question) {
            ExamQuestion::create([
                'exam_id' => $exam->id,
                'order' => $index,
                'type' => $question['type'] ?? null,
                'marks' => $question['marks'] ?? 1,
                'section' => $question['section'] ?? null,
                'content' => $question['content'] ?? [],
                'options' => $question['options'] ?? null,
                'correct_answer' => $question['correct_answer'] ?? null,
                'explanation' => $question['explanation'] ?? null,
                'metadata' => [
                    'id' => $question['id'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'exam_id' => $exam->slug,
            'message' => 'Exam saved successfully'
        ]);
    }

    /**
     * List all saved exams for current user
     */
    public function listSavedExams(Request $request)
    {
        $userId = Auth::id();

        $exams = Exam::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->slug,
                    'title' => $exam->name,
                    'name' => $exam->name,
                    'created_at' => $exam->created_at->toISOString(),
                    'updated_at' => $exam->updated_at->toISOString(),
                    'questions_count' => $exam->questions()->count(),
                ];
            });

        return response()->json(['files' => $exams]);
    }

    /**
     * Load saved exam data from database
     */
    public function loadSavedExam($examId)
    {
        $userId = Auth::id();

        $exam = Exam::where('user_id', $userId)
            ->where('slug', $examId)
            ->first();

        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        // Convert database model to JSON format compatible with frontend
        $questions = $exam->questions->map(function ($question) {
            return [
                'id' => $question->metadata['id'] ?? $question->id,
                'type' => $question->type,
                'marks' => $question->marks,
                'section' => $question->section,
                'content' => $question->content,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation,
            ];
        })->toArray();

        $data = [
            'id' => $exam->slug,
            'name' => $exam->name,
            'created_at' => $exam->created_at->toISOString(),
            'updated_at' => $exam->updated_at->toISOString(),
            'questions' => $questions,
            'settings' => $exam->settings,
            'sections' => $exam->metadata['sections'] ?? [],
            'questionSectionMap' => $exam->metadata['questionSectionMap'] ?? [],
            'pageBreaks' => $exam->metadata['pageBreaks'] ?? [],
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get print HTML for an exam (generated on the fly)
     */
    public function getPrintHtml($examId)
    {
        $userId = Auth::id();

        $exam = Exam::where('user_id', $userId)
            ->where('slug', $examId)
            ->first();

        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        // Convert database model to JSON format
        $questions = $exam->questions->map(function ($question) {
            return [
                'id' => $question->metadata['id'] ?? $question->id,
                'type' => $question->type,
                'marks' => $question->marks,
                'section' => $question->section,
                'content' => $question->content,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation,
            ];
        })->toArray();

        $data = [
            'id' => $exam->slug,
            'name' => $exam->name,
            'created_at' => $exam->created_at->toISOString(),
            'updated_at' => $exam->updated_at->toISOString(),
            'questions' => $questions,
            'settings' => $exam->settings,
            'sections' => $exam->metadata['sections'] ?? [],
            'questionSectionMap' => $exam->metadata['questionSectionMap'] ?? [],
            'pageBreaks' => $exam->metadata['pageBreaks'] ?? [],
        ];

        $paginationReport = null;
        $htmlContent = $this->generatePrintHtml($data, $paginationReport);

        $debugMode = request()->boolean('debug');
        if ($debugMode) {
            return response()->json([
                'success' => true,
                'examId' => $examId,
                'paginationReport' => $paginationReport,
                'html' => $htmlContent,
            ]);
        }

        $response = response($htmlContent)
            ->header('Content-Type', 'text/html');

        if (is_array($paginationReport)) {
            $response->header('X-Pagination-Total-Pages', (string) ($paginationReport['totalPages'] ?? 1));
            $response->header('X-Pagination-Metrics-Version', (string) ($paginationReport['metricsVersion'] ?? 'unknown'));
            $response->header('X-Pagination-Overflow', !empty($paginationReport['overflow']) ? '1' : '0');
        }

        return $response;
    }

    /**
     * Generate and download PDF for an exam
     */
    public function generatePdf($examId)
    {
        try {
            // Increase memory limit for PDF generation
            ini_set('memory_limit', '512M');
            set_time_limit(300);

            $userId = Auth::id();

            $exam = Exam::where('user_id', $userId)
                ->where('slug', $examId)
                ->first();

            if (!$exam) {
                return response()->json(['message' => 'Exam not found'], 404);
            }

            // Convert database model to JSON format
            $questions = $exam->questions->map(function ($question) {
                return [
                    'id' => $question->metadata['id'] ?? $question->id,
                    'type' => $question->type,
                    'marks' => $question->marks,
                    'section' => $question->section,
                    'content' => $question->content,
                    'options' => $question->options,
                    'correct_answer' => $question->correct_answer,
                    'explanation' => $question->explanation,
                ];
            })->toArray();

            $data = [
                'id' => $exam->slug,
                'name' => $exam->name,
                'created_at' => $exam->created_at->toISOString(),
                'updated_at' => $exam->updated_at->toISOString(),
                'questions' => $questions,
                'settings' => $exam->settings,
                'sections' => $exam->metadata['sections'] ?? [],
                'questionSectionMap' => $exam->metadata['questionSectionMap'] ?? [],
                'pageBreaks' => $exam->metadata['pageBreaks'] ?? [],
            ];

            // Generate HTML
            $htmlContent = $this->generatePrintHtml($data);

            // Generate filename
            $examName = $data['name'] ?? 'exam';
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $examName);
            $filename = "{$safeName}_{$examId}.pdf";

            $puppeteerResult = app(PuppeteerPdfService::class)->renderHtmlToPdf($htmlContent);

            if (!$puppeteerResult['error']) {
                $pdfOutput = $puppeteerResult['pdf'];

                return response($pdfOutput, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Content-Length' => strlen($pdfOutput),
                    'X-PDF-Engine' => 'puppeteer',
                ]);
            }

            \Log::warning('Puppeteer PDF failed; falling back to dompdf', [
                'exam_id' => $examId,
                'error' => $puppeteerResult['error'],
                'meta' => $puppeteerResult['meta'] ?? null,
            ]);

            if (!class_exists(\Dompdf\Dompdf::class) || !class_exists(\Dompdf\Options::class)) {
                \Log::warning('Dompdf classes unavailable; returning HTML print fallback', [
                    'exam_id' => $examId,
                ]);

                return response($htmlContent, 200, [
                    'Content-Type' => 'text/html',
                    'X-PDF-Fallback' => 'true',
                    'X-PDF-Fallback-Reason' => 'dompdf_unavailable',
                ]);
            }

            // Sanitize HTML - remove problematic elements for dompdf
            $htmlContent = $this->sanitizeHtmlForPdf($htmlContent);

            // Configure dompdf with minimal settings
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false); // Disable remote for security
            $options->set('defaultFont', 'Arial');
            $options->set('isPhpEnabled', false);
            $options->set('chroot', base_path());

            // Create dompdf instance
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Get PDF output
            $pdfOutput = $dompdf->output();

            // Return PDF as download
            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfOutput),
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage(), [
                'exam_id' => $examId,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback: Return HTML for browser print-to-PDF
            try {
                $exam = Exam::where('user_id', $userId)
                    ->where('slug', $examId)
                    ->first();

                if ($exam) {
                    $questions = $exam->questions->map(function ($question) {
                        return [
                            'id' => $question->metadata['id'] ?? $question->id,
                            'type' => $question->type,
                            'marks' => $question->marks,
                            'section' => $question->section,
                            'content' => $question->content,
                            'options' => $question->options,
                            'correct_answer' => $question->correct_answer,
                            'explanation' => $question->explanation,
                        ];
                    })->toArray();

                    $data = [
                        'id' => $exam->slug,
                        'name' => $exam->name,
                        'created_at' => $exam->created_at->toISOString(),
                        'updated_at' => $exam->updated_at->toISOString(),
                        'questions' => $questions,
                        'settings' => $exam->settings,
                        'sections' => $exam->metadata['sections'] ?? [],
                        'questionSectionMap' => $exam->metadata['questionSectionMap'] ?? [],
                        'pageBreaks' => $exam->metadata['pageBreaks'] ?? [],
                    ];

                    $htmlContent = $this->generatePrintHtml($data);
                    
                    return response($htmlContent, 200, [
                        'Content-Type' => 'text/html',
                        'X-PDF-Fallback' => 'true',
                    ]);
                }
            } catch (\Exception $fallbackError) {
                return response()->json([
                    'message' => 'PDF generation failed: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Sanitize HTML for PDF generation - remove problematic elements
     */
    private function sanitizeHtmlForPdf($html)
    {
        // Remove script tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        
        // Remove iframe tags
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        
        // Remove data URLs from images (dompdf doesn't handle them well)
        $html = preg_replace('/src=["\']data:image[^"\']*["\']/', 'src=""', $html);
        
        // Remove complex CSS that might cause issues
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        
        // Remove inline styles that use complex properties
        $html = preg_replace('/style=["\'][^"\']*grid[^"\']*["\']/', 'style=""', $html);
        $html = preg_replace('/style=["\'][^"\']*flex[^"\']*["\']/', 'style=""', $html);
        
        return $html;
    }

    /**
     * Delete saved exam from database
     */
    public function deleteSavedExam($examId)
    {
        $userId = Auth::id();

        $exam = Exam::where('user_id', $userId)
            ->where('slug', $examId)
            ->first();

        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        // Questions will be deleted automatically due to cascade delete
        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully'
        ]);
    }

    /**
     * Generate print-ready HTML from exam data
     */
    private function generatePrintHtml($data, &$paginationReport = null)
    {
        $settings = $data['settings'] ?? [];
        $questions = $data['questions'] ?? [];
        $sections = $data['sections'] ?? [];
        $questionSectionMap = $data['questionSectionMap'] ?? [];
        $pageBreaks = $data['pageBreaks'] ?? [];

        $examTitle = ($settings['examTitle']['enabled'] ?? false)
            ? ($settings['examTitle']['text'] ?? 'Exam')
            : 'Exam';

        $showMarks = $settings['showMarksPerQuestion'] ?? true;
        $printHeader = $settings['printHeader'] ?? [];
        $printFooter = $settings['printFooter'] ?? [];
        $questionSeparator = $settings['questionSeparator'] ?? [];

        $pageBreakLookup = [];
        foreach ($pageBreaks as $questionId => $enabled) {
            if ($enabled) {
                $pageBreakLookup[(string) $questionId] = true;
            }
        }

        $questionSectionMapTyped = [];
        foreach ($questions as $question) {
            $qId = $question['id'] ?? null;
            if ($qId === null) {
                continue;
            }

            foreach ($questionSectionMap as $strKey => $sectionId) {
                if ((string) $qId === (string) $strKey) {
                    $questionSectionMapTyped[$qId] = $sectionId;
                    break;
                }
            }
        }

        $renderSections = [];

        if (empty($sections) || empty($questionSectionMapTyped)) {
            $renderSections[] = [
                'id' => 'default',
                'title' => null,
                'instructions' => null,
                'pageBreakBefore' => false,
                'questions' => array_values($questions),
            ];
        } else {
            $assignedQuestionIds = [];
            foreach ($questionSectionMapTyped as $qId => $sectionId) {
                $assignedQuestionIds[] = $qId;
            }

            foreach ($sections as $section) {
                $sectionId = $section['id'] ?? null;
                if ($sectionId === null) {
                    continue;
                }

                $sectionQuestions = array_values(array_filter($questions, function ($q) use ($sectionId, $questionSectionMapTyped) {
                    $qId = $q['id'] ?? null;
                    if ($qId === null) {
                        return false;
                    }

                    $mappedSection = $questionSectionMapTyped[$qId] ?? null;
                    return $mappedSection === $sectionId;
                }));

                if (empty($sectionQuestions)) {
                    continue;
                }

                $renderSections[] = [
                    'id' => $sectionId,
                    'title' => $section['title'] ?? 'Section',
                    'instructions' => $section['instructions'] ?? null,
                    'pageBreakBefore' => (bool) ($section['pageBreakBefore'] ?? false),
                    'questions' => $sectionQuestions,
                ];
            }

            $unassignedQuestions = array_values(array_filter($questions, function ($q) use ($assignedQuestionIds) {
                return !in_array($q['id'] ?? null, $assignedQuestionIds, true);
            }));

            if (!empty($unassignedQuestions)) {
                $renderSections[] = [
                    'id' => 'unassigned',
                    'title' => 'Questions',
                    'instructions' => null,
                    'pageBreakBefore' => false,
                    'questions' => $unassignedQuestions,
                ];
            }
        }

        $pagination = app(ExamPaginationService::class)->paginate(
            $renderSections,
            'v1.0',
            ['questionSeparatorEnabled' => (bool) ($questionSeparator['enabled'] ?? false)]
        );

        $pageBreakLookup = array_merge($pageBreakLookup, $pagination['pageBreakLookup'] ?? []);

        \Log::info('Deterministic pagination report', [
            'report' => $pagination['report'] ?? null,
        ]);

        $paginationReport = $pagination['report'] ?? null;

        return view('exam.print', [
            'examTitle' => $examTitle,
            'showMarks' => $showMarks,
            'printHeader' => $printHeader,
            'printFooter' => $printFooter,
            'questionSeparator' => $questionSeparator,
            'renderSections' => $renderSections,
            'pageBreakLookup' => $pageBreakLookup,
            'paginationReport' => $pagination['report'] ?? null,
        ])->render();
    }

    /**
     * Render math content (basic LaTeX support)
     */
    private function renderMath($text)
    {
        // For now, just return the text as-is
        // KaTeX will render it on the client side
        return htmlspecialchars($text ?? '');
    }
}
