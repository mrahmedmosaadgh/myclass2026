<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Exam;
use App\Models\ExamQuestion;

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

        $htmlContent = $this->generatePrintHtml($data);

        return response($htmlContent)
            ->header('Content-Type', 'text/html');
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

            // Sanitize HTML - remove problematic elements for dompdf
            $htmlContent = $this->sanitizeHtmlForPdf($htmlContent);

            // Generate filename
            $examName = $data['name'] ?? 'exam';
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $examName);
            $filename = "{$safeName}_{$examId}.pdf";

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
    private function generatePrintHtml($data)
    {
        $settings = $data['settings'] ?? [];
        $questions = $data['questions'] ?? [];
        $sections = $data['sections'] ?? [];
        $questionSectionMap = $data['questionSectionMap'] ?? [];
        $pageBreaks = $data['pageBreaks'] ?? [];

        // Debug logging
        \Log::info('Generating print HTML', [
            'questions_count' => count($questions),
            'sections_count' => count($sections),
            'questionSectionMap_count' => count($questionSectionMap),
            'questions_sample' => array_slice($questions, 0, 2),
        ]);

        $examTitle = $settings['examTitle']['enabled'] ? $settings['examTitle']['text'] : 'Exam';
        $showMarks = $settings['showMarksPerQuestion'] ?? true;
        $printHeader = $settings['printHeader'] ?? [];
        $printFooter = $settings['printFooter'] ?? [];
        $questionSeparator = $settings['questionSeparator'] ?? [];
        $mcqOptions = $settings['mcqOptions'] ?? [];
        $questionNumbering = $settings['questionNumbering'] ?? [];
        $sectionTotal = $settings['sectionTotal'] ?? [];

        // Build HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($examTitle) . '</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Add page numbers to footer
            const pageNumbers = document.querySelectorAll(".page-number");
            pageNumbers.forEach(function(el) {
                el.textContent = "Page " + (el.dataset.pageNumber || 1);
            });
        });
    </script>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            counter-reset: page;
        }
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
        }
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
        }
        .page-break {
            page-break-before: always;
            height: 0;
        }
        .question {
            margin-bottom: 18pt;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 6pt;
        }
        .question-content {
            margin-bottom: 8pt;
        }
        .question-options {
            margin: 6pt 0 8pt;
        }
        .option {
            margin-bottom: 4pt;
        }
        .answer-area {
            border-top: 1px solid #ccc;
            margin-top: 10pt;
            padding-top: 8pt;
        }
        .answer-line {
            border-bottom: 1px solid #ccc;
            height: 18pt;
            margin-bottom: 6pt;
        }
        .section-header {
            margin: 20pt 0 10pt;
        }
        .section-title {
            font-size: 15pt;
            text-decoration: underline;
            font-weight: bold;
        }
        @media print {
            .print-header {
                position: fixed;
                top: 0;
            }
            .print-footer {
                position: fixed;
                bottom: 0;
            }
            .page-number::after {
                content: " " counter(page);
            }
        }
    </style>
</head>
<body>';

        // Add header if enabled
        if ($printHeader['enabled'] ?? false) {
            $html .= '<div class="print-header">';
            if ($printHeader['mode'] === 'html' && !empty($printHeader['html'])) {
                $html .= $printHeader['html'];
            } elseif ($printHeader['mode'] === 'image' && !empty($printHeader['imageUrl'])) {
                $html .= '<img src="' . htmlspecialchars($printHeader['imageUrl']) . '" style="width:100%; height:auto;">';
            }
            $html .= '</div>';
        }

        // Add spacer for header
        $html .= '<div style="height: 140px;"></div>';

        // Add exam title
        if ($settings['examTitle']['enabled'] ?? false) {
            $html .= '<h1>' . htmlspecialchars($settings['examTitle']['text']) . '</h1>';
        }

        // Group questions by section
        $globalIndex = 0;

        // Rebuild questionSectionMap using actual question IDs from questions array to match types
        $questionSectionMapTyped = [];
        foreach ($questions as $question) {
            $qId = $question['id'];
            // Find this question in the original map (as string)
            foreach ($questionSectionMap as $strKey => $sectionId) {
                if (strval($qId) === $strKey) {
                    $questionSectionMapTyped[$qId] = $sectionId;
                    break;
                }
            }
        }

        \Log::info('Starting question rendering', [
            'sections_empty' => empty($sections),
            'sections_count' => count($sections),
            'questions_count' => count($questions),
            'questionSectionMap_count' => count($questionSectionMap),
            'questionSectionMapTyped_count' => count($questionSectionMapTyped),
        ]);

        // If no sections exist or typed map is empty, render all questions without section grouping
        if (empty($sections) || empty($questionSectionMapTyped)) {
            \Log::info('Rendering questions without sections (typed map empty fallback)');
            foreach ($questions as $question) {
                $globalIndex++;
                $qid = $question['id'];

                // Page break before question
                if (isset($pageBreaks[$qid])) {
                    $html .= '<div class="page-break"></div>';
                }

                $html .= '<div class="question">';
                $html .= '<div class="question-header">';
                $html .= '<span>Q' . $globalIndex . '</span>';
                if ($showMarks) {
                    $html .= '<span>' . ($question['marks'] ?? 1) . ' marks</span>';
                }
                $html .= '</div>';
                $html .= '<div class="question-content">' . $this->renderMath($question['content']['prompt'] ?? '') . '</div>';

                // Options for MCQ
                if (!empty($question['content']['options'])) {
                    $html .= '<div class="question-options">';
                    foreach ($question['content']['options'] as $idx => $option) {
                        $label = chr(65 + $idx) . ')';
                        $html .= '<div class="option"><strong>' . $label . '</strong> ' . $this->renderMath($option) . '</div>';
                    }
                    $html .= '</div>';
                }

                // Answer lines for non-MCQ
                if ($question['type'] !== 'multiple_choice' && $question['type'] !== 'true_false') {
                    $marks = $question['marks'] ?? 1;
                    $lines = min(4, max(2, $marks));
                    $html .= '<div class="answer-area">';
                    for ($i = 0; $i < $lines; $i++) {
                        $html .= '<div class="answer-line"></div>';
                    }
                    $html .= '</div>';
                }

                // Question separator
                if ($questionSeparator['enabled'] ?? false) {
                    $style = $questionSeparator['lineStyle'] ?? 'solid';
                    $color = $questionSeparator['color'] ?? '#1f3a5a';
                    $html .= '<div style="margin: 8pt 0; border-bottom: 1pt ' . $style . ' ' . $color . ';"></div>';
                }

                $html .= '</div>';
            }
        } else {
            // First, collect all assigned question IDs
            $assignedQuestionIds = [];
            foreach ($questionSectionMapTyped as $qId => $sectionId) {
                $assignedQuestionIds[] = $qId;
            }

            \Log::info('Section rendering', [
                'assigned_question_ids' => $assignedQuestionIds,
                'sections' => array_map(function($s) { return ['id' => $s['id'], 'title' => $s['title']]; }, $sections),
            ]);

            // Render questions by section
            foreach ($sections as $section) {
                $sectionId = $section['id'];
                
                \Log::info('Filtering questions for section', [
                    'section_id' => $sectionId,
                    'section_id_type' => gettype($sectionId),
                    'total_questions' => count($questions),
                    'questionSectionMapTyped_sample' => array_slice($questionSectionMapTyped, 0, 3),
                ]);

                $sectionQuestions = array_filter($questions, function($q) use ($sectionId, $questionSectionMapTyped) {
                    $qId = $q['id'];
                    // Use the question ID directly as key without conversion
                    $mappedSection = $questionSectionMapTyped[$qId] ?? null;
                    
                    \Log::info('Question filtering check', [
                        'question_id' => $qId,
                        'question_id_type' => gettype($qId),
                        'mapped_section' => $mappedSection,
                        'mapped_section_type' => $mappedSection ? gettype($mappedSection) : 'null',
                        'section_id' => $sectionId,
                        'matches' => $mappedSection === $sectionId,
                    ]);
                    
                    return $mappedSection === $sectionId;
                });

                \Log::info('Section questions', [
                    'section_id' => $sectionId,
                    'section_title' => $section['title'],
                    'section_questions_count' => count($sectionQuestions),
                    'section_question_ids' => array_map(function($q) { return $q['id']; }, $sectionQuestions),
                ]);

                if (empty($sectionQuestions)) continue;

                // Section header
                $html .= '<div class="section-header">';
                $html .= '<div class="section-title">' . htmlspecialchars($section['title']) . '</div>';
                if (!empty($section['instructions'])) {
                    $html .= '<div>' . htmlspecialchars($section['instructions']) . '</div>';
                }
                $html .= '</div>';

                // Section page break
                if ($section['pageBreakBefore'] ?? false) {
                    $html .= '<div class="page-break"></div>';
                }

                // Questions
                foreach ($sectionQuestions as $question) {
                    $globalIndex++;
                    $qid = $question['id'];

                    // Page break before question
                    if (isset($pageBreaks[$qid])) {
                        $html .= '<div class="page-break"></div>';
                    }

                    $html .= '<div class="question">';
                    $html .= '<div class="question-header">';
                    $html .= '<span>Q' . $globalIndex . '</span>';
                    if ($showMarks) {
                        $html .= '<span>' . ($question['marks'] ?? 1) . ' marks</span>';
                    }
                    $html .= '</div>';
                    $html .= '<div class="question-content">' . $this->renderMath($question['content']['prompt'] ?? '') . '</div>';

                    // Options for MCQ
                    if (!empty($question['content']['options'])) {
                        $html .= '<div class="question-options">';
                        foreach ($question['content']['options'] as $idx => $option) {
                            $label = chr(65 + $idx) . ')';
                            $html .= '<div class="option"><strong>' . $label . '</strong> ' . $this->renderMath($option) . '</div>';
                        }
                        $html .= '</div>';
                    }

                    // Answer lines for non-MCQ
                    if ($question['type'] !== 'multiple_choice' && $question['type'] !== 'true_false') {
                        $marks = $question['marks'] ?? 1;
                        $lines = min(4, max(2, $marks));
                        $html .= '<div class="answer-area">';
                        for ($i = 0; $i < $lines; $i++) {
                            $html .= '<div class="answer-line"></div>';
                        }
                        $html .= '</div>';
                    }

                    // Question separator
                    if ($questionSeparator['enabled'] ?? false) {
                        $style = $questionSeparator['lineStyle'] ?? 'solid';
                        $color = $questionSeparator['color'] ?? '#1f3a5a';
                        $html .= '<div style="margin: 8pt 0; border-bottom: 1pt ' . $style . ' ' . $color . ';"></div>';
                    }

                    $html .= '</div>';
                }
            }

            // Render unassigned questions (questions not in any section)
            $unassignedQuestions = array_filter($questions, function($q) use ($assignedQuestionIds) {
                return !in_array($q['id'], $assignedQuestionIds, true);
            });

            if (!empty($unassignedQuestions)) {
                $html .= '<div class="section-header">';
                $html .= '<div class="section-title">Questions</div>';
                $html .= '</div>';

                foreach ($unassignedQuestions as $question) {
                    $globalIndex++;
                    $qid = $question['id'];

                    // Page break before question
                    if (isset($pageBreaks[$qid])) {
                        $html .= '<div class="page-break"></div>';
                    }

                    $html .= '<div class="question">';
                    $html .= '<div class="question-header">';
                    $html .= '<span>Q' . $globalIndex . '</span>';
                    if ($showMarks) {
                        $html .= '<span>' . ($question['marks'] ?? 1) . ' marks</span>';
                    }
                    $html .= '</div>';
                    $html .= '<div class="question-content">' . $this->renderMath($question['content']['prompt'] ?? '') . '</div>';

                    // Options for MCQ
                    if (!empty($question['content']['options'])) {
                        $html .= '<div class="question-options">';
                        foreach ($question['content']['options'] as $idx => $option) {
                            $label = chr(65 + $idx) . ')';
                            $html .= '<div class="option"><strong>' . $label . '</strong> ' . $this->renderMath($option) . '</div>';
                        }
                        $html .= '</div>';
                    }

                    // Answer lines for non-MCQ
                    if ($question['type'] !== 'multiple_choice' && $question['type'] !== 'true_false') {
                        $marks = $question['marks'] ?? 1;
                        $lines = min(4, max(2, $marks));
                        $html .= '<div class="answer-area">';
                        for ($i = 0; $i < $lines; $i++) {
                            $html .= '<div class="answer-line"></div>';
                        }
                        $html .= '</div>';
                    }

                    // Question separator
                    if ($questionSeparator['enabled'] ?? false) {
                        $style = $questionSeparator['lineStyle'] ?? 'solid';
                        $color = $questionSeparator['color'] ?? '#1f3a5a';
                        $html .= '<div style="margin: 8pt 0; border-bottom: 1pt ' . $style . ' ' . $color . ';"></div>';
                    }

                    $html .= '</div>';
                }
            }
        }

        // Add footer if enabled
        if ($printFooter['enabled'] ?? false) {
            $html .= '<div class="print-footer">';
            if ($printFooter['mode'] === 'html' && !empty($printFooter['html'])) {
                $html .= $printFooter['html'];
            }
            if ($printFooter['showPageNumbers'] ?? false) {
                $html .= '<div class="page-number">Page <span class="page-counter"></span></div>';
            }
            $html .= '</div>';
        }

        $html .= '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            renderMathInElement(document.body, {
                delimiters: [
                    {left: "$$", right: "$$", display: true},
                    {left: "$", right: "$", display: false}
                ]
            });
        });
    </script>
</body>
</html>';

        return $html;
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
