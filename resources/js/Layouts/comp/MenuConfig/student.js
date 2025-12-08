/**
 * Menu configuration for student role
 */

import common from './common';

export default [
  common.dashboard,
  common.profile,
  {
    title: 'Courses',
    icon: 'menu_book',
    to: '/courses'
  },
  {
    title: 'Assignments',
    icon: 'assignment',
    to: '/assignments'
  },
  {
    title: 'Grades',
    icon: 'show_chart',
    to: '/grades'
  }


  ,
  
];
