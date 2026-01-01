<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialSuperAdminSeeder extends Seeder
{
    /**
     * Create the initial super admin user.
     * This user has full system access and can manage everything.
     */
    public function run()
    {
        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@myclass.com',
            'password' => Hash::make('password'), // CHANGE THIS IN PRODUCTION!
            'role' => 'SuperAdmin',
            'is_active' => true,
            'email_verified_at' => now(),
            'school_id' => null, // Super admin is not tied to any school
        ]);

        // Assign super_admin role
        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->warn('   Email: admin@myclass.com');
        $this->command->warn('   Password: password');
        $this->command->error('   ⚠️  CHANGE THE PASSWORD IN PRODUCTION!');
    }
}
