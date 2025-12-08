/**
 * Menu configurations aggregator
 * Import and exports all role-based menu configurations
 */

import guestMenu from './guest';
import studentMenu from './student';
import teacherMenu from './teacher';
import parentMenu from './parent';
import adminMenu from './admin';
import superadminMenu from './superadmin';
import developerMenu from './developer';

// Export all menus with their respective roles
const menuConfig = {
  guest: guestMenu,
  student: studentMenu,
  teacher: teacherMenu,
  parent: parentMenu,
  admin: adminMenu,
  super_admin: superadminMenu,
  developer: developerMenu
};

export default menuConfig;

/**
 * Helper function to get menu by role
 * @param {string} role - User role
 * @returns {Array} Menu items for the specified role or guest menu as fallback
 */
export function getMenuByRole(role) {
  if (!role || typeof role !== 'string') {
    return menuConfig.guest;
  }

  const normalizedRole = role.toLowerCase();
  return menuConfig[normalizedRole] || menuConfig.guest;
}
