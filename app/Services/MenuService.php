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
     * @return array
     */
    public function getConfigMenu(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        // Get role from user
        $role = $this->getUserRole($user);
        
        // Load menu items for this role from config
        $items = config("menus.{$role}", []);
        
        // Filter by permission
        $filtered = $this->filterConfigByPermission($items, $user);
        
        // Translate labels based on current locale
        return $this->translateConfigLabels($filtered);
    }
    
    /**
     * Get user role for config-based menu
     */
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
     * Filter config menu items by permission
     */
    private function filterConfigByPermission(array $items, $user): array
    {
        return collect($items)->filter(function ($item) use ($user) {
            // If no permission specified, show it
            if (!isset($item['permission'])) {
                // If route specified, check if it exists
                if (isset($item['route']) && !\Illuminate\Support\Facades\Route::has($item['route'])) {
                    return false;
                }
                return true;
            }
            
            // Check permission using Laravel's Gate
            try {
                return $user->can($item['permission']);
            } catch (\Exception $e) {
                // If permission check fails, hide the item
                return false;
            }
        })->values()->all();
    }
    
    /**
     * Translate config menu labels based on current locale
     */
    private function translateConfigLabels(array $items): array
    {
        $locale = app()->getLocale();
        
        return collect($items)->map(function ($item) use ($locale) {
            if (isset($item['label'][$locale])) {
                $item['label'] = $item['label'][$locale];
            } elseif (isset($item['label']['en'])) {
                $item['label'] = $item['label']['en']; // Fallback to English
            }
            
            return $item;
        })->all();
    }
}
