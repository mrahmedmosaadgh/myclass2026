<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HR;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class InitialHRAndSchoolSeeder extends Seeder
{
    /**
     * Create initial HR user, HR record, and first school.
     * This follows the correct order: User → HR → School → Update User.school_id
     */
    public function run()
    {
        // Step 1: Create HR User
        $hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@myclass.com',
            'password' => Hash::make('password'), // CHANGE THIS IN PRODUCTION!
            'role' => 'hr_admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'school_id' => null, // Will be set after school creation
        ]);

        // Assign hr_admin role
        $hrUser->assignRole('hr_admin');

        // Step 2: Create HR Record
        $hr = HR::create([
            'user_id' => $hrUser->id,
            'name' => 'Main HR Department',
            'active' => 1,
            'data' => json_encode([
                'phone' => '',
                'address' => '',
                'department' => 'Human Resources',
                'notes' => 'Initial HR setup'
            ])
        ]);

        // Step 3: Create First School
        $school = School::create([
            'h_r_id' => $hr->id,
            'name' => 'Main School',
            'data' => json_encode([
                'address' => '',
                'phone' => '',
                'email' => 'info@mainschool.com',
                'logo' => null,
                'established_year' => date('Y'),
                'notes' => 'Initial school setup'
            ]),
            'is_active' => true
        ]);

        // Step 4: Update HR User with school_id
        $hrUser->update(['school_id' => $school->id]);

        $this->command->info('✅ HR and School created successfully!');
        $this->command->info('   HR User: hr@myclass.com');
        $this->command->info('   School: ' . $school->name . ' (ID: ' . $school->id . ')');
        $this->command->warn('   Password: password');
        $this->command->error('   ⚠️  CHANGE THE PASSWORD IN PRODUCTION!');
    }
}
