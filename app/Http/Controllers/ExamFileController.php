<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamFileController extends Controller
{
    /**
     * Save exam data (JSON) and generate cached HTML ggggggggggg
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
        ]);

        // Debug logging
        \Log::info('saveExam received data', [
            'questions_count' => count($validated['questions']),
            'sections_count' => count($validated['sections'] ?? []),
            'questionSectionMap_count' => count($validated['questionSectionMap'] ?? []),
            'questions_sample' => array_slice($validated['questions'], 0, 2),
        ]);

        $userId = Auth::id();
        $examId = uniqid('exam_', true);
        $timestamp = now()->format('Y-m-d_H-i-s');

        // Create user directory if it doesn't exist
        $userDir = "exams/{$userId}";
        if (!Storage::exists($userDir)) {
            Storage::makeDirectory($userDir);
        }

        // Save JSON data
        $jsonData = [
            'id' => $examId,
            'name' => $validated['name'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'questions' => $validated['questions'],
            'settings' => $validated['settings'],
            'sections' => $validated['sections'] ?? [],
            'questionSectionMap' => $validated['questionSectionMap'] ?? [],
            'pageBreaks' => $validated['pageBreaks'] ?? [],
        ];

        $jsonPath = "{$userDir}/{$examId}.json";
        Storage::put($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT));

        // Generate and cache HTML
        $htmlContent = $this->generatePrintHtml($jsonData);
        $htmlPath = "{$userDir}/{$examId}.html";
        Storage::put($htmlPath, $htmlContent);

        return response()->json([
            'success' => true,
            'exam_id' => $examId,
            'message' => 'Exam saved successfully'
        ]);
    }

    /**
     * List all saved exams for current user
     */
    public function listSavedExams(Request $request)
    {
        $userId = Auth::id();
        $userDir = "exams/{$userId}";

        if (!Storage::exists($userDir)) {
            return response()->json(['files' => []]);
        }

        $files = Storage::files($userDir);
        $exams = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.json')) {
                $content = Storage::get($file);
                $data = json_decode($content, true);

                if ($data) {
                    $exams[] = [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'created_at' => $data['created_at'],
                        'updated_at' => $data['updated_at'],
                        'questions_count' => count($data['questions'] ?? []),
                    ];
                }
            }
        }

        // Sort by created date descending
        usort($exams, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json(['files' => $exams]);
    }

    /**
     * Load saved exam data (JSON)
     */
    public function loadSavedExam($examId)
    {
        $userId = Auth::id();
        $jsonPath = "exams/{$userId}/{$examId}.json";

        if (!Storage::exists($jsonPath)) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $content = Storage::get($jsonPath);
        $data = json_decode($content, true);

        if (!$data) {
            return response()->json(['message' => 'Failed to parse exam data'], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get cached print HTML for an exam
     */
    public function getPrintHtml($examId)
    {
        $userId = Auth::id();
        $htmlPath = "exams/{$userId}/{$examId}.html";

        if (!Storage::exists($htmlPath)) {
            // If HTML doesn't exist, regenerate from JSON
            $jsonPath = "exams/{$userId}/{$examId}.json";
            if (!Storage::exists($jsonPath)) {
                return response()->json(['message' => 'Exam not found'], 404);
            }

            $content = Storage::get($jsonPath);
            $data = json_decode($content, true);

            if (!$data) {
                return response()->json(['message' => 'Failed to parse exam data'], 500);
            }

            $htmlContent = $this->generatePrintHtml($data);
            Storage::put($htmlPath, $htmlContent);
        } else {
            $htmlContent = Storage::get($htmlPath);
        }

        return response($htmlContent)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Delete saved exam (both JSON and HTML)
     */
    public function deleteSavedExam($examId)
    {
        $userId = Auth::id();
        $jsonPath = "exams/{$userId}/{$examId}.json";
        $htmlPath = "exams/{$userId}/{$examId}.html";

        $deleted = false;

        if (Storage::exists($jsonPath)) {
            Storage::delete($jsonPath);
            $deleted = true;
        }

        if (Storage::exists($htmlPath)) {
            Storage::delete($htmlPath);
            $deleted = true;
        }

        if (!$deleted) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

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
        body {
            counter-reset: page;
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

        // If no sections exist, render all questions without section grouping
        if (empty($sections)) {
            \Log::info('Rendering questions without sections');
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
                $html .= '<div class="page-number">Page </div>';
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
