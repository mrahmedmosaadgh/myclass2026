<?php

return [
    [
        'id' => 'dashboard',
        'label' => ['en' => 'Dashboard', 'ar' => 'لوحة القيادة'],
        'route' => 'v2.parent.dashboard',
        'icon' => 'dashboard',
    ],
    // Add more parent routes as they become available/discovered
    [
        'id' => 'conversations',
        'label' => ['en' => 'Messages', 'ar' => 'الرسائل'],
        'route' => 'conversations.index',
        'icon' => 'chat',
    ],
];
