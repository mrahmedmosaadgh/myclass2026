/**
 * Menu configuration for superadmin role
 */

import adminMenu from './admin';

export default [
  ...adminMenu,
  {
    title: 'Documentation Portal',
    icon: 'library_books',
    children: [
      {
        title: 'View All Docs',
        icon: 'visibility',
        to: '/admin/documentation-portal'
      },
      {
        title: 'Manage Database Docs',
        icon: 'edit',
        to: '/admin/documentation'
      }
    ]
  },
  {
    title: 'Developer Tools',
    icon: 'code',
    children: [
      {
        title: 'System Routes',
        icon: 'link',
        to: '/developer/system-routes'
      }
    ]
  },
  {
    title: 'System Settings',
    icon: 'settings',
    to: '/settings'
  },
  {
    title: 'Logs',
    icon: 'description',
    to: '/logs'
  },
  {
    title: 'Backup',
    icon: 'storage',
    to: '/backup'
  }
];
