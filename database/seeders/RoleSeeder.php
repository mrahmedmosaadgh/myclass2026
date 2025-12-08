<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clean existing roles and permissions
        Schema::disableForeignKeyConstraints();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        // Define permissions once
        $permissions = [
            // Admin Permissions
            'manage users',
            'manage courses',
            'manage roles',
            'manage settings',
            'view reports',

            // Supervisor Permissions
            'manage teachers',
            'view student progress',
            'review course content',

            // Teacher Permissions
            'create assignments',
            'grade assignments',
            'create course materials',
            'manage student grades',
            'communicate with students',

            // Student Permissions
            'access course materials',
            'submit assignments',
            'view grades',
            'participate in forums',

            // Parent Permissions
            'view student grades',
            'communicate with teachers',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
        // Create roles and assign permissions

        //++++++++++++++++++++++++++++++++++++++++++++++++
        $adminRole = Role::create(['name' => 'super_admin']);

        $permissions = [
            'manage app',
        ];

        // Iterate over permissions and create them if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        $adminRole->givePermissionTo(Permission::all());

        //++++++++++++++++++++++++++++++++++++++++++++++++

        $supervisorRole = Role::create(['name' => 'supervisor']);
        $supervisorRole->givePermissionTo([
            'manage teachers',
            'view student progress',
            'review course content',
            'view reports',
        ]);

        $teacherRole = Role::create(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'create assignments',
            'grade assignments',
            'create course materials',
            'manage student grades',
            'communicate with students',
            'access course materials',
            'submit assignments',
            'view grades',
            'participate in forums',
        ]);

        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'access course materials',
            'submit assignments',
            'view grades',
            'participate in forums',
        ]);

        $parentRole = Role::create(['name' => 'parent']);
        $parentRole->givePermissionTo([
            'view student grades',
            'view student progress',
            'communicate with teachers',
        ]);
        // $parentRole = Role::create(['name' => 'user']);
        $adminRole = Role::create(['name' => 'user', 'guard_name' => 'web']);


    }
}
