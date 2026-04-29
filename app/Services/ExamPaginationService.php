<?php

namespace App\Services;

class ExamPaginationService
{
    protected string $bindingPath;
    protected int $maxPages;
    protected float $usablePageHeightMm;
    protected float $sectionKeepWithNextMinMm;

    public function __construct()
    {
        $this->bindingPath = base_path('tools/print-calibration/layout-metrics-binding.json');
        $this->maxPages = (int) config('services.print_pdf.max_pages', 60);
        $this->usablePageHeightMm = 297 - (12 * 2);
        $this->sectionKeepWithNextMinMm = 24.0;
    }

    public function paginate(array $renderSections, string $layoutVersion = 'v1.0', array $options = []): array
    {
        $result = [
            'pageBreakLookup' => [],
            'report' => [
                'layoutVersion' => $layoutVersion,
                'metricsVersion' => null,
                'metricsFile' => null,
                'usablePageHeightMm' => $this->usablePageHeightMm,
                'totalPages' => 1,
                'overflow' => false,
                'reason' => null,
                'events' => [],
                'pages' => [],
            ],
        ];

        [$metrics, $metricsVersion, $metricsFile, $error] = $this->loadMetricsForLayout($layoutVersion);
        $result['report']['metricsVersion'] = $metricsVersion;
        $result['report']['metricsFile'] = $metricsFile;

        if ($error !== null) {
            $result['report']['reason'] = $error;
            return $result;
        }

        $separatorEnabled = (bool) ($options['questionSeparatorEnabled'] ?? false);

        $page = 1;
        $usedHeight = 0.0;
        $pages = [[
            'pageNumber' => 1,
            'usedHeightMm' => 0.0,
            'blocks' => [],
        ]];

        foreach ($renderSections as $section) {
            $sectionId = $section['id'] ?? null;
            $sectionTitle = $section['title'] ?? null;
            $sectionInstructions = $section['instructions'] ?? null;
            $sectionQuestions = $section['questions'] ?? [];

            $sectionHeaderHeight = 0.0;
            if (!empty($sectionTitle)) {
                $sectionHeaderHeight += 10.0;
            }
            if (!empty($sectionInstructions)) {
                $sectionHeaderHeight += 6.0;
            }

            if ($sectionHeaderHeight > 0 && $usedHeight + $sectionHeaderHeight > $this->usablePageHeightMm && $usedHeight > 0) {
                $page++;
                $usedHeight = 0.0;
                $pages[] = [
                    'pageNumber' => $page,
                    'usedHeightMm' => 0.0,
                    'blocks' => [],
                ];
                $result['report']['events'][] = [
                    'type' => 'new_page_before_section',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'page' => $page,
                ];
            }

            $nextQuestionMin = 0.0;
            if (!empty($sectionQuestions) && is_array($sectionQuestions)) {
                $firstQuestion = $sectionQuestions[0];
                $nextQuestionMin = $this->estimateQuestionHeightMm($firstQuestion, $metrics);
                if ($separatorEnabled) {
                    $nextQuestionMin += 3.0;
                }
            }

            if (
                $sectionHeaderHeight > 0 &&
                $usedHeight > 0 &&
                ($this->usablePageHeightMm - $usedHeight) < ($sectionHeaderHeight + min($nextQuestionMin, $this->sectionKeepWithNextMinMm))
            ) {
                $page++;
                $usedHeight = 0.0;
                $pages[] = [
                    'pageNumber' => $page,
                    'usedHeightMm' => 0.0,
                    'blocks' => [],
                ];
                $result['report']['events'][] = [
                    'type' => 'keep_with_next_section_header',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'page' => $page,
                ];
            }

            $usedHeight += $sectionHeaderHeight;
            if ($sectionHeaderHeight > 0) {
                $pages[$page - 1]['blocks'][] = [
                    'type' => 'section_header',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'estimatedHeightMm' => round($sectionHeaderHeight, 1),
                ];
                $pages[$page - 1]['usedHeightMm'] = round($usedHeight, 1);
            }

            foreach ($sectionQuestions as $question) {
                $questionId = (string) ($question['id'] ?? '');
                if ($questionId === '') {
                    continue;
                }

                $estimated = $this->estimateQuestionHeightMm($question, $metrics);
                if ($separatorEnabled) {
                    $estimated += 3.0;
                }

                $splitPolicy = $this->resolveSplitPolicy($question);

                if ($estimated > $this->usablePageHeightMm) {
                    $result['report']['overflow'] = true;
                    $result['report']['reason'] = "Question block exceeds usable page height ({$questionId})";
                    $result['report']['events'][] = [
                        'type' => 'question_exceeds_page_height',
                        'questionId' => $questionId,
                        'estimatedHeightMm' => round($estimated, 1),
                        'usablePageHeightMm' => $this->usablePageHeightMm,
                        'splitPolicy' => $splitPolicy,
                    ];
                }

                if ($usedHeight > 0 && ($usedHeight + $estimated) > $this->usablePageHeightMm) {
                    $result['pageBreakLookup'][$questionId] = true;
                    $page++;
                    $usedHeight = 0.0;
                    $pages[] = [
                        'pageNumber' => $page,
                        'usedHeightMm' => 0.0,
                        'blocks' => [],
                    ];

                    $result['report']['events'][] = [
                        'type' => 'new_page_before_question',
                        'questionId' => $questionId,
                        'estimatedHeightMm' => round($estimated, 1),
                        'splitPolicy' => $splitPolicy,
                        'page' => $page,
                    ];
                }

                $usedHeight += $estimated;
                $pages[$page - 1]['blocks'][] = [
                    'type' => 'question',
                    'sectionId' => $sectionId,
                    'questionId' => $questionId,
                    'questionType' => $question['type'] ?? null,
                    'estimatedHeightMm' => round($estimated, 1),
                    'splitPolicy' => $splitPolicy,
                ];
                $pages[$page - 1]['usedHeightMm'] = round($usedHeight, 1);

                if ($page > $this->maxPages) {
                    $result['report']['overflow'] = true;
                    $result['report']['reason'] = "Max page limit exceeded ({$this->maxPages})";
                    $result['report']['totalPages'] = $page;
                    $result['report']['pages'] = $pages;
                    return $result;
                }
            }
        }

        $result['report']['totalPages'] = $page;
        $result['report']['pages'] = $pages;
        return $result;
    }

    protected function estimateQuestionHeightMm(array $question, array $metrics): float
    {
        $type = $question['type'] ?? '';
        $content = $question['content'] ?? [];
        $options = $content['options'] ?? [];
        $optionCount = is_array($options) ? count($options) : 0;

        if ($type === 'multiple_choice') {
            if ($optionCount <= 2) {
                return (float) ($metrics['mcq_2_choices'] ?? 24.0);
            }
            if ($optionCount <= 4) {
                return (float) ($metrics['mcq_4_choices'] ?? 40.0);
            }
            return (float) ($metrics['mcq_6_choices'] ?? 55.0);
        }

        if ($type === 'true_false') {
            return (float) ($metrics['mcq_2_choices'] ?? 24.0);
        }

        if ($type === 'essay') {
            return (float) ($metrics['essay'] ?? 70.0);
        }

        $base = (float) ($metrics['long_text'] ?? 40.0);
        $marks = (int) ($question['marks'] ?? 1);
        $extraLines = max(0, min(4, $marks) - 2);
        return $base + ($extraLines * 6.0);
    }

    protected function resolveSplitPolicy(array $question): string
    {
        $type = $question['type'] ?? '';

        if ($type === 'multiple_choice' || $type === 'true_false') {
            return 'no_split';
        }

        if ($type === 'essay') {
            return 'avoid_split';
        }

        return 'soft_split';
    }

    protected function loadMetricsForLayout(string $layoutVersion): array
    {
        if (!is_file($this->bindingPath)) {
            return [null, null, null, "Binding file not found: {$this->bindingPath}"];
        }

        $bindingData = json_decode((string) file_get_contents($this->bindingPath), true);
        if (!is_array($bindingData)) {
            return [null, null, null, 'Binding file is invalid JSON'];
        }

        $metricsVersion = $bindingData['layoutMetricsBinding'][$layoutVersion] ?? null;
        if (!is_string($metricsVersion) || $metricsVersion === '') {
            return [null, null, null, "No metrics binding found for layoutVersion {$layoutVersion}"];
        }

        $artifact = $bindingData['lockedArtifacts'][$metricsVersion] ?? null;
        $metricsRelativeFile = $artifact['file'] ?? null;

        if (!is_string($metricsRelativeFile) || $metricsRelativeFile === '') {
            return [null, $metricsVersion, null, "Locked artifact missing file for metricsVersion {$metricsVersion}"];
        }

        $metricsFile = base_path('tools/print-calibration/' . ltrim($metricsRelativeFile, '/'));
        if (!is_file($metricsFile)) {
            return [null, $metricsVersion, $metricsFile, "Metrics file not found: {$metricsFile}"];
        }

        $metricsData = json_decode((string) file_get_contents($metricsFile), true);
        if (!is_array($metricsData) || !isset($metricsData['values']) || !is_array($metricsData['values'])) {
            return [null, $metricsVersion, $metricsFile, 'Metrics file has invalid structure'];
        }

        return [$metricsData['values'], $metricsVersion, $metricsFile, null];
    }
}
