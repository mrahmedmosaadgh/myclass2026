<?php

namespace App\Services;

use JsonException;

class ExamPaginationService
{
    protected string $bindingPath;
    protected int $maxPages;
    protected float $pageHeightMm;
    protected float $usablePageHeightMm;
    protected float $sectionKeepWithNextMinMm;

    public function __construct()
    {
        $this->bindingPath = base_path('tools/print-calibration/layout-metrics-binding.json');
        $this->maxPages = (int) config('services.print_pdf.max_pages', 60);
        $this->pageHeightMm = 297.0;
        $this->usablePageHeightMm = $this->pageHeightMm - (12 * 2);
        $this->sectionKeepWithNextMinMm = 24.0;
    }

    public function paginate(array $renderSections, string $layoutVersion = 'v1.0', array $options = []): array
    {
        $rawMode = strtolower((string) ($options['mode'] ?? 'strict'));
        $mode = in_array($rawMode, ['strict', 'flex'], true) ? $rawMode : 'strict';

        $marginTopMm = $this->normalizeMm($options['marginTopMm'] ?? 12.0, 12.0);
        $marginBottomMm = $this->normalizeMm($options['marginBottomMm'] ?? 12.0, 12.0);
        $headerHeightMm = $this->normalizeMm($options['headerHeightMm'] ?? 0.0, 0.0);
        $footerHeightMm = $this->normalizeMm($options['footerHeightMm'] ?? 0.0, 0.0);
        $usablePageHeightMm = round(max(10.0, $this->pageHeightMm - $marginTopMm - $marginBottomMm - $headerHeightMm - $footerHeightMm), 1);

        $allowOverflow = (bool) ($options['allowOverflow'] ?? false);
        $defaultToleranceMm = $mode === 'flex' ? 2.0 : 1.0;
        $overflowToleranceMm = $this->normalizeMm($options['overflowToleranceMm'] ?? $defaultToleranceMm, $defaultToleranceMm);
        $overflowFallback = (string) ($options['overflowFallback'] ?? 'next-page');
        $keepWithNextMinMm = $mode === 'flex' ? 8.0 : $this->sectionKeepWithNextMinMm;

        $seedInput = (string) ($options['paginationSeed'] ?? '');
        $paginationSeed = $seedInput !== ''
            ? $seedInput
            : $this->buildPaginationSeed($renderSections, $layoutVersion);

        $rulePriorities = $this->rulePriorities();

        $result = [
            'pageBreakLookup' => [],
            'report' => [
                'layoutVersion' => $layoutVersion,
                'metricsVersion' => null,
                'metricsFile' => null,
                'mode' => $mode,
                'paginationSeed' => $paginationSeed,
                'rulePriorities' => $rulePriorities,
                'page' => [
                    'heightMm' => $this->pageHeightMm,
                    'marginTopMm' => $marginTopMm,
                    'marginBottomMm' => $marginBottomMm,
                    'headerHeightMm' => $headerHeightMm,
                    'footerHeightMm' => $footerHeightMm,
                    'usableHeightMm' => $usablePageHeightMm,
                ],
                'overflowPolicy' => [
                    'allowOverflow' => $allowOverflow,
                    'fallback' => $overflowFallback,
                    'toleranceMm' => $overflowToleranceMm,
                ],
                'usablePageHeightMm' => $usablePageHeightMm,
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
            'remainingHeightMm' => round($usablePageHeightMm, 1),
            'items' => [],
            'blocks' => [],
        ]];

        foreach ($renderSections as $section) {
            $sectionId = $section['id'] ?? null;
            $sectionTitle = $section['title'] ?? null;
            $sectionInstructions = $section['instructions'] ?? null;
            $sectionPageBreakBefore = (bool) ($section['pageBreakBefore'] ?? false);
            $sectionQuestions = $section['questions'] ?? [];

            if ($sectionPageBreakBefore && $usedHeight > 0) {
                $page++;
                $usedHeight = 0.0;
                $pages[] = [
                    'pageNumber' => $page,
                    'usedHeightMm' => 0.0,
                    'remainingHeightMm' => round($usablePageHeightMm, 1),
                    'items' => [],
                    'blocks' => [],
                ];
                $result['report']['events'][] = [
                    'type' => 'page_break_before_section',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'rule' => 'manual-page-break',
                    'priority' => 110,
                    'page' => $page,
                ];
            }

            $sectionHeaderHeight = 0.0;
            if (!empty($sectionTitle)) {
                $sectionHeaderHeight += 10.0;
            }
            if (!empty($sectionInstructions)) {
                $sectionHeaderHeight += 6.0;
            }

            if ($sectionHeaderHeight > 0 && $usedHeight + $sectionHeaderHeight > $usablePageHeightMm && $usedHeight > 0) {
                $page++;
                $usedHeight = 0.0;
                $pages[] = [
                    'pageNumber' => $page,
                    'usedHeightMm' => 0.0,
                    'remainingHeightMm' => round($usablePageHeightMm, 1),
                    'items' => [],
                    'blocks' => [],
                ];
                $result['report']['events'][] = [
                    'type' => 'new_page_before_section',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'rule' => 'section-not-last',
                    'priority' => $rulePriorities['section-not-last'] ?? null,
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
                ($usablePageHeightMm - $usedHeight) < ($sectionHeaderHeight + min($nextQuestionMin, $keepWithNextMinMm))
            ) {
                $page++;
                $usedHeight = 0.0;
                $pages[] = [
                    'pageNumber' => $page,
                    'usedHeightMm' => 0.0,
                    'remainingHeightMm' => round($usablePageHeightMm, 1),
                    'items' => [],
                    'blocks' => [],
                ];
                $result['report']['events'][] = [
                    'type' => 'keep_with_next_section_header',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'rule' => 'keep-with-next',
                    'priority' => $rulePriorities['keep-with-next'] ?? null,
                    'page' => $page,
                ];
            }

            $usedHeight += $sectionHeaderHeight;
            if ($sectionHeaderHeight > 0) {
                $sectionBlock = [
                    'type' => 'section_header',
                    'sectionId' => $sectionId,
                    'sectionTitle' => $sectionTitle,
                    'estimatedHeightMm' => round($sectionHeaderHeight, 1),
                ];
                $pages[$page - 1]['blocks'][] = $sectionBlock;
                $pages[$page - 1]['items'][] = $sectionBlock;
                $pages[$page - 1]['usedHeightMm'] = round($usedHeight, 1);
                $pages[$page - 1]['remainingHeightMm'] = round(max(0.0, $usablePageHeightMm - $usedHeight), 1);
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
                $questionExceedsPage = $estimated > $usablePageHeightMm;
                $forcedSingleItemOverflow = false;

                if ($questionExceedsPage) {
                    $result['report']['overflow'] = true;
                    $result['report']['reason'] = "Question block exceeds usable page height ({$questionId})";
                    $result['report']['events'][] = [
                        'type' => 'question_exceeds_page_height',
                        'questionId' => $questionId,
                        'estimatedHeightMm' => round($estimated, 1),
                        'usablePageHeightMm' => $usablePageHeightMm,
                        'splitPolicy' => $splitPolicy,
                        'rule' => 'no-split-question',
                        'priority' => $rulePriorities['no-split-question'] ?? null,
                    ];
                }

                if (
                    $usedHeight > 0 &&
                    ($usedHeight + $estimated) > ($usablePageHeightMm + $overflowToleranceMm)
                ) {
                    $result['pageBreakLookup'][$questionId] = true;
                    $page++;
                    $usedHeight = 0.0;
                    $pages[] = [
                        'pageNumber' => $page,
                        'usedHeightMm' => 0.0,
                        'remainingHeightMm' => round($usablePageHeightMm, 1),
                        'items' => [],
                        'blocks' => [],
                    ];

                    $result['report']['events'][] = [
                        'type' => 'new_page_before_question',
                        'questionId' => $questionId,
                        'estimatedHeightMm' => round($estimated, 1),
                        'splitPolicy' => $splitPolicy,
                        'rule' => 'no-split-question',
                        'priority' => $rulePriorities['no-split-question'] ?? null,
                        'page' => $page,
                    ];
                }

                if ($questionExceedsPage && $usedHeight === 0.0) {
                    $forcedSingleItemOverflow = true;
                    $result['report']['events'][] = [
                        'type' => 'forced_single_item_overflow',
                        'questionId' => $questionId,
                        'estimatedHeightMm' => round($estimated, 1),
                        'usablePageHeightMm' => $usablePageHeightMm,
                        'splitPolicy' => $splitPolicy,
                        'rule' => 'overflow-policy-single-item',
                        'fallback' => $overflowFallback,
                    ];
                }

                $usedHeight += $estimated;
                $questionBlock = [
                    'type' => 'question',
                    'sectionId' => $sectionId,
                    'questionId' => $questionId,
                    'questionType' => $question['type'] ?? null,
                    'estimatedHeightMm' => round($estimated, 1),
                    'splitPolicy' => $splitPolicy,
                ];
                $pages[$page - 1]['blocks'][] = $questionBlock;
                $pages[$page - 1]['items'][] = $questionBlock;
                $pages[$page - 1]['usedHeightMm'] = round($usedHeight, 1);
                $pages[$page - 1]['remainingHeightMm'] = round(max(0.0, $usablePageHeightMm - $usedHeight), 1);

                if (!$allowOverflow && !$forcedSingleItemOverflow && $usedHeight > ($usablePageHeightMm + $overflowToleranceMm)) {
                    $result['report']['overflow'] = true;
                    $result['report']['events'][] = [
                        'type' => 'overflow_violation',
                        'questionId' => $questionId,
                        'usedHeightMm' => round($usedHeight, 1),
                        'usablePageHeightMm' => $usablePageHeightMm,
                        'fallback' => $overflowFallback,
                    ];
                }

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

    protected function normalizeMm(mixed $value, float $default): float
    {
        $n = is_numeric($value) ? (float) $value : $default;
        return max(0.0, round($n, 1));
    }

    protected function buildPaginationSeed(array $renderSections, string $layoutVersion): string
    {
        $payload = ['layoutVersion' => $layoutVersion, 'sections' => []];

        foreach ($renderSections as $section) {
            $questionIds = [];
            foreach (($section['questions'] ?? []) as $question) {
                $questionIds[] = (string) ($question['id'] ?? '');
            }

            $payload['sections'][] = [
                'id' => (string) ($section['id'] ?? ''),
                'title' => (string) ($section['title'] ?? ''),
                'questionIds' => $questionIds,
            ];
        }

        return 'seed-' . substr(sha1(json_encode($payload)), 0, 16);
    }

    protected function rulePriorities(): array
    {
        return [
            'no-split-question' => 100,
            'section-not-last' => 90,
            'keep-with-next' => 80,
            'avoid-orphan' => 50,
        ];
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
        $isFrozen = $artifact['frozen'] ?? null;
        $metricsRelativeFile = $artifact['file'] ?? null;

        if ($isFrozen !== true) {
            return [null, $metricsVersion, null, "Locked artifact for {$metricsVersion} is not frozen"];
        }

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

        $metricsLayoutVersion = (string) ($metricsData['layoutVersion'] ?? '');
        if ($metricsLayoutVersion !== $layoutVersion) {
            return [null, $metricsVersion, $metricsFile, "Metrics layoutVersion mismatch: expected {$layoutVersion}, got {$metricsLayoutVersion}"];
        }

        $metricsDataVersion = (string) ($metricsData['metricsVersion'] ?? '');
        if ($metricsDataVersion !== $metricsVersion) {
            return [null, $metricsVersion, $metricsFile, "Metrics version mismatch: expected {$metricsVersion}, got {$metricsDataVersion}"];
        }

        $expectedIntegrityHash = (string) ($metricsData['hash'] ?? '');
        if ($expectedIntegrityHash === '') {
            return [null, $metricsVersion, $metricsFile, 'Metrics file hash is missing'];
        }

        $computedIntegrityHash = $this->computeMetricsIntegrityHash($metricsData);
        if ($computedIntegrityHash === null) {
            return [null, $metricsVersion, $metricsFile, 'Unable to compute metrics integrity hash'];
        }

        if ($expectedIntegrityHash !== $computedIntegrityHash) {
            return [null, $metricsVersion, $metricsFile, 'Metrics integrity hash mismatch'];
        }

        return [$metricsData['values'], $metricsVersion, $metricsFile, null];
    }

    protected function computeMetricsIntegrityHash(array $metricsData): ?string
    {
        $payload = [
            'values' => $metricsData['values'] ?? null,
            'font' => $metricsData['font'] ?? null,
            'lineHeight' => $metricsData['lineHeight'] ?? null,
        ];

        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_string($json)) {
            return null;
        }

        return 'sha256-' . hash('sha256', $json);
    }
}
