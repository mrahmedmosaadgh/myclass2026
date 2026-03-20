<?php

namespace App\Console\Commands;

use App\Models\CrCategoryMapping;
use App\Models\CrScore;
use App\Models\CrStudentPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrBackfillScores extends Command
{
    protected $signature = 'cr:backfill-scores
        {--school_id= : Limit to a specific school_id}
        {--year_id= : Limit to a specific year_id}
        {--dry-run : Only report changes, do not write}';

    protected $description = 'Ensure each CR student period has score rows for all active mappings';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $schoolId = $this->option('school_id');
        $yearId = $this->option('year_id');

        $query = CrStudentPeriod::query();
        if ($schoolId) $query->where('school_id', (int) $schoolId);
        if ($yearId) $query->where('year_id', (int) $yearId);

        $totalPeriods = (clone $query)->count();
        $this->info("CR Backfill starting...");
        $this->info("Dry run: " . ($dryRun ? 'YES' : 'NO'));
        $this->info("Student periods matched: " . $totalPeriods);

        if ($totalPeriods === 0) {
            $this->warn("No student periods found matching the criteria.");
            return 0;
        }

        $updatedPeriods = 0;
        $createdScores = 0;
        $mappingCache = [];

        $bar = $this->output->createProgressBar($totalPeriods);
        $bar->start();

        $query->orderBy('id')->chunk(200, function ($periods) use ($dryRun, &$updatedPeriods, &$createdScores, &$mappingCache, $bar) {
            /** @var \Illuminate\Support\Collection<int, \App\Models\CrStudentPeriod> $periods */
            foreach ($periods as $period) {
                // Cache mappings by school_id to avoid N+1 redundant queries
                if (!isset($mappingCache[$period->school_id])) {
                    $mappingCache[$period->school_id] = CrCategoryMapping::where('school_id', $period->school_id)
                        ->where('active', true)
                        ->orderBy('sort_order')
                        ->get();
                }

                $mappings = $mappingCache[$period->school_id];

                if ($mappings->isEmpty()) {
                    $bar->advance();
                    continue;
                }

                $missing = [];
                // Optimized check: fetch all existing mapping IDs for this period at once
                $existingMappingIds = CrScore::where('student_period_id', $period->id)
                    ->pluck('mapping_id')
                    ->toArray();

                foreach ($mappings as $mapping) {
                    if (!in_array($mapping->id, $existingMappingIds)) {
                        $missing[] = $mapping;
                    }
                }

                if (count($missing) > 0) {
                    $updatedPeriods++;
                    $createdScores += count($missing);

                    if (!$dryRun) {
                        DB::transaction(function () use ($period, $missing) {
                            foreach ($missing as $mapping) {
                                CrScore::create([
                                    'student_period_id' => $period->id,
                                    'mapping_id' => $mapping->id,
                                    'numeric_value' => $mapping->default_value,
                                ]);
                            }

                            // Explicitly recalculate and persist the total score
                            // attendance_score is unsignedTinyInteger, scores sum is decimal(8,2)
                            $categoryScoresSum = $period->scores()->sum('numeric_value');
                            $period->total_score = round(($period->attendance_score ?? 0) + $categoryScoresSum);
                            $period->save();
                        });
                    }
                }
                
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Student periods updated', $updatedPeriods],
                ['Scores created', $createdScores]
            ]
        );

        return 0;
    }
}

