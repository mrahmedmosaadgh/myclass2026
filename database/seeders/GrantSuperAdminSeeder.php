<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrantSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'tuhn06837';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->command->error("User with email {$email} not found!");
            return;
        }

        // 1. Ensure Super Admin role exists
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // 2. Assign role to user
        if (!$user->hasRole('super_admin')) {
            $user->assignRole($role);
            $this->command->info("Granted 'super_admin' role to {$user->name} ({$user->email})");
        } else {
            $this->command->info("User {$user->name} already has 'super_admin' role.");
        }
        
        // 3. Optional: Sync all permissions to the role to be sure
        // (Usually Super Admin bypasses checks in AuthServiceProvider, but just in case)
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
        $this->command->info("Synced " . $permissions->count() . " permissions to 'super_admin' role.");

        // 4. Also update the local 'role' column if used by the app logic
        if ($user->role !== 'admin' && $user->role !== 'super_admin') {
             // We can set it to 'admin' or 'super_admin' depending on what the column enums allowed
             // Based on previous checks, 'admin' is valid.
             // $user->update(['role' => 'admin']); 
             // Leaving this alone for now to avoid side effects if not requested, 
             // but user requested "all permissions".
        }
    }
}
