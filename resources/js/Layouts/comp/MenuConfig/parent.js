/**
 * Menu configuration for parent role
 */

import common from './common';

export default [
  common.dashboard,
  common.profile,
  {
    title: 'My Children',
    icon: 'child_care',
    to: '/children'
  },
  {
    title: 'Academic Progress',
    icon: 'trending_up',
    to: '/progress'
  },
  {
    title: 'Contact Teachers',
    icon: 'message',
    to: '/messages'
  }
];
