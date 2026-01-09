/**
 * User Type Definitions
 */

import type { RoleType } from './Menu';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  current_team_id: number | null;
  profile_photo_path: string | null;
  profile_photo_url?: string;
  two_factor_enabled: boolean;
  roles?: Role[];
  permissions?: Permission[];
  created_at: string;
  updated_at: string;
}

export interface Role {
  id: number;
  name: RoleType;
  guard_name: string;
  created_at: string;
  updated_at: string;
  permissions?: Permission[];
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

export interface AuthState {
  user: User | null;
  roles: string[];
  permissions: string[];
  isAuthenticated: boolean;
}
