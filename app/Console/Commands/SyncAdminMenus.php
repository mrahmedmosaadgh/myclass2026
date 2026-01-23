<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use Illuminate\Support\Str;

class SyncAdminMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Admin routes into the Menu database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning routes...');

        $routes = Route::getRoutes(); 
        $count = 0;

        foreach ($routes as $route) {
            $name = $route->getName();

            // Filter for admin routes only
            // Skip api.admin routes if they shouldn't be in the sidebar (usually api routes don't have 'admin.' prefix unless named explicitly)
            // Just focus on 'admin.' prefix for now.
            if (!$name || !Str::startsWith($name, 'admin.')) {
                continue;
            }

            // Skip internal/debug routes if any
            // Skip "admin.dashboard" if it's already handled specially or treat it as root
            
            // Parse structure: admin.{resource}.{action}
            // e.g., admin.students.index
            // e.g., admin.schedule-dailies.index
            
            $parts = explode('.', $name);
            
            // Expected format at least: admin.something
            if (count($parts) < 2) continue;

            $resourceKey = $parts[1]; // e.g. 'students', 'schedule-dailies'
            $action = $parts[2] ?? 'index'; // e.g. 'index', 'create'

            // Generate Parent Label
            $parentLabel = Str::headline($resourceKey); // "Schedule Dailies", "Students"

            // 1. Find or Create Parent Menu
            $parentLabelAr = $this->translateToArabic($parentLabel);
            $parentMenu = Menu::firstOrCreate(
                [
                    'label' => $parentLabel, 
                    'parent_id' => null,
                ],
                [
                    'label_ar' => $parentLabelAr,
                    'module' => 'administration',
                    'icon' => $this->guessIcon($resourceKey),
                    'order' => 99,
                    'is_active' => true,
                    'role_specific' => 'admin',
                    'v2_enabled' => true,
                    'route' => null, 
                ]
            );

            // Ensure parent is v2 enabled if it existed
            if (!$parentMenu->v2_enabled || !$parentMenu->label_ar) {
                $parentMenu->update([
                    'v2_enabled' => true, 
                    'role_specific' => 'admin',
                    'label_ar' => $parentMenu->label_ar ?? $parentLabelAr
                ]);
            }

            // 2. Create Child Menu Item
            // Label: "List" (for index), "Create", etc.
            // Improve label generation for deeper routes to avoid duplicates
            // e.g. admin.hr.setup-wizard.default-data -> "Setup Wizard Default Data"
            $remainingParts = array_slice($parts, 2);
            $childLabel = Str::headline(implode(' ', $remainingParts));
            
            if (empty($childLabel) || $childLabel === 'Index') $childLabel = 'List';
            
            $childLabelAr = $this->translateToArabic($childLabel);

            // Skip 'show', 'update', 'store', 'destroy', 'edit' usually? 
            // Menus usually only link to GET routes that show pages.
            // Skip non-GET routes
            if (!in_array('GET', $route->methods())) {
                continue;
            }
            
            // Skip parameter routes for menus? e.g. admin.users.edit needs {user}
            if ($this->hasRequiredParameters($route)) {
               continue;
            }

            Menu::updateOrCreate(
                [
                    'route' => $name, // Match strictly by route (unique key)
                ],
                [
                    'parent_id' => $parentMenu->id, // Update parent if it moved
                    'label' => $childLabel,
                    'label_ar' => $childLabelAr,
                    'module' => 'administration',
                    'icon' => $this->guessIcon($action),
                    'order' => 1,
                    'is_active' => true,
                    'role_specific' => 'admin',
                    'v2_enabled' => true,
                    'requires_context' => false,
                ]
            );

            $this->line("Synced: $parentLabel -> $childLabel ($name)");
            $count++;
        }

        $this->info("Sync complete! $count menu items processed.");
        
        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();
        $this->info("Cache cleared.");
    }

    private function translateToArabic($text)
    {
        $map = [
            'List' => 'قائمة',
            'Create' => 'إضافة',
            'Edit' => 'تعديل',
            'Show' => 'عرض',
            'Delete' => 'حذف',
            'Students' => 'الطلاب',
            'Teachers' => 'المعلمين',
            'Classrooms' => 'الفصول',
            'Dashboard' => 'لوحة التحكم',
            'Settings' => 'الإعدادات',
            'Reports' => 'التقارير',
            'Attendance' => 'الحضور',
            'Schedule' => 'الجدول',
            'Schedules' => 'الجداول',
            'Schedule Dailies' => 'الجداول اليومية',
            'Academic Structure' => 'الهيكل الأكاديمي',
            'Course Management' => 'إدارة المواد',
            'School Branding' => 'هوية المدرسة',
            'User & Role Management' => 'إدارة المستخدمين والأدوار',
            'Schools Management' => 'إدارة المدارس',
            'Developer Tools' => 'أدوات المطور',
            'Hr' => 'الموارد البشرية',
            'Curriculum' => 'المناهج',
            'Academic Calendar' => 'التقويم الأكاديمي',
            'Subject' => 'المواد',
            'Stage' => 'المراحل',
            'Grade' => 'الصفوف',
            'Semester' => 'الفصول الدراسية',
            'Calendar' => 'التقويم',
            'Question Banks' => 'بنك الأسئلة',
            'Behaviors' => 'السلوكيات',
            'Import Page' => 'استيراد',
            'Export' => 'تصدير',
            'Download Template' => 'تحميل النموذج',
            'Download Template With Classroom' => 'تحميل النموذج مع الفصول',
            'Filtered' => 'تصفية',
            'Classroom Mapping Suggestions' => 'اقتراحات ربط الفصول',
            'Setup Wizard' => 'معالج الإعداد',
            'Setup Wizard Default Data' => 'بيانات معالج الإعداد الافتراضية',
            'My Schools Index' => 'مدارسي',
            'Reward Sys' => 'نظام المكافآت',
            'Classroom Subject Teachers' => 'معلمي مواد الفصول',
            'Menus' => 'القوائم',
        ];

        // Direct match
        if (isset($map[$text])) return $map[$text];

        // Partial match for "List" etc
        // Not perfect, but better than nothing
        return $text;
    }

    private function hasRequiredParameters($route)
    {
        // Simple check for {param}
        return preg_match('/\{[a-zA-Z0-9_]+\}/', $route->uri());
    }

    private function guessIcon($key)
    {
        $icons = [
            'students' => 'groups',
            'teachers' => 'school',
            'classrooms' => 'meeting_room',
            'dashboard' => 'dashboard',
            'settings' => 'settings',
            'schedule' => 'calendar_today',
            'reports' => 'bar_chart',
            'create' => 'add_circle',
            'list' => 'list',
            'index' => 'list',
            'import' => 'upload_file',
            'attendance' => 'fact_check',
            'users' => 'group',
            'roles' => 'admin_panel_settings',
            'calendar' => 'event',
            'schedule-dailies' => 'update', // Guessing icon
        ];

        foreach ($icons as $match => $icon) {
            if (Str::contains($key, $match, true)) {
                return $icon;
            }
        }

        return 'circle'; // Default bullet
    }
}
