<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Seed the test user account.
     * 
     * This creates a test user for development and testing purposes.
     * The credentials are stored in the .env file:
     * - TEST_USER_EMAIL
     * - TEST_USER_PASSWORD
     */
    public function run(): void
    {
        $email = env('TEST_USER_EMAIL', 'test@example.com');
        $password = env('TEST_USER_PASSWORD', 'Test12345678!');

        // Check if test user already exists
        $testUser = User::where('email', $email)->first();

        if ($testUser) {
            $this->command->info("✓ Test user already exists: {$email}");
            
            // Optionally update password if it has changed
            if ($this->command->confirm('Would you like to reset the password to the one in .env?')) {
                $testUser->update([
                    'password' => Hash::make($password),
                ]);
                $this->command->info('✓ Test user password updated');
            }
            
            return;
        }

        // Create test user
        $this->command->info('Creating test user account...');
        
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign teacher role
        $user->assignRole('teacher');

        // Create associated teacher record
        $school = School::first();
        
        if (!$school) {
            $this->command->warn('⚠ No school found. Creating default school...');
            $school = School::create([
                'name' => 'Test School',
                'academic_year_id' => 1,
                'semester_id' => 1,
            ]);
        }

        Teacher::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => 'Test User',
            'email' => $email,
        ]);

        $this->command->info('✓ Test user created successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Email:    ' . $email);
        $this->command->info('Password: ' . $password);
        $this->command->info('Role:     teacher');
        $this->command->info('School:   ' . $school->name);
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        $this->command->warn('⚠️  SECURITY REMINDER:');
        $this->command->warn('   - These credentials are for LOCAL TESTING ONLY');
        $this->command->warn('   - Never use these in production');
        $this->command->warn('   - Change passwords before deploying');
    }
}
