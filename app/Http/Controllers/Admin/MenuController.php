<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class MenuController extends Controller
{
    /**
     * Display menu management page
     */
    public function index()
    {
        $menus = Menu::with('children')
            ->whereNull('parent_id')
            ->orderBy('module')
            ->orderBy('order')
            ->get();

        $modules = config('menus.modules');

        return Inertia::render('Admin/MenuManagement', [
            'menus' => $menus,
            'modules' => $modules,
        ]);
    }

    /**
     * Store a new menu item
     */
    public function store(Request $request)
    {
        $validator = $this->validateMenu($request);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Check parent validity
        if (isset($data['parent_id'])) {
            $menu = new Menu();
            if (!$menu->canHaveParent($data['parent_id'])) {
                return response()->json([
                    'errors' => ['parent_id' => ['Invalid parent selection. Parent must be root level.']]
                ], 422);
            }
        }

        $menu = Menu::create($data);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => $menu->load('children')
        ], 201);
    }

    /**
     * Update an existing menu item
     */
    public function update(Request $request, Menu $menu)
    {
        $validator = $this->validateMenu($request, $menu->id);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Check parent validity
        if (isset($data['parent_id'])) {
            if (!$menu->canHaveParent($data['parent_id'])) {
                return response()->json([
                    'errors' => ['parent_id' => ['Invalid parent selection. Would create circular reference or exceed depth limit.']]
                ], 422);
            }
        }

        $menu->update($data);

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => $menu->load('children')
        ]);
    }

    /**
     * Delete a menu item
     */
    public function destroy(Menu $menu)
    {
        $childrenCount = $menu->children()->count();
        
        $menu->delete(); // Cascade will handle children

        return response()->json([
            'message' => 'Menu deleted successfully',
            'children_deleted' => $childrenCount
        ]);
    }

    /**
     * Reorder menus via drag-and-drop
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Menu order updated successfully'
        ]);
    }

    /**
     * Bulk import menus from JSON
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
            'dry_run' => 'boolean',
        ]);

        $dryRun = $request->boolean('dry_run', false);
        $results = [
            'success' => [],
            'errors' => [],
            'total' => count($request->menus),
        ];

        foreach ($request->menus as $index => $menuData) {
            try {
                $validator = Validator::make($menuData, $this->getValidationRules());

                if ($validator->fails()) {
                    $results['errors'][] = [
                        'index' => $index,
                        'data' => $menuData,
                        'errors' => $validator->errors()->toArray()
                    ];
                    continue;
                }

                if (!$dryRun) {
                    $menu = Menu::create($validator->validated());

                    // Handle children if present
                    if (isset($menuData['children']) && is_array($menuData['children'])) {
                        foreach ($menuData['children'] as $childData) {
                            $childData['parent_id'] = $menu->id;
                            $childValidator = Validator::make($childData, $this->getValidationRules());
                            
                            if ($childValidator->fails()) {
                                $results['errors'][] = [
                                    'index' => $index,
                                    'parent' => $menuData['label'],
                                    'child' => $childData,
                                    'errors' => $childValidator->errors()->toArray()
                                ];
                                continue;
                            }

                            Menu::create($childValidator->validated());
                        }
                    }

                    $results['success'][] = [
                        'index' => $index,
                        'label' => $menu->label,
                        'id' => $menu->id
                    ];
                } else {
                    $results['success'][] = [
                        'index' => $index,
                        'label' => $menuData['label'] ?? 'Unknown',
                        'dry_run' => true
                    ];
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $menuData,
                    'errors' => ['exception' => $e->getMessage()]
                ];
            }
        }

        return response()->json([
            'message' => $dryRun ? 'Dry run completed' : 'Bulk import completed',
            'results' => $results,
            'success_count' => count($results['success']),
            'error_count' => count($results['errors']),
        ]);
    }

    /**
     * Get available Laravel routes
     */
    public function getAvailableRoutes()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            $name = $route->getName();
            if ($name) {
                return [
                    'name' => $name,
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                ];
            }
            return null;
        })->filter()->values();

        return response()->json($routes);
    }

    /**
     * Get available Spatie permissions
     */
    public function getAvailablePermissions()
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return response()->json($permissions);
    }

    /**
     * Get available modules
     */
    public function getAvailableModules()
    {
        $modules = config('menus.modules');

        return response()->json($modules);
    }

    /**
     * Get available parent menus
     */
    public function getAvailableParents(Request $request)
    {
        $excludeId = $request->query('exclude_id');
        $parents = Menu::getAvailableParents($excludeId);

        return response()->json($parents);
    }

    /**
     * Generate AI prompt for bulk menu creation
     */
    public function generateAIPrompt()
    {
        $modules = config('menus.modules');
        
        // Get all permissions
        $allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
        
        // Get all routes organized
        $allRoutes = collect(Route::getRoutes())
            ->map(fn($route) => $route->getName())
            ->filter()
            ->sort()
            ->values()
            ->toArray();
        
        // Get already used routes and permissions
        $usedRoutes = Menu::whereNotNull('route')->pluck('route')->unique()->toArray();
        $usedPermissions = Menu::whereNotNull('permission')->pluck('permission')->unique()->toArray();
        
        // Organize routes by module/prefix
        $routesByModule = collect($allRoutes)->groupBy(function ($route) {
            $parts = explode('.', $route);
            return $parts[0] ?? 'other';
        })->map(function ($routes) use ($usedRoutes) {
            return $routes->map(function ($route) use ($usedRoutes) {
                return [
                    'name' => $route,
                    'used' => in_array($route, $usedRoutes),
                ];
            })->toArray();
        })->toArray();
        
        // Organize permissions with usage status
        $permissionsWithStatus = collect($allPermissions)->map(function ($permission) use ($usedPermissions) {
            return [
                'name' => $permission,
                'used' => in_array($permission, $usedPermissions),
            ];
        })->toArray();
        
        // Build comprehensive prompt
        $prompt = "Generate a JSON array of menu items for my Laravel LMS application.\n\n";
        $prompt .= "=== JSON STRUCTURE ===\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"label\": \"Menu Label\",\n";
        $prompt .= "    \"route\": \"named.route.here\",  // Must be from available routes below\n";
        $prompt .= "    \"permission\": \"permission-name\",  // Must be from available permissions below\n";
        $prompt .= "    \"module\": \"module-name\",  // Must be from available modules below\n";
        $prompt .= "    \"parent_id\": null,  // Leave null, system will handle parent relationships\n";
        $prompt .= "    \"order\": 0,\n";
        $prompt .= "    \"icon\": \"icon-name\",  // Use Material Icons names\n";
        $prompt .= "    \"is_active\": true,\n";
        $prompt .= "    \"children\": [  // Optional nested menus (max 1 level)\n";
        $prompt .= "      {\n";
        $prompt .= "        \"label\": \"Child Menu\",\n";
        $prompt .= "        \"route\": \"child.route\",\n";
        $prompt .= "        \"permission\": \"permission-name\",\n";
        $prompt .= "        \"module\": \"module-name\",\n";
        $prompt .= "        \"order\": 1,\n";
        $prompt .= "        \"icon\": \"icon-name\",\n";
        $prompt .= "        \"is_active\": true\n";
        $prompt .= "      }\n";
        $prompt .= "    ]\n";
        $prompt .= "  }\n";
        $prompt .= "]\n\n";
        
        $prompt .= "=== AVAILABLE MODULES ===\n";
        $prompt .= implode(', ', $modules) . "\n\n";
        
        $prompt .= "=== AVAILABLE PERMISSIONS (" . count($allPermissions) . " total) ===\n";
        $prompt .= "Format: permission-name [USED] or permission-name [AVAILABLE]\n";
        foreach ($permissionsWithStatus as $perm) {
            $status = $perm['used'] ? '[USED]' : '[AVAILABLE]';
            $prompt .= "- {$perm['name']} {$status}\n";
        }
        $prompt .= "\n";
        
        $prompt .= "=== AVAILABLE ROUTES (" . count($allRoutes) . " total, organized by module) ===\n";
        $prompt .= "Format: route.name [USED] or route.name [AVAILABLE]\n\n";
        foreach ($routesByModule as $module => $routes) {
            $prompt .= "**{$module}** (" . count($routes) . " routes):\n";
            foreach ($routes as $route) {
                $status = $route['used'] ? '[USED]' : '[AVAILABLE]';
                $prompt .= "  - {$route['name']} {$status}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "=== YOUR TASK ===\n";
        $prompt .= "Create menu items for: [DESCRIBE WHAT YOU WANT HERE]\n\n";
        $prompt .= "IMPORTANT NOTES:\n";
        $prompt .= "- Only use [AVAILABLE] routes and permissions (or suggest creating new ones if needed)\n";
        $prompt .= "- Match routes to appropriate modules (e.g., 'academics.subjects.index' → module: 'academics')\n";
        $prompt .= "- Use descriptive, user-friendly labels\n";
        $prompt .= "- Choose appropriate Material Icons (e.g., 'school', 'calendar_today', 'people', 'settings')\n";
        $prompt .= "- Organize logically with parent-child relationships where it makes sense\n";
        $prompt .= "- Set proper order values (0, 1, 2, etc.) for display sequence\n";

        return response()->json([
            'prompt' => $prompt,
            'modules' => $modules,
            'permissions' => $permissionsWithStatus,
            'routes' => $routesByModule,
            'stats' => [
                'total_permissions' => count($allPermissions),
                'used_permissions' => count($usedPermissions),
                'total_routes' => count($allRoutes),
                'used_routes' => count($usedRoutes),
            ],
        ]);
    }

    /**
     * Get validation rules for menu
     */
    protected function getValidationRules($menuId = null)
    {
        return [
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'permission' => 'nullable|string|exists:permissions,name',
            'module' => ['required', 'string', Rule::in(config('menus.modules'))],
            'parent_id' => [
                'nullable',
                'exists:menus,id',
                $menuId ? Rule::notIn([$menuId]) : '',
            ],
            'order' => 'integer|min:0',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_feature_flag' => 'boolean',
            'feature_flag_key' => 'nullable|required_if:is_feature_flag,true|string|max:255',
            'meta' => 'nullable|array',
        ];
    }

    /**
     * Validate menu data
     */
    protected function validateMenu(Request $request, $menuId = null)
    {
        return Validator::make($request->all(), $this->getValidationRules($menuId));
    }
}
