<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class SuperSystemUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Create SuperSystem role
        $role = Role::firstOrCreate(['name' => 'SuperSystem', 'guard_name' => 'web']);
        
        // Give all permissions to SuperSystem for good measure (it's god mode)
        $role->givePermissionTo(Permission::all());

        // 2. Create Developer User
        $user = User::firstOrCreate(
            ['email' => 'developer@myclass.com'],
            [
                'name' => 'Super System Developer',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign Role
        $user->assignRole($role);
        // Also assign super_admin just in case legacy checks look for it
        $user->assignRole('super_admin');

        $this->command->info('✅ SuperSystem User created!');
        $this->command->warn('   Email: developer@myclass.com');
        $this->command->warn('   Password: password');
    }
}
