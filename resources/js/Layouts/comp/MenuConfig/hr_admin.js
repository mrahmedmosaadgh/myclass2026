/**
 * Menu configuration for HR Admin role
 */

import common from './common';

export default [
    common.dashboard,
    common.profile,
    common.privateChat,

    // My Schools (Primary Workspace)
    {
        title: 'My Schools',
        icon: 'business',
        to: '/admin/my-schools',
    },

    // Notifications
    {
        title: 'Notifications',
        icon: 'notifications',
        children: [
            {
                title: 'All Notifications',
                icon: 'notifications',
                to: '/notifications',
            }
        ]
    },

    // They might need access to HR-specific settings or reports later
];
