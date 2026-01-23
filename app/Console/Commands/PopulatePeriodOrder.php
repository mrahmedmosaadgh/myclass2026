<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Support\Facades\DB;

class PopulatePeriodOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:populate-period-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate period_order for schedules sequentially per subject per classroom';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting period_order population...');

        DB::transaction(function () {
            // Get all CSTs (Classroom Subject Teacher links)
            // We group by CST because that represents a Subject in a Classroom (and specific teacher)
            $csts = ClassroomSubjectTeacher::with('classroom', 'subject')->get();

            $bar = $this->output->createProgressBar($csts->count());

            foreach ($csts as $cst) {
                // Get all schedules for this specific CST, ordered chronologically
                $schedules = Schedule::where('cst_id', $cst->id)
                    ->whereNotNull('day_number')
                    ->whereNotNull('period_number')
                    ->orderBy('day_number')
                    ->orderBy('period_number')
                    ->get();

                if ($schedules->isEmpty()) {
                    $bar->advance();
                    continue;
                }

                $order = 1;
                foreach ($schedules as $schedule) {
                    // Update directly to avoid triggering model events if any, for speed
                    Schedule::where('id', $schedule->id)->update(['period_order' => $order]);
                    $order++;
                }

                $bar->advance();
            }

            $bar->finish();
        });

        $this->newLine();
        $this->info('Successfully populated period_order for all schedules!');

        return 0;
    }
}
