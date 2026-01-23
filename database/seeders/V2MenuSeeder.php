<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class V2MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. SuperSystem (Developer Tools) -> super_admin
        $superSystem = Menu::firstOrCreate(
            ['route' => 'v2.super-system.dashboard'],
            [
                'label' => 'Super System',
                'module' => 'super-system',
                'icon' => 'build',
                'order' => 900,
                'is_active' => true,
                'role_specific' => 'super_admin',
                'v2_enabled' => true,
                'requires_context' => false,
            ]
        );

        $this->createChildren($superSystem, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.super-system.dashboard',
                'icon' => 'dashboard',
                'order' => 1,
            ],
            [
                'label' => 'Configuration',
                'route' => 'v2.super-system.config',
                'icon' => 'settings',
                'order' => 2,
            ],
            [
                'label' => 'Jobs Monitor',
                'route' => 'v2.super-system.jobs',
                'icon' => 'memory',
                'order' => 3,
            ],
            [
                'label' => 'System Logs',
                'route' => 'v2.super-system.logs',
                'icon' => 'article',
                'order' => 4,
            ],
        ]);

        // 2. SystemAdmin (Platform Management) -> super_admin
        $systemAdmin = Menu::firstOrCreate(
            ['route' => 'v2.system-admin.dashboard'],
            [
                'label' => 'System Admin',
                'module' => 'system-admin',
                'icon' => 'admin_panel_settings',
                'order' => 100,
                'is_active' => true,
                'role_specific' => 'super_admin',
                'v2_enabled' => true,
                'requires_context' => false,
            ]
        );

        $this->createChildren($systemAdmin, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.system-admin.dashboard',
                'icon' => 'analytics',
                'order' => 1,
            ],
            [
                'label' => 'Schools',
                'route' => 'v2.system-admin.schools.index',
                'icon' => 'school',
                'order' => 2,
            ],
            [
                'label' => 'Global Users',
                'route' => 'v2.system-admin.users.index',
                'icon' => 'group',
                'order' => 3,
            ],
        ]);

        // 3. SchoolAdmin (School Management) -> admin
        $schoolAdmin = Menu::firstOrCreate(
            ['route' => 'v2.school-admin.dashboard'],
            [
                'label' => 'School Admin',
                'module' => 'school-admin',
                'icon' => 'domain',
                'order' => 200,
                'is_active' => true,
                'role_specific' => 'admin',
                'requires_context' => true,
                'v2_enabled' => true,
            ]
        );

        $this->createChildren($schoolAdmin, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.school-admin.dashboard',
                'icon' => 'dashboard',
                'order' => 1,
            ],
            [
                'label' => 'Academics',
                'route' => 'v2.school-admin.academics.index',
                'icon' => 'menu_book',
                'order' => 2,
            ],
            [
                'label' => 'Staff & Students',
                'route' => 'v2.school-admin.people.index',
                'icon' => 'people',
                'order' => 3,
            ],
        ]);

        // 4. Teacher (Classroom Management) -> teacher
        $teacher = Menu::firstOrCreate(
            ['route' => 'v2.teacher.dashboard'],
            [
                'label' => 'Teacher Portal',
                'module' => 'teacher',
                'icon' => 'school',
                'order' => 300,
                'is_active' => true,
                'role_specific' => 'teacher',
                'requires_context' => true,
                'v2_enabled' => true,
            ]
        );

        $this->createChildren($teacher, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.teacher.dashboard',
                'icon' => 'dashboard',
                'order' => 1,
            ],
            [
                'label' => 'My Schedule',
                'route' => 'v2.teacher.schedule',
                'icon' => 'calendar_today',
                'order' => 2,
            ],
            [
                'label' => 'My Classes',
                'route' => 'v2.teacher.classrooms.index',
                'icon' => 'class',
                'order' => 3,
            ],
            [
                'label' => 'Assignments',
                'route' => 'v2.teacher.assignments.index',
                'icon' => 'assignment',
                'order' => 4,
            ],
        ]);

        // 5. Student (Learning Portal) -> student
        $student = Menu::firstOrCreate(
            ['route' => 'v2.student.dashboard'],
            [
                'label' => 'Student Portal',
                'module' => 'student',
                'icon' => 'backpack',
                'order' => 400,
                'is_active' => true,
                'role_specific' => 'student',
                'requires_context' => true,
                'v2_enabled' => true,
            ]
        );

        $this->createChildren($student, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.student.dashboard',
                'icon' => 'dashboard',
                'order' => 1,
            ],
            [
                'label' => 'My Courses',
                'route' => 'v2.student.courses.index',
                'icon' => 'menu_book',
                'order' => 2,
            ],
            [
                'label' => 'Schedule',
                'route' => 'v2.student.schedule',
                'icon' => 'schedule',
                'order' => 3,
            ],
            [
                'label' => 'Homework',
                'route' => 'v2.student.homework',
                'icon' => 'edit_note',
                'order' => 4,
            ],
        ]);

        // 6. Parent (Family Portal) -> parent
        $parent = Menu::firstOrCreate(
            ['route' => 'v2.parent.dashboard'],
            [
                'label' => 'Parent Portal',
                'module' => 'parent',
                'icon' => 'family_restroom',
                'order' => 500,
                'is_active' => true,
                'role_specific' => 'parent',
                'requires_context' => true,
                'v2_enabled' => true,
            ]
        );

        $this->createChildren($parent, [
            [
                'label' => 'Dashboard',
                'route' => 'v2.parent.dashboard',
                'icon' => 'dashboard',
                'order' => 1,
            ],
            [
                'label' => 'My Children',
                'route' => 'v2.parent.children',
                'icon' => 'child_care',
                'order' => 2,
            ],
            [
                'label' => 'Fee & Payments',
                'route' => 'v2.parent.payments',
                'icon' => 'payments',
                'order' => 3,
            ],
        ]);
        // 7. Sync generic Admin routes (catch-all for admin.*)
        \Illuminate\Support\Facades\Artisan::call('menu:sync');
    }

    private function createChildren(Menu $parent, array $children): void
    {
        foreach ($children as $child) {
            Menu::firstOrCreate(
                [
                    'route' => $child['route'],
                    'parent_id' => $parent->id
                ],
                array_merge($child, [
                    'module' => $parent->module,
                    'is_active' => true,
                    'role_specific' => $parent->role_specific,
                    'requires_context' => $parent->requires_context,
                    'v2_enabled' => true,
                ])
            );
        }
    }
}
