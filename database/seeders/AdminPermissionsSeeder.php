<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'manage users',
            'manage roles',
            'admin_add_hr',
            'admin_manage_school',
            'admin_manage_academic_year',
            'admin_manage_semester',
            'admin_manage_teacher',
            'admin_manage_student',
            'admin_manage_classroom',
            'admin_manage_schedule_copy',
            'admin_manage_schedule',
            'admin_manage_calendar',
            'manage_users',
            'manage_roles',
            'admin_add_hr',
            'admin_manage_school',
            'admin_manage_academic_year',
            'admin_manage_semester',
            'admin_manage_teacher',
            'admin_manage_student',
            'admin_manage_classroom',
            'admin_manage_schedule_copy',
            'admin_manage_schedule',
            'admin_manage_calendar',
            'admin_manage_school',
            'admin_manage_academic_year',
            'admin_manage_semester',
            'admin_manage_teacher',
            'admin_manage_student',
            'admin_manage_classroom',
            'admin_manage_schedule',
            'admin_manage_schedule',
            'admin_manage_calendar'

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Optionally, assign these permissions to admin role if it exists
        if ($adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first()) {
            $adminRole->syncPermissions($permissions);
        }
    }
}
