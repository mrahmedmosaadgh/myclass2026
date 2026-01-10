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
        // 1. SuperSystem (Developer Tools)
        $superSystem = Menu::firstOrCreate(
            ['route' => 'v2.super-system.dashboard'],
            [
                'label' => 'Super System',
                'module' => 'super-system',
                'icon' => 'build',
                'order' => 900,
                'is_active' => true,
                'role_specific' => 'SuperSystem',
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

        // 2. SystemAdmin (Platform Management)
        $systemAdmin = Menu::firstOrCreate(
            ['route' => 'v2.system-admin.dashboard'],
            [
                'label' => 'System Admin',
                'module' => 'system-admin',
                'icon' => 'admin_panel_settings',
                'order' => 100,
                'is_active' => true,
                'role_specific' => 'SystemAdmin',
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

        // 3. SchoolAdmin (School Management)
        $schoolAdmin = Menu::firstOrCreate(
            ['route' => 'v2.school-admin.dashboard'],
            [
                'label' => 'School Admin',
                'module' => 'school-admin',
                'icon' => 'domain',
                'order' => 200,
                'is_active' => true,
                'role_specific' => 'SchoolAdmin',
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
    }

    private function createChildren(Menu $parent, array $children): void
    {
        foreach ($children as $child) {
            Menu::firstOrCreate(
                ['route' => $child['route']],
                array_merge($child, [
                    'parent_id' => $parent->id,
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
