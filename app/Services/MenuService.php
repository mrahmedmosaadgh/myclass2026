<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class MenuService
{
    /**
     * Get menu from config files (Config-based approach)
     * Matches the user's role and requested role, filtering by permissions and active routes.
     *
     * @param string|null $requestedRole
     * @return array
     */
    public function getConfigMenu(?string $requestedRole = null): array
    {
        $user = Auth::user();
        
        if (!$user) {
            $role = 'guest';
        } else {
            $role = $this->getUserRole($user);
            
            // Allow Admins and Super Admins to preview other roles' menus via query parameter
            if ($requestedRole && (
                $role === 'admin' || 
                $role === 'super_admin' || 
                $user->hasRole('super_admin') || 
                $user->hasRole('admin')
            )) {
                $role = $requestedRole;
            }
        }
        
        // Load menu items for this role from config directory
        $items = config("menus.{$role}", []);
        
        // Filter by permission and active routes
        $filtered = $this->filterConfigByPermission($items, $user);
        
        // Translate labels based on current locale
        return $this->translateConfigLabels($filtered);
    }

    /**
     * Determine user's primary role for menu selection
     */
    private function getUserRole($user): string
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('developer')) {
            return 'developer';
        }

        if (isset($user->role)) {
            return $user->role;
        }
        
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            return $roles->first() ?? 'guest';
        }
        
        return 'guest';
    }
    
    /**
     * Filter config menu items by permission and ensure routes exist (Recursive)
     */
    private function filterConfigByPermission(array $items, $user): array
    {
        return collect($items)->filter(function ($item) use ($user) {
            
            // 1. Check feature flag (if defined) matches config items
            if (isset($item['feature']) && !config("features.{$item['feature']}", true)) {
                return false;
            }

            // 2. Check permission for the item itself
            if (isset($item['permission'])) {
                if (!$user) {
                    return false;
                }
                
                try {
                    if (!$user->can($item['permission'])) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            // 3. Validate route existence (if defined and not a hash link)
            if (isset($item['route']) && $item['route'] !== '#' && !Route::has($item['route'])) {
                Log::warning("Menu route missing: " . $item['route'], ['item_id' => $item['id'] ?? 'unknown']);
                return false; // Automatically hides broken links!
            }

            return true;
        })
        ->map(function ($item) use ($user) {
            // Recursively filter children
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->filterConfigByPermission($item['children'], $user);
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
        $locale = app()->getLocale() ?: 'en';
        
        return collect($items)->map(function ($item) use ($locale) {
            // Translate current item
            if (isset($item['label'][$locale])) {
                $item['label'] = $item['label'][$locale];
            } elseif (isset($item['label']['en'])) {
                $item['label'] = $item['label']['en']; // Fallback to English
            } elseif (is_array($item['label'])) {
                $item['label'] = reset($item['label']); // Fallback to first available translation
            }
            
            // Recursively translate children if they exist
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->translateConfigLabels($item['children']);
            }
            
            return $item;
        })->all();
    }
}
