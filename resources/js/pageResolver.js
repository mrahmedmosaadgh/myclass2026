/**
 * Custom page resolver for Inertia that ensures proper code splitting
 * Each page component is loaded as a separate chunk
 */
export function resolvePageComponent(pageName) {
    // Define explicit mappings for major sections to ensure proper splitting
    const pageSections = {
        // Admin sections
        'my_class/admin': () => import(`./Pages/my_class/admin/${pageName.replace('my_class/admin/', '')}.vue`),
        'my_class/super_admin': () => import(`./Pages/my_class/super_admin/${pageName.replace('my_class/super_admin/', '')}.vue`),
        'my_class/teacher': () => import(`./Pages/my_class/teacher/${pageName.replace('my_class/teacher/', '')}.vue`),
        'my_class/hr': () => import(`./Pages/my_class/hr/${pageName.replace('my_class/hr/', '')}.vue`),
        
        // Main sections
        'Admin': () => import(`./Pages/Admin/${pageName.replace('Admin/', '')}.vue`),
        'Auth': () => import(`./Pages/Auth/${pageName.replace('Auth/', '')}.vue`),
        'Chat': () => import(`./Pages/Chat/${pageName.replace('Chat/', '')}.vue`),
        'Conversations': () => import(`./Pages/Conversations/${pageName.replace('Conversations/', '')}.vue`),
        'CourseManagement': () => import(`./Pages/CourseManagement/${pageName.replace('CourseManagement/', '')}.vue`),
        'Dashboard': () => import(`./Pages/Dashboard/${pageName.replace('Dashboard/', '')}.vue`),
        'Developer': () => import(`./Pages/developer/${pageName.replace('developer/', '')}.vue`),
        'Documentation': () => import(`./Pages/Documentation/${pageName.replace('Documentation/', '')}.vue`),
        'Firebase': () => import(`./Pages/Firebase/${pageName.replace('Firebase/', '')}.vue`),
        'Lessons': () => import(`./Pages/Lessons/${pageName.replace('Lessons/', '')}.vue`),
        'Notifications': () => import(`./Pages/Notifications/${pageName.replace('Notifications/', '')}.vue`),
        'PrivateChat': () => import(`./Pages/PrivateChat/${pageName.replace('PrivateChat/', '')}.vue`),
        'Profile': () => import(`./Pages/Profile/${pageName.replace('Profile/', '')}.vue`),
        'Qudrat': () => import(`./Pages/Qudrat/${pageName.replace('Qudrat/', '')}.vue`),
        'QuizManagement': () => import(`./Pages/QuizManagement/${pageName.replace('QuizManagement/', '')}.vue`),
        'Student': () => import(`./Pages/Student/${pageName.replace('Student/', '')}.vue`),
        'Students': () => import(`./Pages/Students/${pageName.replace('Students/', '')}.vue`),
        'Teacher': () => import(`./Pages/Teacher/${pageName.replace('Teacher/', '')}.vue`),
        'Teachers': () => import(`./Pages/Teachers/${pageName.replace('Teachers/', '')}.vue`),
        'WeeklyPlans': () => import(`./Pages/WeeklyPlans/${pageName.replace('WeeklyPlans/', '')}.vue`),
        'Academy': () => import(`./Pages/academy/${pageName.replace('academy/', '')}.vue`),
        'DailyTasks': () => import(`./Pages/dailyTasks/${pageName.replace('dailyTasks/', '')}.vue`),
        'Modules': () => import(`./Pages/modules/${pageName.replace('modules/', '')}.vue`),
        'MyClassV2': () => import(`./Pages/myclass_v2/${pageName.replace('myclass_v2/', '')}.vue`),
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