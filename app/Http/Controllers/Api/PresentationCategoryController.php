<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrPresentationCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PresentationCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = CrPresentationCategory::with(['presentations' => function ($query) use ($user) {
            if ($user) {
                $query->forUser($user->id);
            }
        }]);

        // Filter by school
        if ($request->has('school_id')) {
            $query->forSchool($request->school_id);
        } elseif ($user && $user->school_id) {
            $query->forSchool($user->school_id);
        }

        // Include system categories
        if (!$request->has('include_system') || $request->include_system) {
            // Include system categories by default
        } else {
            $query->custom();
        }

        // Filter active only
        $query->active();

        // Sort
        $query->ordered();

        $categories = $query->get();

        // Build tree structure
        $tree = $this->buildCategoryTree($categories);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Only allow creation if user has appropriate permissions
        if (!$user->hasRole('school_admin') && !$user->hasRole('teacher')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create categories'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:cr_presentation_categories,id',
            'school_id' => 'nullable|exists:schools,id'
        ]);

        try {
            $category = CrPresentationCategory::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? '#6b7280',
                'icon' => $validated['icon'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'school_id' => $validated['school_id'] ?? $user->school_id,
                'created_by' => $user->id,
                'is_system' => false,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CrPresentationCategory $category): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->load(['presentations' => function ($query) use ($user) {
            $query->forUser($user->id)->latest();
        }, 'parent', 'children']);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CrPresentationCategory $category): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$category->canBeEditedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:cr_presentation_categories,id',
            'is_active' => 'boolean'
        ]);

        try {
            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CrPresentationCategory $category): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$category->canBeDeletedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if category has presentations
        if ($category->presentations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing presentations'
            ], 422);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        $categories = CrPresentationCategory::active()
            ->forSchool($schoolId)
            ->withCount(['presentations' => function ($query) use ($user) {
                $query->forUser($user->id);
            }])
            ->ordered()
            ->get();

        $stats = [
            'total_categories' => $categories->count(),
            'categories_with_presentations' => $categories->where('presentations_count', '>', 0)->count(),
            'total_presentations' => $categories->sum('presentations_count'),
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'presentation_count' => $category->presentations_count,
                    'is_system' => $category->is_system
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Build category tree structure
     */
    protected function buildCategoryTree($categories)
    {
        $rootCategories = $categories->whereNull('parent_id');
        
        return $rootCategories->map(function ($category) use ($categories) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'color' => $category->color,
                'icon' => $category->icon,
                'is_system' => $category->is_system,
                'presentation_count' => $category->presentations_count ?? count($category->presentations),
                'children' => $this->buildChildrenTree($category, $categories)
            ];
        })->toArray();
    }

    /**
     * Build children tree recursively
     */
    protected function buildChildrenTree($category, $allCategories)
    {
        $children = $allCategories->where('parent_id', $category->id);
        
        return $children->map(function ($child) use ($allCategories) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'description' => $child->description,
                'color' => $child->color,
                'icon' => $child->icon,
                'is_system' => $child->is_system,
                'presentation_count' => $child->presentations_count ?? count($child->presentations),
                'children' => $this->buildChildrenTree($child, $allCategories)
            ];
        })->toArray();
    }
}
