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
        $this->info('CR backfill starting');
        $this->info('Dry run: ' . ($dryRun ? 'YES' : 'NO'));
        $this->info('Student periods matched: ' . $totalPeriods);

        $updatedPeriods = 0;
        $createdScores = 0;

        $query->orderBy('id')->chunk(200, function ($periods) use ($dryRun, &$updatedPeriods, &$createdScores) {
            /** @var \Illuminate\Support\Collection<int, \App\Models\CrStudentPeriod> $periods */
            foreach ($periods as $period) {
                $mappings = CrCategoryMapping::where('school_id', $period->school_id)
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->get();

                if ($mappings->isEmpty()) {
                    continue;
                }

                $missing = [];
                foreach ($mappings as $mapping) {
                    $exists = CrScore::where('student_period_id', $period->id)
                        ->where('mapping_id', $mapping->id)
                        ->exists();
                    if (!$exists) {
                        $missing[] = $mapping;
                    }
                }

                if (count($missing) === 0) continue;

                $updatedPeriods++;
                $createdScores += count($missing);

                if ($dryRun) {
                    continue;
                }

                DB::transaction(function () use ($period, $missing) {
                    foreach ($missing as $mapping) {
                        CrScore::create([
                            'student_period_id' => $period->id,
                            'mapping_id' => $mapping->id,
                            'numeric_value' => $mapping->default_value,
                        ]);
                    }

                    // Recalculate total after adding defaults
                    $categoryScoresSum = $period->scores()->sum('numeric_value');
                    $period->total_score = ($period->attendance_score ?? 0) + $categoryScoresSum;
                    $period->save();
                });
            }
        });

        $this->info('Student periods updated: ' . $updatedPeriods);
        $this->info('Scores created: ' . $createdScores);

        return 0;
    }
}

