<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignAllRolesToUser19Seeder extends Seeder
{
    /**
     * Assign all available roles to user ID 19
     */
    public function run()
    {
        $user = User::find(19);
        
        if (!$user) {
            $this->command->error('❌ User ID 19 not found!');
            return;
        }

        // All available roles in the system
        $allRoles = [
            'super_admin',
            'admin',
            'hr_admin',
            'supervisor',
            'teacher',
            'student',
            'parent',
            'user'
        ];

        // Assign all roles to user
        $user->syncRoles($allRoles);

        $this->command->info('✅ Successfully assigned all roles to user ID 19 (' . $user->email . ')');
        $this->command->info('   Assigned roles: ' . implode(', ', $allRoles));
        $this->command->info('');
        $this->command->info('   User can now access:');
        $this->command->info('   - /admin/user_management');
        $this->command->info('   - /acadimy/user-manager');
        $this->command->info('   - All role-based routes');
    }
}
