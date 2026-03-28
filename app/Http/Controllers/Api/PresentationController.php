<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\PresentationCategory;
use App\Http\Requests\API\PresentationStoreRequest;
use App\Http\Requests\API\PresentationUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresentationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Presentation::with(['category', 'user']);

        // Filter by user
        if ($request->has('user_id') && $request->user_id != 'all') {
            $query->where('user_id', $request->user_id);
        } else {
            $query->forUser($user->id);
        }

        // Filter by school
        if ($request->has('school_id')) {
            $query->forSchool($request->school_id);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->inCategory($request->category_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'published') {
                $query->published();
            } elseif ($request->status === 'draft') {
                $query->draft();
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Include templates
        if ($request->has('include_templates') && $request->include_templates) {
            // No additional filter needed
        } else {
            $query->where('is_template', false);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $presentations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $presentations,
            'meta' => [
                'total' => $presentations->total(),
                'per_page' => $presentations->perPage(),
                'current_page' => $presentations->currentPage(),
                'last_page' => $presentations->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PresentationStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $data = $request->validated();
            $slides = $data['slides'] ?? [];

            // Create presentation record first
            $presentation = Presentation::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'classroom_id' => $data['classroom_id'] ?? null,
                'current_slide_index' => $data['current_slide_index'] ?? 0,
                'use_phases' => $data['use_phases'] ?? false,
                'has_initialized_phases' => $data['has_initialized_phases'] ?? false,
                'metadata' => [
                    'slide_count' => count($slides),
                    'version' => '1.0',
                    'created_from' => 'api'
                ],
                'status' => $data['status'] ?? 'draft',
                'is_public' => $data['is_public'] ?? false,
                'is_template' => $data['is_template'] ?? false,
            ]);

            // Save slides to file storage
            if (!empty($slides)) {
                $presentation->saveSlidesToFile($slides);
            }

            // Create initial backup
            $presentation->createBackup('initial');

            DB::commit();

            // Load relationships for response
            $presentation->load(['category', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Presentation created successfully',
                'data' => $presentation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create presentation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Presentation $presentation): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$presentation->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $presentation->load(['category', 'user', 'backups' => function ($query) {
            $query->latest()->take(5);
        }]);

        // Load slides from file storage
        $slides = $presentation->loadSlidesFromFile();
        $presentationData = $presentation->toArray();
        $presentationData['slides'] = $slides;

        return response()->json([
            'success' => true,
            'data' => $presentationData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PresentationUpdateRequest $request, Presentation $presentation): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if ($presentation->user_id !== $user->id && !$user->hasRole('school_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $oldSlides = $presentation->loadSlidesFromFile();
            $newSlides = $data['slides'] ?? null;

            // Update presentation metadata
            $presentation->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? $presentation->description,
                'category_id' => $data['category_id'] ?? $presentation->category_id,
                'classroom_id' => $data['classroom_id'] ?? $presentation->classroom_id,
                'current_slide_index' => $data['current_slide_index'] ?? $presentation->current_slide_index,
                'use_phases' => $data['use_phases'] ?? $presentation->use_phases,
                'has_initialized_phases' => $data['has_initialized_phases'] ?? $presentation->has_initialized_phases,
                'status' => $data['status'] ?? $presentation->status,
                'is_public' => $data['is_public'] ?? $presentation->is_public,
                'is_template' => $data['is_template'] ?? $presentation->is_template,
                'metadata' => array_merge($presentation->metadata ?? [], [
                    'slide_count' => $newSlides ? count($newSlides) : count($oldSlides),
                    'last_updated_from' => 'api',
                    'updated_at' => now()->toISOString()
                ])
            ]);

            // Update slides file if provided
            if ($newSlides !== null) {
                $presentation->saveSlidesToFile($newSlides);
                
                // Create backup if slides changed significantly
                $slidesChanged = json_encode($oldSlides) !== json_encode($newSlides);
                if ($slidesChanged || $request->has('create_backup')) {
                    $presentation->createBackup($request->get('backup_reason', 'auto'));
                }
            }

            DB::commit();

            $presentation->load(['category', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Presentation updated successfully',
                'data' => $presentation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update presentation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presentation $presentation): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if ($presentation->user_id !== $user->id && !$user->hasRole('school_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Create backup before deletion
            $presentation->createBackup('before_delete');

            // Delete slides file
            $presentation->deleteSlidesFile();

            // Soft delete
            $presentation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presentation deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete presentation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Duplicate a presentation
     */
    public function duplicate(Presentation $presentation): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$presentation->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $newPresentation = $presentation->replicate([
                'title',
                'description',
                'category_id',
                'slides',
                'current_slide_index',
                'use_phases',
                'has_initialized_phases',
                'metadata',
                'status',
                'is_public',
                'is_template'
            ]);

            $newPresentation->user_id = $user->id;
            $newPresentation->title = $presentation->title . ' (Copy)';
            $newPresentation->slug = null; // Will be generated automatically
            $newPresentation->status = 'draft';
            $newPresentation->is_template = false;
            $newPresentation->is_public = false;
            $newPresentation->metadata = array_merge($newPresentation->metadata ?? [], [
                'duplicated_from' => $presentation->id,
                'duplicated_at' => now()->toISOString()
            ]);

            $newPresentation->save();
            $newPresentation->createBackup('duplicate');

            DB::commit();

            $newPresentation->load(['category', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Presentation duplicated successfully',
                'data' => $newPresentation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate presentation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get presentation statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        $stats = [
            'total_presentations' => Presentation::forUser($user->id)->count(),
            'published_presentations' => Presentation::forUser($user->id)->published()->count(),
            'draft_presentations' => Presentation::forUser($user->id)->draft()->count(),
            'total_size' => 0,
            'categories' => [],
            'recent_activity' => []
        ];

        // Calculate total size
        $presentations = Presentation::forUser($user->id)->get(['slides', 'metadata']);
        foreach ($presentations as $presentation) {
            $stats['total_size'] += strlen(json_encode([
                'slides' => $presentation->slides,
                'metadata' => $presentation->metadata
            ]));
        }

        // Category breakdown
        $categoryStats = Presentation::forUser($user->id)
            ->join('presentation_categories', 'presentations.category_id', '=', 'presentation_categories.id')
            ->selectRaw('presentation_categories.name, presentation_categories.color, COUNT(*) as count')
            ->groupBy('presentation_categories.id', 'presentation_categories.name', 'presentation_categories.color')
            ->get();

        $stats['categories'] = $categoryStats->toArray();

        // Recent activity
        $recentPresentations = Presentation::forUser($user->id)
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'updated_at', 'status', 'category_id']);

        $stats['recent_activity'] = $recentPresentations->toArray();

        // Format size
        $bytes = $stats['total_size'];
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        $stats['total_size_formatted'] = round($bytes, 2) . ' ' . $units[$pow];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Search presentations
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 20), 50);

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $presentations = Presentation::forUser($user->id)
            ->search($query)
            ->with(['category', 'user'])
            ->limit($limit)
            ->get(['id', 'title', 'description', 'slug', 'category_id', 'user_id', 'updated_at', 'status']);

        return response()->json([
            'success' => true,
            'data' => $presentations
        ]);
    }
}
