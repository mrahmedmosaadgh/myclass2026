<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddManageMenusPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permission
        $permission = Permission::firstOrCreate(['name' => 'manage-menus']);

        // Assign to Super Admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permission);
            $this->command->info('Granted manage-menus to super_admin');
        }

        // Assign to Admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
            $this->command->info('Granted manage-menus to admin');
        }
    }
}
