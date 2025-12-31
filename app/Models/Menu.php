<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'label',
        'route',
        'permission',
        'module',
        'parent_id',
        'order',
        'icon',
        'is_active',
        'is_feature_flag',
        'feature_flag_key',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_feature_flag' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * Parent menu relationship
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Children menus relationship
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Scope: Active menus only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Root level menus (no parent)
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if this menu can have the specified parent
     * Prevents circular references and enforces 2-level depth limit
     */
    public function canHaveParent($parentId)
    {
        if (!$parentId) {
            return true; // Can be root level
        }

        // Can't be its own parent
        if ($this->id && $this->id == $parentId) {
            return false;
        }

        $parent = static::find($parentId);
        if (!$parent) {
            return false;
        }

        // Parent must be root level (2-level depth limit)
        if ($parent->parent_id !== null) {
            return false;
        }

        // Check if parent is a descendant of this menu (circular reference)
        if ($this->id && $this->isDescendant($parentId)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the given menu ID is a descendant of this menu
     */
    protected function isDescendant($menuId)
    {
        $menu = static::find($menuId);
        if (!$menu) {
            return false;
        }

        if ($menu->parent_id === $this->id) {
            return true;
        }

        if ($menu->parent_id) {
            return $this->isDescendant($menu->parent_id);
        }

        return false;
    }

    /**
     * Get available parent options for this menu
     */
    public static function getAvailableParents($excludeId = null)
    {
        return static::rootLevel()
            ->active()
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->orderBy('module')
            ->orderBy('order')
            ->get(['id', 'label', 'module']);
    }
}
