/**
 * Menu configuration for admin role
 */

import common from './common';

export default [
  common.dashboard,
  common.profile,
  common.privateChat,

  // Notifications Section
  {
    title: 'Notifications',
    icon: 'notifications',
    children: [
      {
        title: 'All Notifications',
        icon: 'notifications',
        to: '/notifications',
      },
      {
        title: 'Notification Settings',
        icon: 'notifications_active',
        to: '/notifications/settings',
      },
      {
        title: 'Send Notifications',
        icon: 'send',
        to: '/notifications',
      }
    ]
  },

  // User Management Section
  {
    title: 'User Management ',
    icon: 'settings',
    to: '/admin/user_management',
  },

  // School Management Section
  {
    title: 'School Management',
    icon: 'school',
    children: [
      {
        title: 'Schools',
        icon: 'domain',
        children: [
          {
            title: 'All Schools',
            icon: 'list',
            to: '/admin/school',
          },
          {
            title: 'Add School',
            icon: 'add',
            to: '/admin/school/create',
          },
          {
            title: 'Import Schools',
            icon: 'upload',
            to: '/admin/school/import',
          },
          {
            title: 'Export Schools',
            icon: 'download',
            to: '/admin/school/export',
          }
        ]
      },
      {
        title: 'School Structure',
        icon: 'grid_view',
        children: [
          {
            title: 'Sections',
            icon: 'view_quilt',
            to: '/admin/school_section',
          },
          {
            title: 'Stages',
            icon: 'stairs',
            to: '/admin/stage',
          },
          {
            title: 'Grades',
            icon: 'format_list_numbered',
            to: '/admin/grade',
          },
          {
            title: 'Classrooms',
            icon: 'school',
            to: '/admin/classroom',
          }
        ]
      },
    ]
  },

  // Academic Section
  {
    title: 'Academic Management',
    icon: 'menu_book',
    children: [
      {
        title: 'Subjects',
        icon: 'menu_book',
        to: '/admin/subject',
      },
      {
        title: 'Grade Subjects',
        icon: 'local_library',
        to: '/admin/grade-subject',
      },
      {
        title: 'Curriculum',
        icon: 'library_books',
        children: [
          {
            title: 'Curriculum Management',
            icon: 'book',
            to: '/admin/curriculum/management',
          },
          {
            title: 'Curriculum Lessons',
            icon: 'list_alt',
            to: '/admin/curriculum/lessons',
          },
          {
            title: 'Lesson Plans',
            icon: 'assignment',
            to: '/admin/curriculum/lesson-plans',
          },
          {
            title: 'Curriculum Maps',
            icon: 'map',
            to: '/admin/curriculum/maps',
          }
        ]
      },
      {
        title: 'Assessment',
        icon: 'quiz',
        children: [
          {
            title: 'Question Banks',
            icon: 'help',
            to: '/admin/question-banks',
          },
          {
            title: 'Semester Tests',
            icon: 'edit_document',
            to: '/admin/semester-test',
          }
        ]
      },
      {
        title: 'Class Subject Teachers',
        icon: 'school',
        to: '/admin/classroom-subject-teacher',
      }
    ]
  },

  // People Section
  {
    title: 'People',
    icon: 'people',
    children: [
      {
        title: 'Teachers',
        icon: 'manage_accounts',
        to: '/admin/teacher',
      },
      {
        title: 'Students',
        icon: 'school',
        to: '/admin/students',
      },
      {
        title: 'Parents',
        icon: 'group',
        to: '/admin/student-parent',
      },
      {
        title: 'HR',
        icon: 'person',
        to: '/admin/hr',
      },
    ]
  },

  // Schedule Section
  {
    title: 'Schedule',
    icon: 'calendar_month',
    children: [
      {
        title: 'Academic Years',
        icon: 'calendar_today',
        to: '/admin/academic-year',
      },
      {
        title: 'Semesters',
        icon: 'date_range',
        to: '/admin/semester',
      },
      {
        title: 'Calendar',
        icon: 'calendar_month',
        to: '/admin/calendar',
      },
      {
        title: 'Schedules',
        icon: 'schedule',
        to: '/admin/schedules',
      },
      {
        title: 'Daily Schedules',
        icon: 'calendar_today',
        to: '/admin/schedule-dailies',
      },
      {
        title: 'Schedule Copies',
        icon: 'content_copy',
        to: '/admin/schedule-copies',
      },
      {
        title: 'Period Details',
        icon: 'access_time',
        to: '/admin/period-details',
      },
    ]
  },

  // Attendance Section
  {
    title: 'Attendance',
    icon: 'event_available',
    to: '/admin/attendance',
  },

  // Import/Export Tools
  {
    title: 'Import/Export',
    icon: 'import_export',
    children: [
      {
        title: 'Import Classroom-Subject-Teacher',
        icon: 'upload',
        to: '/admin/classroom-subject-teachers/import'
      },
      {
        title: 'Import Data',
        icon: 'file_upload',
        to: '/classroom-subject-teacher/import-page'
      }
    ]
  },

  // Reports & Analytics
  {
    title: 'Reports & Analytics',
    icon: 'bar_chart',
    children: [
      {
        title: 'Academic Reports',
        icon: 'assessment',
        to: '/reports/academic',
      },
      {
        title: 'Attendance Reports',
        icon: 'event_available',
        to: '/reports/attendance',
      },
      {
        title: 'General Reports',
        icon: 'bar_chart',
        to: '/reports',
      }
    ]
  },

  // System Management
  {
    title: 'System',
    icon: 'settings',
    children: [
      {
        title: 'Documentation',
        icon: 'description',
        to: '/admin/documentation',
      },
      {
        title: 'System Settings',
        icon: 'tune',
        to: '/admin/settings',
      }
    ]
  }


];
