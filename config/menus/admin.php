<?php

return [
    [
        'id' => 'dashboard',
        'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
        'route' => 'admin.dashboard',
        'icon' => 'dashboard',
    ],
    [
        'id' => 'schools',
        'label' => ['en' => 'Schools', 'ar' => 'المدارس'],
        'route' => 'admin.schools.index',
        'icon' => 'account_balance',
        'permission' => 'admin.schools.view',
    ],
    [
        'id' => 'users',
        'label' => ['en' => 'Users', 'ar' => 'المستخدمون'],
        'route' => 'admin.users.index',
        'icon' => 'people',
        'permission' => 'admin.users.view',
    ],
    [
        'id' => 'menus',
        'label' => ['en' => 'Menu Management', 'ar' => 'إدارة القوائم'],
        'route' => 'admin.menus.index',
        'icon' => 'menu',
        'permission' => 'admin.menus.manage',
    ],
    [
        'id' => 'settings',
        'label' => ['en' => 'System Settings', 'ar' => 'إعدادات النظام'],
        'route' => 'admin.settings.index',
        'icon' => 'settings',
        'permission' => 'admin.settings.manage',
    ],
];
