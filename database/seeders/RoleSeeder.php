<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates all roles and permissions for the school management system.
     */
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

        // Define all permissions
        $permissions = [
            // System Management (Super Admin only)
            'manage app',
            'manage system settings',
            'manage-menus',
            
            // User Management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // School Management
            'manage schools',
            'view schools',
            'create schools',
            'edit schools',
            'delete schools',
            
            // HR Management
            'manage hr',
            'view hr',
            
            // Teacher Management
            'manage teachers',
            'view teachers',
            'create teachers',
            'edit teachers',
            'delete teachers',
            'import teachers',
            
            // Student Management
            'manage students',
            'view students',
            'create students',
            'edit students',
            'delete students',
            'import students',
            
            // Academic Management
            'manage subjects',
            'manage grades',
            'manage classrooms',
            'manage schedules',
            'manage curriculum',
            
            // Teaching & Learning
            'create assignments',
            'grade assignments',
            'create course materials',
            'manage student grades',
            'view student progress',
            'access course materials',
            'submit assignments',
            'view grades',
            
            // Communication
            'communicate with students',
            'communicate with teachers',
            'communicate with parents',
            
            // Reports & Analytics
            'view reports',
            'generate reports',
            
            // Roles & Permissions
            'manage roles',
            'manage permissions',
            
            // Settings
            'manage settings',
            'view settings',
            
            // Other
            'review course content',
            'participate in forums',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Super Admin Role (Full Access)
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Create Admin Role (School-level admin)
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view schools',
            'edit schools',
            'manage teachers',
            'view teachers',
            'create teachers',
            'edit teachers',
            'delete teachers',
            'import teachers',
            'manage students',
            'view students',
            'create students',
            'edit students',
            'delete students',
            'import students',
            'manage subjects',
            'manage grades',
            'manage classrooms',
            'manage schedules',
            'manage curriculum',
            'view student progress',
            'view reports',
            'generate reports',
            'manage settings',
            'view settings',
            'communicate with teachers',
            'communicate with parents',
            'manage-menus',
        ]);

        // Create HR Admin Role
        $hrAdminRole = Role::create(['name' => 'hr_admin']);
        $hrAdminRole->givePermissionTo([
            'manage hr',
            'view hr',
            'manage schools',
            'view schools',
            'create schools',
            'edit schools',
            'manage users',
            'view users',
            'create users',
            'edit users',
            'manage teachers',
            'view teachers',
            'create teachers',
            'edit teachers',
            'import teachers',
            'view reports',
        ]);

        // Create Supervisor Role
        $supervisorRole = Role::create(['name' => 'supervisor']);
        $supervisorRole->givePermissionTo([
            'manage teachers',
            'view teachers',
            'view student progress',
            'review course content',
            'view reports',
            'communicate with teachers',
        ]);

        // Create Teacher Role
        $teacherRole = Role::create(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'create assignments',
            'grade assignments',
            'create course materials',
            'manage student grades',
            'communicate with students',
            'communicate with parents',
            'access course materials',
            'view students',
            'view student progress',
            'view grades',
            'participate in forums',
        ]);

        // Create Student Role
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'access course materials',
            'submit assignments',
            'view grades',
            'participate in forums',
        ]);

        // Create Parent Role
        $parentRole = Role::create(['name' => 'parent']);
        $parentRole->givePermissionTo([
            'view grades',
            'view student progress',
            'communicate with teachers',
        ]);

        // Create User Role (default, minimal permissions)
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);
        // No permissions for basic user role

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('   - super_admin: ' . $superAdminRole->permissions->count() . ' permissions');
        $this->command->info('   - admin: ' . $adminRole->permissions->count() . ' permissions');
        $this->command->info('   - hr_admin: ' . $hrAdminRole->permissions->count() . ' permissions');
        $this->command->info('   - supervisor: ' . $supervisorRole->permissions->count() . ' permissions');
        $this->command->info('   - teacher: ' . $teacherRole->permissions->count() . ' permissions');
        $this->command->info('   - student: ' . $studentRole->permissions->count() . ' permissions');
        $this->command->info('   - parent: ' . $parentRole->permissions->count() . ' permissions');
        $this->command->info('   - user: ' . $userRole->permissions->count() . ' permissions');
    }
}
