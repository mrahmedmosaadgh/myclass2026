# Database-Driven Role-Based Menu & Routing Architecture

## 1. Database Schema

### 1.1 Menus Table
```sql
CREATE TABLE menus (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    route VARCHAR(255) NULL,  -- Named route (e.g., 'academics.subjects.index')
    url VARCHAR(255) NULL,     -- External URL (optional)
    permission VARCHAR(255) NULL,  -- Spatie permission name
    module VARCHAR(100) NOT NULL,  -- Feature/Domain grouping
    parent_id BIGINT UNSIGNED NULL,
    order INT NOT NULL DEFAULT 0,
    icon VARCHAR(100) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_feature_flag BOOLEAN NOT NULL DEFAULT FALSE,
    feature_flag_key VARCHAR(255) NULL,
    meta JSON NULL,  -- Additional metadata (badges, descriptions, etc.)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE,
    INDEX idx_module (module),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active),
    INDEX idx_order (order)
);
```

### 1.2 Multi-Tenant Schema Extension
```sql
-- If using multi-tenancy (e.g., schools in LMS)
CREATE TABLE tenant_menus (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    menu_id BIGINT UNSIGNED NOT NULL,
    is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    custom_label VARCHAR(255) NULL,  -- Allow tenant to override label
    custom_order INT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_menu (tenant_id, menu_id)
);
```

### 1.3 Menu Roles/Permissions (via Spatie)
```sql
-- Use existing Spatie Laravel Permission tables
-- permissions table
-- roles table
-- model_has_permissions
-- model_has_roles
-- role_has_permissions
```

---

## 2. Menu JSON Structure

### 2.1 API Response Format
```json
{
  "data": [
    {
      "id": 1,
      "label": "Academics",
      "route": null,
      "icon": "academic-cap",
      "module": "academics",
      "order": 1,
      "meta": {
        "description": "Academic management",
        "badge": null
      },
      "children": [
        {
          "id": 2,
          "label": "Subjects",
          "route": "academics.subjects.index",
          "icon": "book-open",
          "module": "academics",
          "order": 1,
          "permission": "view-subjects",
          "children": []
        },
        {
          "id": 3,
          "label": "Classes",
          "route": "academics.classes.index",
          "icon": "users",
          "module": "academics",
          "order": 2,
          "permission": "view-classes",
          "children": []
        }
      ]
    },
    {
      "id": 10,
      "label": "Administration",
      "route": null,
      "icon": "cog",
      "module": "admin",
      "order": 2,
      "children": [
        {
          "id": 11,
          "label": "Users",
          "route": "admin.users.index",
          "icon": "user-group",
          "module": "admin",
          "order": 1,
          "permission": "manage-users",
          "children": []
        }
      ]
    }
  ],
  "version": "a8f3c9d1e2b4567890abcdef12345678",
  "cached_at": "2025-12-31T17:00:00Z"
}
```

---

## 3. Route Naming Strategy

### 3.1 Convention
```
{module}.{resource}.{action}
```

**Examples:**
- `academics.subjects.index` - List subjects
- `academics.subjects.create` - Show create form
- `academics.subjects.store` - Store new subject
- `academics.subjects.show` - Show single subject
- `academics.subjects.edit` - Edit form
- `academics.subjects.update` - Update subject
- `academics.subjects.destroy` - Delete subject

### 3.2 Route Organization
```
routes/
├── web.php                  # Main entry, delegates to modules
├── api.php                  # API entry, delegates to modules
├── modules/
│   ├── Academics/
│   │   ├── web.php          # Inertia routes
│   │   └── api.php          # JSON API routes
│   ├── Attendance/
│   │   ├── web.php
│   │   └── api.php
│   └── Administration/
│       ├── web.php
│       └── api.php
```

### 3.3 Route-to-Controller Mapping
```php
// routes/modules/Academics/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/academics/subjects', [SubjectController::class, 'index'])
        ->name('academics.subjects.index')
        ->can('view-subjects');
    
    Route::get('/academics/subjects/create', [SubjectController::class, 'create'])
        ->name('academics.subjects.create')
        ->can('create-subjects');
});

// routes/modules/Academics/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('academics/subjects', SubjectApiController::class)
        ->names('api.academics.subjects')
        ->middleware('permission:view-subjects');
});
```

---

## 4. Pinia Store

### 4.1 Navigation Store (`stores/useNavigationStore.js`)
```javascript
import { defineStore } from 'pinia';
import axios from 'axios';

export const useNavigationStore = defineStore('navigation', {
    state: () => ({
        menuItems: [],
        isLoading: false,
        menuVersion: null,
        lastFetchedAt: null,
        error: null,
    }),

    getters: {
        /**
         * Get only visible/active menu items
         */
        visibleItems: (state) => state.menuItems,

        /**
         * Check if menu has items
         */
        hasItems: (state) => state.menuItems.length > 0,

        /**
         * Get menu items by module
         */
        itemsByModule: (state) => (module) => {
            return state.menuItems.filter(item => item.module === module);
        },

        /**
         * Flatten menu structure for breadcrumbs
         */
        flatMenuItems: (state) => {
            const flatten = (items, parent = null) => {
                let result = [];
                items.forEach(item => {
                    result.push({ ...item, parent });
                    if (item.children && item.children.length > 0) {
                        result = result.concat(flatten(item.children, item));
                    }
                });
                return result;
            };
            return flatten(state.menuItems);
        },

        /**
         * Find menu item by route name
         */
        findByRoute: (state) => (routeName) => {
            const flatten = (items) => {
                let result = [];
                items.forEach(item => {
                    result.push(item);
                    if (item.children) {
                        result = result.concat(flatten(item.children));
                    }
                });
                return result;
            };
            return flatten(state.menuItems).find(item => item.route === routeName);
        },
    },

    actions: {
        /**
         * Fetch menu from API
         */
        async fetchMenu(forceRefresh = false) {
            // Prevent duplicate requests
            if (this.isLoading) return;

            // Check if we need to refresh (5 min cache)
            const cacheExpiry = 5 * 60 * 1000;
            if (!forceRefresh && this.lastFetchedAt && Date.now() - this.lastFetchedAt < cacheExpiry) {
                return;
            }

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get('/api/navigation/menu', {
                    headers: {
                        'If-None-Match': this.menuVersion || '',
                    },
                });

                if (response.status === 200) {
                    this.menuItems = response.data.data;
                    this.menuVersion = response.data.version;
                    this.lastFetchedAt = Date.now();
                }
                // 304 Not Modified - no action needed
            } catch (error) {
                console.error('Failed to load navigation menu:', error);
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Refresh if version changed
         */
        async refreshIfVersionChanged(serverVersion) {
            if (serverVersion && serverVersion !== this.menuVersion) {
                await this.fetchMenu(true);
            }
        },

        /**
         * Clear menu cache
         */
        clearCache() {
            this.menuItems = [];
            this.menuVersion = null;
            this.lastFetchedAt = null;
        },
    },

    persist: {
        enabled: true,
        strategies: [
            {
                key: 'navigation',
                storage: localStorage,
                paths: ['menuItems', 'menuVersion', 'lastFetchedAt'],
            },
        ],
    },
});
```

---

## 5. Caching & Versioning Strategy

### 5.1 Server-Side Caching (Laravel)

#### Menu Controller with Caching
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NavigationController extends Controller
{
    /**
     * Get menu items for authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? null;
        
        // Cache key includes user permissions hash and tenant
        $cacheKey = $this->getCacheKey($user, $tenantId);
        
        // Cache for 1 hour
        $menus = Cache::remember($cacheKey, 3600, function () use ($user, $tenantId) {
            return $this->buildMenuTree($user, $tenantId);
        });

        $version = md5(json_encode($menus));
        
        // Check If-None-Match header for ETag support
        $clientVersion = request()->header('If-None-Match');
        if ($clientVersion === $version) {
            return response()->json(null, 304);
        }

        return response()->json([
            'data' => $menus,
            'version' => $version,
            'cached_at' => now()->toIso8601String(),
        ])->header('ETag', $version);
    }

    /**
     * Build menu tree with permissions
     */
    private function buildMenuTree($user, $tenantId)
    {
        $query = Menu::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('order');
            }])
            ->orderBy('order');

        // Multi-tenant filtering
        if ($tenantId) {
            $query->whereHas('tenantMenus', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->where('is_enabled', true);
            });
        }

        $menus = $query->get();

        // Filter by permissions
        return $menus->filter(function ($menu) use ($user) {
            return $this->userCanAccess($menu, $user);
        })->map(function ($menu) use ($user) {
            $menu->children = $menu->children->filter(function ($child) use ($user) {
                return $this->userCanAccess($child, $user);
            })->values();
            return $menu;
        })->values();
    }

    /**
     * Check if user can access menu
     */
    private function userCanAccess($menu, $user)
    {
        // Feature flag check
        if ($menu->is_feature_flag && $menu->feature_flag_key) {
            if (!$this->isFeatureEnabled($menu->feature_flag_key, $user)) {
                return false;
            }
        }

        // Permission check
        if (!empty($menu->permission)) {
            return $user->can($menu->permission);
        }

        return true;
    }

    /**
     * Feature flag check
     */
    private function isFeatureEnabled($key, $user)
    {
        // Integrate with your feature flag system
        // e.g., Laravel Pennant, custom solution
        return config("features.{$key}", false);
    }

    /**
     * Generate cache key
     */
    private function getCacheKey($user, $tenantId)
    {
        $permissionsHash = md5(json_encode($user->getAllPermissions()->pluck('name')));
        return sprintf(
            'menu.user.%s.tenant.%s.perms.%s',
            $user->id,
            $tenantId ?? 'global',
            $permissionsHash
        );
    }

    /**
     * Clear menu cache for user
     */
    public function clearCache()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? null;
        $cacheKey = $this->getCacheKey($user, $tenantId);
        
        Cache::forget($cacheKey);
        
        return response()->json(['message' => 'Cache cleared']);
    }
}
```

### 5.2 Cache Invalidation Strategy

#### Event-Based Invalidation
```php
<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Handle menu changes
     */
    public function saved(Menu $menu)
    {
        $this->clearMenuCache();
    }

    public function deleted(Menu $menu)
    {
        $this->clearMenuCache();
    }

    /**
     * Clear all menu caches
     */
    private function clearMenuCache()
    {
        // Clear all menu caches (use tag-based caching for efficiency)
        Cache::tags(['menus'])->flush();
    }
}
```

#### Register Observer
```php
// app/Providers/EventServiceProvider.php
use App\Models\Menu;
use App\Observers\MenuObserver;

public function boot()
{
    Menu::observe(MenuObserver::class);
}
```

---

## 6. Laravel Implementation Examples

### 6.1 Menu Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'label',
        'route',
        'url',
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
     * Tenant-specific menu settings
     */
    public function tenantMenus()
    {
        return $this->hasMany(TenantMenu::class);
    }

    /**
     * Scope: Active menus only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Root level menus
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
```

### 6.2 Menu Seeder
```php
<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Academics Module
        $academics = Menu::create([
            'label' => 'Academics',
            'route' => null,
            'module' => 'academics',
            'icon' => 'academic-cap',
            'order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'label' => 'Subjects',
            'route' => 'academics.subjects.index',
            'module' => 'academics',
            'parent_id' => $academics->id,
            'permission' => 'view-subjects',
            'icon' => 'book-open',
            'order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'label' => 'Classes',
            'route' => 'academics.classes.index',
            'module' => 'academics',
            'parent_id' => $academics->id,
            'permission' => 'view-classes',
            'icon' => 'users',
            'order' => 2,
            'is_active' => true,
        ]);

        // Administration Module
        $admin = Menu::create([
            'label' => 'Administration',
            'route' => null,
            'module' => 'admin',
            'icon' => 'cog',
            'order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'label' => 'Users',
            'route' => 'admin.users.index',
            'module' => 'admin',
            'parent_id' => $admin->id,
            'permission' => 'manage-users',
            'icon' => 'user-group',
            'order' => 1,
            'is_active' => true,
        ]);
    }
}
```

---

## 7. Vue 3 Components

### 7.1 Main Navigation Component
```vue
<template>
  <nav class="main-navigation">
    <template v-if="navStore.isLoading">
      <div class="loading-skeleton">Loading navigation...</div>
    </template>

    <template v-else-if="navStore.hasItems">
      <NavigationGroup
        v-for="item in navStore.visibleItems"
        :key="item.id"
        :item="item"
      />
    </template>

    <template v-else>
      <div class="no-navigation">No menu items available</div>
    </template>
  </nav>
</template>

<script setup>
import { onMounted } from 'vue';
import { useNavigationStore } from '@/Stores/useNavigationStore';
import NavigationGroup from './NavigationGroup.vue';

const navStore = useNavigationStore();

onMounted(() => {
  navStore.fetchMenu();
});
</script>
```

### 7.2 Navigation Group Component
```vue
<template>
  <div class="nav-group">
    <!-- Parent Item -->
    <div v-if="item.route" class="nav-item">
      <Link
        :href="route(item.route)"
        class="nav-link"
        :class="{ 'active': isActive(item.route) }"
      >
        <component :is="iconComponent" v-if="item.icon" class="nav-icon" />
        <span>{{ item.label }}</span>
        <span v-if="item.meta?.badge" class="badge">{{ item.meta.badge }}</span>
      </Link>
    </div>

    <div v-else class="nav-header">
      <component :is="iconComponent" v-if="item.icon" class="nav-icon" />
      <span>{{ item.label }}</span>
    </div>

    <!-- Children -->
    <div v-if="item.children && item.children.length > 0" class="nav-children">
      <NavigationItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import NavigationItem from './NavigationItem.vue';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const page = usePage();

const iconComponent = computed(() => {
  // Dynamically import icon based on item.icon
  // Using Heroicons as example
  return defineAsyncComponent(() => 
    import(`@heroicons/vue/24/outline/${props.item.icon}.js`)
  );
});

const isActive = (routeName) => {
  return page.component.value.startsWith(routeName);
};
</script>
```

---

## 8. Anti-Patterns to Avoid

### ❌ 8.1 Hardcoded Menus in Frontend
```vue
<!-- WRONG: Hardcoded menu structure -->
<template>
  <nav>
    <a href="/academics/subjects">Subjects</a>
    <a v-if="$can('manage-users')" href="/admin/users">Users</a>
  </nav>
</template>
```

**✅ Correct:** Always fetch from Pinia store populated by backend.

---

### ❌ 8.2 Permission Checks in Frontend
```vue
<!-- WRONG: Frontend permission logic -->
<template>
  <a v-if="user.role === 'admin'" href="/admin">Admin</a>
</template>
```

**✅ Correct:** Backend filters menu items. Frontend just renders what it receives.

---

### ❌ 8.3 Hardcoded URLs
```vue
<!-- WRONG: Hardcoded path -->
<Link href="/academics/subjects/123">View Subject</Link>
```

**✅ Correct:** Use named routes
```vue
<Link :href="route('academics.subjects.show', { subject: 123 })">View Subject</Link>
```

---

### ❌ 8.4 No Caching Strategy
```javascript
// WRONG: Fetch on every component mount
onMounted(() => {
  axios.get('/api/navigation/menu').then(...)
});
```

**✅ Correct:** Use Pinia store with caching and version checks.

---

### ❌ 8.5 Mixing Concerns
```php
// WRONG: Business logic in routes file
Route::get('/subjects', function () {
    if (Auth::user()->role === 'teacher') {
        return Inertia::render('Teacher/Subjects');
    }
    return Inertia::render('Admin/Subjects');
});
```

**✅ Correct:** Use controllers and policies
```php
Route::get('/subjects', [SubjectController::class, 'index'])
    ->middleware('can:view-subjects');
```

---

### ❌ 8.6 Not Using Transactions
```php
// WRONG: No transaction when updating menus
$menu->update(['label' => 'New Label']);
$menu->children()->delete();
```

**✅ Correct:** Use database transactions
```php
DB::transaction(function () use ($menu) {
    $menu->update(['label' => 'New Label']);
    $menu->children()->delete();
    Cache::tags(['menus'])->flush();
});
```

---

## 9. Multi-Tenancy Implementation

### 9.1 Tenant Context Middleware
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetTenantContext
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user && $user->tenant_id) {
            // Set tenant context for the request
            app()->instance('current_tenant_id', $user->tenant_id);
        }
        
        return $next($request);
    }
}
```

### 9.2 Tenant-Aware Menu Query
```php
private function buildMenuTree($user, $tenantId)
{
    $query = Menu::active()->rootLevel();

    if ($tenantId) {
        // Join with tenant_menus to get tenant-specific overrides
        $query->leftJoin('tenant_menus', function ($join) use ($tenantId) {
            $join->on('menus.id', '=', 'tenant_menus.menu_id')
                 ->where('tenant_menus.tenant_id', '=', $tenantId);
        })
        ->select('menus.*', 'tenant_menus.custom_label', 'tenant_menus.custom_order')
        ->where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_menus.id')
              ->orWhere('tenant_menus.is_enabled', true);
        });
    }

    return $query->get();
}
```

---

## 10. Feature Flags Integration

### 10.1 Using Laravel Pennant
```php
use Laravel\Pennant\Feature;

// Define feature
Feature::define('new-grading-system', function ($user) {
    return $user->tenant->hasFeature('new-grading-system');
});

// In NavigationController
private function isFeatureEnabled($key, $user)
{
    return Feature::for($user)->active($key);
}
```

### 10.2 Menu Seeder with Feature Flags
```php
Menu::create([
    'label' => 'New Grading System',
    'route' => 'academics.grading.index',
    'module' => 'academics',
    'permission' => 'view-grading',
    'is_feature_flag' => true,
    'feature_flag_key' => 'new-grading-system',
    'order' => 5,
]);
```

---

## 11. Performance Optimization

### 11.1 Eager Loading
```php
// Load all relationships in one query
Menu::with(['children.children', 'tenantMenus'])
    ->active()
    ->rootLevel()
    ->get();
```

### 11.2 Query Optimization
```php
// Add indexes
Schema::table('menus', function (Blueprint $table) {
    $table->index(['module', 'is_active']);
    $table->index(['parent_id', 'order']);
});
```

### 11.3 Response Compression
```php
// In NavigationController
return response()
    ->json($data)
    ->header('Content-Encoding', 'gzip');
```

---

## 12. Testing Strategy

### 12.1 Feature Test Example
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;

class NavigationApiTest extends TestCase
{
    public function test_user_sees_only_permitted_menus()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view-subjects');

        Menu::create([
            'label' => 'Subjects',
            'route' => 'academics.subjects.index',
            'permission' => 'view-subjects',
        ]);

        Menu::create([
            'label' => 'Admin Panel',
            'route' => 'admin.index',
            'permission' => 'access-admin',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/navigation/menu');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['label' => 'Subjects'])
            ->assertJsonMissing(['label' => 'Admin Panel']);
    }
}
```

---

## 13. Migration Path

### 13.1 From Hardcoded to Database-Driven

**Step 1:** Create migrations and models
```bash
php artisan make:migration create_menus_table
php artisan make:model Menu
```

**Step 2:** Seed from existing hardcoded structure
```php
// Convert existing menu array to database records
foreach ($oldMenuStructure as $item) {
    Menu::create([
        'label' => $item['label'],
        'route' => $item['route'],
        'permission' => $item['permission'] ?? null,
    ]);
}
```

**Step 3:** Update frontend incrementally
- Replace one section at a time
- Use feature flags to toggle between old/new systems

**Step 4:** Remove legacy code after full migration

---

## 14. Monitoring & Debugging

### 14.1 Cache Hit Rate Monitoring
```php
// In NavigationController
use Illuminate\Support\Facades\Log;

$cacheHit = Cache::has($cacheKey);
Log::info('Menu cache', ['hit' => $cacheHit, 'user_id' => $user->id]);
```

### 14.2 Performance Logging
```php
$start = microtime(true);
$menus = $this->buildMenuTree($user, $tenantId);
$duration = microtime(true) - $start;

if ($duration > 0.5) {
    Log::warning('Slow menu query', ['duration' => $duration]);
}
```

---

## Summary

This architecture provides:
✅ **Scalability:** Database-driven, cached at multiple levels  
✅ **Security:** Server-side authorization, no frontend logic  
✅ **Flexibility:** Multi-tenant, feature flags, dynamic configuration  
✅ **Maintainability:** Clear separation of concerns, feature-based routing  
✅ **Performance:** Strategic caching, ETag support, eager loading  

**Key Principles:**
1. Laravel is the single source of truth
2. Frontend is purely presentational
3. Cache aggressively, invalidate intelligently
4. Use named routes everywhere
5. Test authorization at the API level
