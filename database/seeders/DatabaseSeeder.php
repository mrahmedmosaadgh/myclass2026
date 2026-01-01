<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * This runs all initial seeders in the correct order:
     * 1. Roles and Permissions (foundation)
     * 2. Super Admin User (system administrator)
     * 3. HR and School Setup (organizational structure)
     * 4. School Structure (academic setup)
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting initial database seeding...');
        $this->command->newLine();

        $this->call([
            // Step 1: Create roles and permissions
            RoleSeeder::class,
            
            // Step 2: Create super admin user
            InitialSuperAdminSeeder::class,
            
            // Step 3: Create HR user, HR record, and first school
            InitialHRAndSchoolSeeder::class,
            
            // Step 4: Create school structure (sections, stages, grades, subjects, etc.)
            InitialSchoolStructureSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->warn('📝 Default Login Credentials:');
        $this->command->warn('   Super Admin: admin@myclass.com / password');
        $this->command->warn('   HR Manager:  hr@myclass.com / password');
        $this->command->newLine();
        $this->command->error('⚠️  IMPORTANT: Change these passwords in production!');
    }
}
