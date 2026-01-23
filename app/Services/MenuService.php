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
     * @return Collection
     */
    public function getMenus(?string $role = null, bool $isV2 = false): Collection
    {
        $structure = $this->getMenuStructure($role, $isV2);
        
        // Filter by user permissions
        return $this->filterByPermissions($structure);
    }

    /**
     * Get the raw menu structure for a specific role (Cached).
     *
     * @param string|null $role
     * @param bool $isV2
     * @return Collection
     */
    public function getMenuStructure(?string $role = null, bool $isV2 = false): Collection
    {
        $cacheKey = $this->getCacheKey($role, $isV2);

        // Use tags if supported (e.g. Redis), otherwise standard cache
        $cache = Cache::supportsTags() ? Cache::tags([self::CACHE_TAG]) : Cache::store();

        return $cache->remember($cacheKey, self::CACHE_TTL, function () use ($role, $isV2) {
            return $this->buildMenuQuery($role, $isV2);
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
                 Cache::forget($this->getCacheKey($role, true)); // V2
                 Cache::forget($this->getCacheKey($role, false)); // V1
            }
            // Clear global
            Cache::forget($this->getCacheKey(null, true));
            Cache::forget($this->getCacheKey(null, false));
        }
    }

    /**
     * Build the query and structure.
     */
    protected function buildMenuQuery(?string $role, bool $isV2): Collection
    {
        $query = Menu::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('order');
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
    protected function getCacheKey(?string $role, bool $isV2): string
    {
        $roleKey = $role ?: 'global';
        $v2Key = $isV2 ? 'v2' : 'v1';
        return "menus:structure:{$v2Key}:{$roleKey}";
    }
}
