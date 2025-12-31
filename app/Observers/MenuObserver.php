<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Handle the Menu "created" event.
     */
    public function created(Menu $menu): void
    {
        $this->clearMenuCache();
    }

    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        $this->clearMenuCache();
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        $this->clearMenuCache();
    }

    /**
     * Handle the Menu "restored" event.
     */
    public function restored(Menu $menu): void
    {
        $this->clearMenuCache();
    }

    /**
     * Clear all menu-related caches
     */
    protected function clearMenuCache(): void
    {
        // Clear all menu caches using pattern matching
        // This will clear caches for all users
        Cache::flush(); // Simple approach - clears all cache
        
        // For more targeted cache clearing, you could use:
        // Cache::tags(['menus'])->flush();
        // But this requires a cache driver that supports tags (Redis, Memcached)
    }
}
