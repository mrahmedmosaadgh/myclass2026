<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DebugCrAccess extends Command
{
    protected $signature = 'debug:cr-access';
    protected $description = 'Debug classroom records access for current user';

    public function handle()
    {
        // Simulate logged-in user (you'll need to run this while logged in via web)
        $user = User::find(1); // Change to your user ID
        
        if (!$user) {
            $this->error('User not found');
            return 1;
        }
        
        $this->info('=== Classroom Records Debug ===');
        $this->info('User ID: ' . $user->id);
        $this->info('User Name: ' . $user->name);
        $this->info('User Email: ' . $user->email);
        $this->info('User School ID: ' . ($user->school_id ?? 'NULL'));
        
        // Check currentSchoolId method
        $schoolId = $user->currentSchoolId();
        $this->info('currentSchoolId(): ' . ($schoolId ?? 'NULL'));
        
        // Check currentAcademicYearId method
        $yearId = $user->currentAcademicYearId();
        $this->info('currentAcademicYearId(): ' . ($yearId ?? 'NULL'));
        
        // Count assignments
        $assignmentsCount = \DB::table('classroom_subject_teachers')
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->count();
        
        $this->info('Assignments for school ' . $schoolId . ' year ' . $yearId . ': ' . $assignmentsCount);
        
        // Show sample assignments
        $this->info('\nSample Assignments:');
        $assignments = \DB::table('classroom_subject_teachers')
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->join('classrooms', 'classroom_subject_teachers.classroom_id', '=', 'classrooms.id')
            ->join('subjects', 'classroom_subject_teachers.subject_id', '=', 'subjects.id')
            ->select('classrooms.name as classroom', 'subjects.name as subject')
            ->limit(5)
            ->get();
        
        foreach ($assignments as $a) {
            $this->info('  - ' . $a->classroom . ' / ' . $a->subject);
        }
        
        return 0;
    }
}
