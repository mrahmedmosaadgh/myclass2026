<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeacherImportService;
use App\Models\Teacher;
use App\Models\School;

class TeacherMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teacher:maintenance 
                            {action : The maintenance action to perform (sync-status|integrity-check|bulk-sync)}
                            {--school= : Specific school ID to process}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform advanced teacher management maintenance operations';

    /**
     * Teacher import service instance
     *
     * @var TeacherImportService
     */
    protected $teacherImportService;

    /**
     * Create a new command instance.
     *
     * @param TeacherImportService $teacherImportService
     */
    public function __construct(TeacherImportService $teacherImportService)
    {
        parent::__construct();
        $this->teacherImportService = $teacherImportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $schoolId = $this->option('school');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        switch ($action) {
            case 'sync-status':
                return $this->syncTeacherUserStatus($schoolId, $dryRun);
            
            case 'integrity-check':
                return $this->performIntegrityCheck($schoolId);
            
            case 'bulk-sync':
                return $this->performBulkSync($schoolId, $dryRun);
            
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: sync-status, integrity-check, bulk-sync');
                return 1;
        }
    }

    /**
     * Sync teacher-user status (Requirements 8.1, 8.3)
     *
     * @param int|null $schoolId
     * @param bool $dryRun
     * @return int
     */
    protected function syncTeacherUserStatus(?int $schoolId, bool $dryRun): int
    {
        $this->info('Starting teacher-user status synchronization...');

        if ($schoolId) {
            $school = School::find($schoolId);
            if (!$school) {
                $this->error("School with ID {$schoolId} not found");
                return 1;
            }
            $this->info("Processing school: {$school->name} (ID: {$schoolId})");
        } else {
            $this->info('Processing all schools');
        }

        if (!$dryRun) {
            $results = $this->teacherImportService->performBulkTeacherStatusSync($schoolId);
        } else {
            // Dry run - just show what would be processed
            $query = Teacher::with('user');
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
            $teachers = $query->get();
            
            $results = [
                'total_processed' => $teachers->count(),
                'successful_syncs' => 0,
                'failed_syncs' => 0,
                'issues' => []
            ];

            foreach ($teachers as $teacher) {
                $syncResult = $teacher->performComprehensiveStatusSync();
                if (!$syncResult['success']) {
                    $results['issues'][$teacher->id] = $syncResult['issues_found'];
                    $results['failed_syncs']++;
                } else {
                    $results['successful_syncs']++;
                }
            }
        }

        // Display results
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $results['total_processed']],
                ['Successful Syncs', $results['successful_syncs']],
                ['Failed Syncs', $results['failed_syncs']],
            ]
        );

        if (!empty($results['issues'])) {
            $this->warn('Issues found:');
            foreach ($results['issues'] as $teacherId => $issues) {
                $teacher = Teacher::find($teacherId);
                $this->error("Teacher {$teacher->name} (ID: {$teacherId}):");
                foreach ($issues as $issue) {
                    $this->line("  - {$issue}");
                }
            }
        }

        return 0;
    }

    /**
     * Perform comprehensive integrity check (Requirements 8.2, 8.5)
     *
     * @param int|null $schoolId
     * @return int
     */
    protected function performIntegrityCheck(?int $schoolId): int
    {
        $this->info('Starting comprehensive integrity check...');

        if ($schoolId) {
            $school = School::find($schoolId);
            if (!$school) {
                $this->error("School with ID {$schoolId} not found");
                return 1;
            }
            
            $results = $this->teacherImportService->performSchoolIntegrityCheck($schoolId);
            $this->displayIntegrityResults($results, $school->name);
        } else {
            $schools = School::all();
            foreach ($schools as $school) {
                $this->info("\nChecking school: {$school->name} (ID: {$school->id})");
                $results = $this->teacherImportService->performSchoolIntegrityCheck($school->id);
                $this->displayIntegrityResults($results, $school->name);
            }
        }

        return 0;
    }

    /**
     * Display integrity check results
     *
     * @param array $results
     * @param string $schoolName
     */
    protected function displayIntegrityResults(array $results, string $schoolName): void
    {
        $this->info("\n=== Integrity Check Results for {$schoolName} ===");
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Teachers Checked', $results['teachers_checked']],
                ['Assignments Checked', $results['assignments_checked']],
                ['Status Sync Issues', count($results['status_sync_issues'])],
                ['Integrity Issues', count($results['integrity_issues'])],
                ['Historical Data Issues', count($results['historical_data_issues'])],
            ]
        );

        if (!empty($results['status_sync_issues'])) {
            $this->warn('Status Synchronization Issues:');
            foreach ($results['status_sync_issues'] as $issue) {
                $this->line("- {$issue['teacher_name']} (ID: {$issue['teacher_id']})");
                foreach ($issue['issues'] as $detail) {
                    $this->line("  * {$detail}");
                }
            }
        }

        if (!empty($results['integrity_issues'])) {
            $this->error('Referential Integrity Issues:');
            foreach ($results['integrity_issues'] as $issue) {
                if (isset($issue['teacher_name'])) {
                    $this->line("- Teacher: {$issue['teacher_name']} (ID: {$issue['teacher_id']})");
                } elseif (isset($issue['assignment_id'])) {
                    $this->line("- Assignment ID: {$issue['assignment_id']}");
                }
                
                foreach ($issue['issues'] as $detail) {
                    $this->line("  * {$detail}");
                }
            }
        }

        if (!empty($results['recommendations'])) {
            $this->info('Recommendations:');
            foreach ($results['recommendations'] as $recommendation) {
                $this->line("- {$recommendation}");
            }
        }
    }

    /**
     * Perform bulk synchronization (Requirements 8.1, 8.3)
     *
     * @param int|null $schoolId
     * @param bool $dryRun
     * @return int
     */
    protected function performBulkSync(?int $schoolId, bool $dryRun): int
    {
        $this->info('Starting bulk teacher synchronization...');

        // First perform integrity check
        $this->performIntegrityCheck($schoolId);

        // Then perform status sync
        return $this->syncTeacherUserStatus($schoolId, $dryRun);
    }
}