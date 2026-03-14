/**
 * Custom page resolver for Inertia that ensures proper code splitting
 * Each page component is loaded as a separate chunk
 */
export function resolvePageComponent(pageName) {
    // Define explicit mappings for major sections to ensure proper splitting
    const pageSections = {
        // Admin sections
        'my_class/admin/Students': () => import(`./Pages/old_features/my_class/admin/Students/${pageName.replace('my_class/admin/Students/', '')}.vue`),
        'my_class/admin/Teacher': () => import(`./Pages/old_features/my_class/admin/Teacher/${pageName.replace('my_class/admin/Teacher/', '')}.vue`),
        'my_class/admin/StudentParents': () => import(`./Pages/old_features/my_class/admin/StudentParents/${pageName.replace('my_class/admin/StudentParents/', '')}.vue`),
        'my_class/admin': () => import(`./Pages/my_class/admin/${pageName.replace('my_class/admin/', '')}.vue`),
        'my_class/super_admin': () => import(`./Pages/old_features/my_class/super_admin/${pageName.replace('my_class/super_admin/', '')}.vue`),
        'my_class/teacher': () => import(`./Pages/my_class/teacher/${pageName.replace('my_class/teacher/', '')}.vue`),
        'my_class': () => import(`./Pages/my_class/${pageName.replace('my_class/', '')}.vue`),
        'my_class/hr': () => import(`./Pages/my_class/hr/${pageName.replace('my_class/hr/', '')}.vue`),

        // Curriculum management sections
        'myclass2026/roles/school-admin/curriculum': () => import(`./Pages/myclass2026/roles/school-admin/curriculum/${pageName.replace('myclass2026/roles/school-admin/curriculum/', '')}.vue`),
        'myclass2026/roles/school-admin/weekly_system': () => import(`./Pages/myclass2026/roles/school-admin/weekly_system/${pageName.replace('myclass2026/roles/school-admin/weekly_system/', '')}.vue`),
        'myclass2026/roles/teacher/weekly_system': () => import(`./Pages/myclass2026/roles/teacher/weekly_system/${pageName.replace('myclass2026/roles/teacher/weekly_system/', '')}.vue`),

        // Courses & Learning Modules
        'Courses': () => import(`./Pages/Courses/${pageName.replace('Courses/', '')}.vue`),

        // Curriculum
        'Curriculum': () => import(`./Pages/Curriculum/${pageName.replace('Curriculum/', '')}.vue`),
        'curriculum': () => import(`./Pages/curriculum/${pageName.replace('curriculum/', '')}.vue`),
        'myclass2026/roles/school-admin/curriculum': () => import(`./Pages/myclass2026/roles/school-admin/curriculum/${pageName.replace('myclass2026/roles/school-admin/curriculum/', '')}.vue`),
        'myclass2026/roles/teacher/curriculum': () => import(`./Pages/myclass2026/roles/teacher/curriculum/${pageName.replace('myclass2026/roles/teacher/curriculum/', '')}.vue`),
        'myclass2026/roles/student/curriculum': () => import(`./Pages/myclass2026/roles/student/curriculum/${pageName.replace('myclass2026/roles/student/curriculum/', '')}.vue`),

        // Modules
        'old_features/Admin/SkillManagement': () => import(`./Pages/old_features/Admin/SkillManagement/${pageName.replace('old_features/Admin/SkillManagement/', '')}.vue`),
        'Admin': () => import(`./Pages/old_features/Admin/${pageName.replace('Admin/', '')}.vue`),
        'Auth': () => import(`./Pages/Auth/${pageName.replace('Auth/', '')}.vue`),
        'Chat': () => import(`./Pages/old_features/Chat/${pageName.replace('Chat/', '')}.vue`),
        'PrivateChat': () => import(`./Pages/old_features/PrivateChat/${pageName.replace('PrivateChat/', '')}.vue`),
        'Conversations': () => import(`./Pages/old_features/Conversations/${pageName.replace('Conversations/', '')}.vue`),
        'Components': () => import(`./Pages/Components/${pageName.replace('Components/', '')}.vue`),
        'CourseManagement': () => import(`./Pages/old_features/CourseManagement/${pageName.replace('CourseManagement/', '')}.vue`),
        'Dashboard': () => import(`./Pages/Dashboard/${pageName.replace('Dashboard/', '')}.vue`),
        'Developer': () => import(`./Pages/developer/${pageName.replace('developer/', '')}.vue`),
        'Documentation': () => import(`./Pages/Documentation/${pageName.replace('Documentation/', '')}.vue`),
        'Firebase': () => import(`./Pages/Firebase/${pageName.replace('Firebase/', '')}.vue`),
        'Notifications': () => import(`./Pages/Notifications/${pageName.replace('Notifications/', '')}.vue`),
        'PrivateChat': () => import(`./Pages/PrivateChat/${pageName.replace('PrivateChat/', '')}.vue`),
        'Profile': () => import(`./Pages/Profile/${pageName.replace('Profile/', '')}.vue`),
        'Qudrat': () => import(`./Pages/Qudrat/${pageName.replace('Qudrat/', '')}.vue`),

        'Student': () => import(`./Pages/Student/${pageName.replace('Student/', '')}.vue`),
        'Students': () => import(`./Pages/Students/${pageName.replace('Students/', '')}.vue`),
        'Teacher': () => import(`./Pages/Teacher/${pageName.replace('Teacher/', '')}.vue`),
        'Teachers': () => import(`./Pages/Teachers/${pageName.replace('Teachers/', '')}.vue`),
        'VocabularyFlashcards': () => import(`./Pages/old_features/VocabularyFlashcards/${pageName.replace('VocabularyFlashcards/', '')}.vue`),
        'WeeklyPlans': () => import(`./Pages/old_features/WeeklyPlans/${pageName.replace('WeeklyPlans/', '')}.vue`),
        'Academy': () => import(`./Pages/academy/${pageName.replace('academy/', '')}.vue`),
        'DailyTasks': () => import(`./Pages/old_features/dailyTasks/${pageName.replace('dailyTasks/', '')}.vue`),
        'Modules': () => import(`./Pages/modules/${pageName.replace('modules/', '')}.vue`),
        'MyClassV2': () => import(`./Pages/myclass_v2/${pageName.replace('myclass_v2/', '')}.vue`),
        'my_table_mnger/reward_sys': () => import(`./Pages/old_features/my_table_mnger/reward_sys/${pageName.replace('my_table_mnger/reward_sys/', '')}.vue`),
        'MyTableManager': () => import(`./Pages/my_table_mnger/${pageName.replace('my_table_mnger/', '')}.vue`),
        'PrintHTML': () => import(`./Pages/print_html/${pageName.replace('print_html/', '')}.vue`),
        'ProjectManager': () => import(`./Pages/project_manager/${pageName.replace('project_manager/', '')}.vue`),
    };

    // Check if the page belongs to a specific section
    for (const [section, resolver] of Object.entries(pageSections)) {
        if (pageName.startsWith(section + '/')) {
            return resolver();
        }
    }

    // Fallback to generic import with glob - this will create individual chunks
    return import(`./Pages/${pageName}.vue`);
}