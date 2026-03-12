<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class MenuService
{
    /**
     * Cache tag for menus
     */
    const CACHE_TAG = 'menus';
    
    /**
     * Cache TTL in seconds (24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Get the menu tree for a active user context.
     * This retrieves the cached structure for the role and then filters by permissions.
     *
     * @param string|null $role
     * @param bool $isV2
     * @param bool $includeInactive
     * @return Collection
     */
    public function getMenus(?string $role = null, bool $isV2 = false, bool $includeInactive = false): Collection
    {
        $structure = $this->getMenuStructure($role, $isV2, $includeInactive);
        
        // Filter by user permissions
        return $this->filterByPermissions($structure);
    }

    /**
     * Get the raw menu structure for a specific role (Cached).
     *
     * @param string|null $role
     * @param bool $isV2
     * @param bool $includeInactive
     * @return Collection
     */
    public function getMenuStructure(?string $role = null, bool $isV2 = false, bool $includeInactive = false): Collection
    {
        $cacheKey = $this->getCacheKey($role, $isV2, $includeInactive);

        // Use tags if supported (e.g. Redis), otherwise standard cache
        $cache = Cache::supportsTags() ? Cache::tags([self::CACHE_TAG]) : Cache::store();

        return $cache->remember($cacheKey, self::CACHE_TTL, function () use ($role, $isV2, $includeInactive) {
            return $this->buildMenuQuery($role, $isV2, $includeInactive);
        });
    }

    /**
     * Invalidate all menu caches.
     */
    public function clearCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->flush();
        } else {
            // Fallback: Clear keys for known roles
            $roles = ['super_admin', 'admin', 'teacher', 'student', 'parent', 'guest'];
            foreach ($roles as $role) {
                 Cache::forget($this->getCacheKey($role, true, false)); // V2
                 Cache::forget($this->getCacheKey($role, false, false)); // V1
                 Cache::forget($this->getCacheKey($role, true, true)); // V2 Inactive
                 Cache::forget($this->getCacheKey($role, false, true)); // V1 Inactive
            }
            // Clear global
            Cache::forget($this->getCacheKey(null, true, false));
            Cache::forget($this->getCacheKey(null, false, false));
            Cache::forget($this->getCacheKey(null, true, true));
            Cache::forget($this->getCacheKey(null, false, true));
        }
    }

    /**
     * Build the query and structure.
     */
    protected function buildMenuQuery(?string $role, bool $isV2, bool $includeInactive = false): Collection
    {
        $query = Menu::query()
            ->when(!$includeInactive, function($q) {
                $q->where('is_active', true);
            })
            ->whereNull('parent_id')
            ->with(['children' => function ($q) use ($role, $isV2, $includeInactive) {
                $q->when(!$includeInactive, function($sub) {
                    $sub->where('is_active', true);
                })->orderBy('order');

                if ($isV2 && $role) {
                    $q->where(function($sub) use ($role) {
                        $sub->where('role_specific', $role)
                            ->orWhereNull('role_specific');
                    });
                }
            }])
            ->orderBy('order');

        if ($isV2) {
            $query->where('v2_enabled', true);
            
            if ($role) {
                $query->where(function($q) use ($role) {
                    $q->where('role_specific', $role)
                      ->orWhereNull('role_specific');
                });
            }
        }

        return $query->get();
    }

    /**
     * Filter menus by current user permissions.
     */
    protected function filterByPermissions(Collection $menus): Collection
    {
        $user = Auth::user();

        return $menus->filter(function ($menu) use ($user) {
            return $this->userCanAccess($menu, $user);
        })->map(function ($menu) use ($user) {
            // We clone to avoid modifying the cached object references
            $menu = clone $menu;
            
            if ($menu->children && $menu->children->isNotEmpty()) {
                $menu->setRelation('children', $this->filterByPermissions($menu->children));
            }
            
            return $menu;
        })->values();
    }

    /**
     * Check access for a single menu item.
     */
    protected function userCanAccess($menu, ?User $user): bool
    {
        if (empty($menu->permission)) {
            return true;
        }

        return $user ? $user->can($menu->permission) : false;
    }

    /**
     * Generate cache key.
     */
    protected function getCacheKey(?string $role, bool $isV2, bool $includeInactive = false): string
    {
        $roleKey = $role ?: 'global';
        $v2Key = $isV2 ? 'v2' : 'v1';
        $inactiveKey = $includeInactive ? ':all' : ':active';
        return "menus:structure:{$v2Key}:{$roleKey}{$inactiveKey}";
    }

    /*
    |--------------------------------------------------------------------------
    | Config-Based Menu Methods (New Approach)
    |--------------------------------------------------------------------------
    */

    /**
     * Get menu from config files (Simple approach)
     * This is an alternative to database-driven menus
     *
     * @param string|null $requestedRole
     * @return array
     */
    public function getConfigMenu(?string $requestedRole = null): array
    {
        $user = Auth::user();
        
        // If no user, treat as guest
        if (!$user) {
            $role = 'guest';
            \Illuminate\Support\Facades\Log::info('Menu Request: Guest User');
        } else {
            // Default to user's actual role
            $role = $this->getUserRole($user);
            
            \Illuminate\Support\Facades\Log::info('Menu Request:', [
                'user_id' => $user->id,
                'actual_role' => $role,
                'requested_role' => $requestedRole,
                'is_admin' => ($role === 'admin'),
                'has_super_admin' => $user->hasRole('super_admin') // Verify usage
            ]);

            // If a specific role is requested, checks if user is allowed to view it
            // (Admins and Super Admins can view any role's menu)
            if ($requestedRole && (
                $role === 'admin' || 
                $role === 'super_admin' || 
                $user->hasRole('super_admin') || 
                $user->hasRole('admin')
            )) {
                $role = $requestedRole;
                \Illuminate\Support\Facades\Log::info('Role Override Applied: ' . $role);
            }
        }
        
        // Load menu items for this role from config
        $items = config("menus.{$role}", []);
        
        // Filter by permission
        $filtered = $this->filterConfigByPermission($items, $user);
        
        // Translate labels based on current locale
        return $this->translateConfigLabels($filtered);
    }

    private function getUserRole($user): string
    {
        // If you have a role column
        if (isset($user->role)) {
            return $user->role;
        }
        
        // If using Spatie permissions, get first role
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            return $roles->first() ?? 'guest';
        }
        
        // Default fallback
        return 'guest';
    }
    
    /**
     * Filter config menu items by permission (Recursive)
     */
    private function filterConfigByPermission(array $items, $user): array
    {
        return collect($items)->filter(function ($item) use ($user) {
            // 1. Check permission for the item itself
            if (isset($item['permission'])) {
                // If user is guest/null, deny permission-based items
                if (!$user) {
                    return false;
                }
                
                // Check using Gate
                try {
                    if (!$user->can($item['permission'])) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            // 2. Validate route existence (if defined)
            if (isset($item['route']) && !\Illuminate\Support\Facades\Route::has($item['route'])) {
                // Log missing route warning
                \Log::warning("Menu route missing: " . $item['route'], ['item_id' => $item['id'] ?? 'unknown']);
                return false;
            }

            return true;
        })
        ->map(function ($item) use ($user) {
            // 3. Recursively filter children
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->filterConfigByPermission($item['children'], $user);
                
                // Optional: If children become empty and it was a dropdown (no route), maybe hide it?
                // For now, we keep parent if it has no children but is allowed itself.
            }
            return $item;
        })
        ->values() // Re-index array
        ->all();
    }
    
    /**
     * Translate config menu labels based on current locale
     */
    private function translateConfigLabels(array $items): array
    {
        $locale = app()->getLocale();
        
        return collect($items)->map(function ($item) use ($locale) {
            // Translate current item
            if (isset($item['label'][$locale])) {
                $item['label'] = $item['label'][$locale];
            } elseif (isset($item['label']['en'])) {
                $item['label'] = $item['label']['en']; // Fallback to English
            }
            
            // Recursively translate children if they exist
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->translateConfigLabels($item['children']);
            }
            
            return $item;
        })->all();
    }
}
