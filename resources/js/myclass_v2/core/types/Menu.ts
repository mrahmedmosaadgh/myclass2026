/**
 * Menu Type Definitions
 * Based on existing menus table structure
 */

export interface Menu {
  id: number;
  label: string;
  route: string | null;
  permission: string | null;
  module: string;
  parent_id: number | null;
  order: number;
  icon: string | null;
  is_active: boolean;
  is_feature_flag: boolean;
  feature_flag_key: string | null;
  meta: MenuMeta | null;
  children?: Menu[];
  
  // V2 additions
  v2_component?: string;
  requires_context?: boolean;
  role_specific?: RoleType;
}

export interface MenuMeta {
  description?: string;
  badge?: string | number;
  color?: string;
  [key: string]: any;
}

export type RoleType = 
  | 'SuperSystem'
  | 'SystemAdmin'
  | 'SchoolAdmin'
  | 'Teacher'
  | 'Student'
  | 'Parent';

export type ModuleType = 
  | 'academics'
  | 'attendance'
  | 'administration'
  | 'course-management'
  | 'weekly-plans'
  | 'curriculum'
  | 'reports'
  | 'settings'
  | 'developer'
  | 'super-system'
  | 'system-admin'
  | 'school-admin';

export interface NavigationResponse {
  data: Menu[];
  version: string;
  cached_at?: string;
}
