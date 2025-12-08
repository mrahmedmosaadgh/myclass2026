/**
 * Menu configuration for teacher role
 */

import common from './common';

export default [
  common.dashboard,
  common.profile,
  common.privateChat,
  {
    title: 'My Classes',
    icon: 'class',
    to: '/classes'
  },
  {
    title: 'My Schedule',
    icon: 'schedule',
    to: 'teacher.timeline'
  },
  //  {
  //     title: 'My Schedule',
  //     icon: 'schedule',
  //     to: 'teacher.timeline.index'
  //   },

  {
    title: 'Assignments',
    icon: 'assignment',
    to: '/assignments/manage'
  },
  {
    title: 'Presentation Dashboard',
    icon: 'slideshow',
    to: '/lesson-presentation/dashboard'
  },
  {
    title: 'Create Lesson',
    icon: 'add_to_queue',
    to: '/lesson-presentation/edit'
  },
  {
    title: 'Student Preview',
    icon: 'visibility',
    to: '/lesson-presentation/student/lessons'
  },
  {
    title: 'Grade Book',
    icon: 'book',
    to: '/gradebook'
  },
  {
    title: 'Students',
    icon: 'group',
    to: '/students'
  }
];
