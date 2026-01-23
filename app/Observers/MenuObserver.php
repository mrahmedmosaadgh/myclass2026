<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\MenuService;

class MenuObserver
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

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
        try {
            $this->menuService->clearCache();  
        } catch (\Exception $e) {
            // Log error
        } 
    }
}
