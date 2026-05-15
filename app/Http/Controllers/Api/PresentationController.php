<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrPresentation;
use App\Models\UserPresentation;
use App\Models\StudentPresentationAttempt;
use App\Http\Requests\Api\PresentationStoreRequest;
use App\Http\Requests\Api\PresentationUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresentationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = CrPresentation::with(['category', 'user']);

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
        $categoryId = $request->get('cr_presentation_category_id') ?? $request->get('category_id');
        if ($categoryId) {
            $query->inCategory($categoryId);
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
            $categoryId = $data['cr_presentation_category_id'] ?? $data['category_id'] ?? null;

            // Create presentation record first
            $presentation = CrPresentation::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'cr_presentation_category_id' => $categoryId,
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'classroom_id' => $data['classroom_id'] ?? null,
                'slides' => $slides,
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
    public function show(CrPresentation $presentation): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$presentation->canBeAccessedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $presentation->load(['category', 'user']);

        return response()->json([
            'success' => true,
            'data' => $presentation
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PresentationUpdateRequest $request, CrPresentation $presentation): JsonResponse
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
            $newSlides = $data['slides'] ?? null;
            $categoryId = $data['cr_presentation_category_id'] ?? $data['category_id'] ?? $presentation->cr_presentation_category_id;

            // Update presentation metadata
            $updatePayload = [
                'title' => $data['title'],
                'description' => $data['description'] ?? $presentation->description,
                'cr_presentation_category_id' => $categoryId,
                'classroom_id' => $data['classroom_id'] ?? $presentation->classroom_id,
                'current_slide_index' => $data['current_slide_index'] ?? $presentation->current_slide_index,
                'use_phases' => $data['use_phases'] ?? $presentation->use_phases,
                'has_initialized_phases' => $data['has_initialized_phases'] ?? $presentation->has_initialized_phases,
                'status' => $data['status'] ?? $presentation->status,
                'is_public' => $data['is_public'] ?? $presentation->is_public,
                'is_template' => $data['is_template'] ?? $presentation->is_template,
                'metadata' => array_merge($presentation->metadata ?? [], [
                    'slide_count' => is_array($newSlides) ? count($newSlides) : count($presentation->slides ?? []),
                    'last_updated_from' => 'api',
                    'updated_at' => now()->toISOString()
                ])
            ];

            if ($newSlides !== null) {
                $updatePayload['slides'] = $newSlides;
            }

            $presentation->update($updatePayload);

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
    public function destroy(CrPresentation $presentation): JsonResponse
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
    public function duplicate(CrPresentation $presentation): JsonResponse
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
                'cr_presentation_category_id',
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
            'total_presentations' => CrPresentation::forUser($user->id)->count(),
            'published_presentations' => CrPresentation::forUser($user->id)->published()->count(),
            'draft_presentations' => CrPresentation::forUser($user->id)->draft()->count(),
            'total_size' => 0,
            'categories' => [],
            'recent_activity' => []
        ];

        // Calculate total size
        $presentations = CrPresentation::forUser($user->id)->get(['slides', 'metadata']);
        foreach ($presentations as $presentation) {
            $stats['total_size'] += strlen(json_encode([
                'slides' => $presentation->slides,
                'metadata' => $presentation->metadata
            ]));
        }

        // Category breakdown
        $categoryStats = CrPresentation::forUser($user->id)
            ->join('cr_presentation_categories', 'cr_presentations.cr_presentation_category_id', '=', 'cr_presentation_categories.id')
            ->selectRaw('cr_presentation_categories.name, cr_presentation_categories.color, COUNT(*) as count')
            ->groupBy('cr_presentation_categories.id', 'cr_presentation_categories.name', 'cr_presentation_categories.color')
            ->get();

        $stats['categories'] = $categoryStats->toArray();

        // Recent activity
        $recentPresentations = CrPresentation::forUser($user->id)
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'updated_at', 'status', 'cr_presentation_category_id']);

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

        $presentations = CrPresentation::forUser($user->id)
            ->search($query)
            ->with(['category', 'user'])
            ->limit($limit)
            ->get(['id', 'title', 'description', 'slug', 'cr_presentation_category_id', 'user_id', 'updated_at', 'status']);

        return response()->json([
            'success' => true,
            'data' => $presentations
        ]);
    }

    // ── V8 Presentation Save/Load/Share Methods ─────────────────────

    /**
     * Save a v8 presentation to user account
     */
    public function saveV8Presentation(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $presentation = UserPresentation::create([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'presentation_data' => $request->input('presentation_data'),
                'is_public' => $request->input('is_public', false),
            ]);

            DB::commit();

            $shareUrl = url('/student-presentation/' . $presentation->share_token);

            return response()->json([
                'success' => true,
                'message' => 'Presentation saved successfully',
                'data' => [
                    'id' => $presentation->id,
                    'share_token' => $presentation->share_token,
                    'share_url' => $shareUrl
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save presentation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * List user's v8 presentations
     */
    public function listV8Presentations(Request $request): JsonResponse
    {
        $user = Auth::user();

        $presentations = UserPresentation::where('user_id', $user->id)
            ->withCount('studentAttempts')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($presentation) {
                return [
                    'id' => $presentation->id,
                    'title' => $presentation->title,
                    'description' => $presentation->description,
                    'share_token' => $presentation->share_token,
                    'share_url' => url('/student-presentation/' . $presentation->share_token),
                    'created_at' => $presentation->created_at->toISOString(),
                    'attempt_count' => $presentation->student_attempts_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $presentations
        ]);
    }

    /**
     * Load a specific v8 presentation
     */
    public function loadV8Presentation($id): JsonResponse
    {
        $user = Auth::user();

        $presentation = UserPresentation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $presentation->id,
                'title' => $presentation->title,
                'description' => $presentation->description,
                'presentation_data' => $presentation->presentation_data,
                'share_token' => $presentation->share_token,
                'share_url' => url('/student-presentation/' . $presentation->share_token),
                'created_at' => $presentation->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * Delete a v8 presentation
     */
    public function deleteV8Presentation($id): JsonResponse
    {
        $user = Auth::user();

        $presentation = UserPresentation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();
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
     * Load shared presentation by share token (for students)
     */
    public function loadSharedPresentation($shareToken): JsonResponse
    {
        $presentation = UserPresentation::where('share_token', $shareToken)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $presentation->id,
                'title' => $presentation->title,
                'description' => $presentation->description,
                'presentation_data' => $presentation->presentation_data,
            ]
        ]);
    }

    /**
     * Submit student attempt
     */
    public function submitStudentAttempt(Request $request, $shareToken): JsonResponse
    {
        $presentation = UserPresentation::where('share_token', $shareToken)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $attempt = StudentPresentationAttempt::create([
                'presentation_id' => $presentation->id,
                'student_identifier' => $request->input('student_identifier'),
                'quiz_attempts' => $request->input('quiz_attempts'),
                'total_score' => $request->input('total_score', 0),
                'total_questions' => $request->input('total_questions', 0),
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attempt saved successfully',
                'data' => [
                    'attempt_id' => $attempt->id
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attempt',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get v8 presentation statistics
     */
    public function getV8Statistics($id): JsonResponse
    {
        $user = Auth::user();

        $presentation = UserPresentation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $attempts = $presentation->studentAttempts;

        $totalAttempts = $attempts->count();
        $uniqueStudents = $attempts->pluck('student_identifier')->unique()->count();

        if ($totalAttempts === 0) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_attempts' => 0,
                    'unique_students' => 0,
                    'average_score' => 0,
                    'high_score' => 0,
                    'low_score' => 0,
                    'score_distribution' => []
                ]
            ]);
        }

        $scores = $attempts->pluck('total_score');
        $averageScore = $scores->sum() / $totalAttempts;
        $highScore = $scores->max();
        $lowScore = $scores->min();

        // Score distribution
        $distribution = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            'below-60' => 0,
        ];

        foreach ($scores as $score) {
            if ($score >= 90) $distribution['90-100']++;
            elseif ($score >= 80) $distribution['80-89']++;
            elseif ($score >= 70) $distribution['70-79']++;
            elseif ($score >= 60) $distribution['60-69']++;
            else $distribution['below-60']++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_attempts' => $totalAttempts,
                'unique_students' => $uniqueStudents,
                'average_score' => round($averageScore, 2),
                'high_score' => $highScore,
                'low_score' => $lowScore,
                'score_distribution' => $distribution
            ]
        ]);
    }

    /**
     * Get v8 presentation attempt history
     */
    public function getV8AttemptHistory($id): JsonResponse
    {
        $user = Auth::user();

        $presentation = UserPresentation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $attempts = $presentation->studentAttempts()
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'student_identifier' => $attempt->student_identifier,
                    'quiz_attempts' => $attempt->quiz_attempts,
                    'total_score' => $attempt->total_score,
                    'total_questions' => $attempt->total_questions,
                    'completed_at' => $attempt->completed_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attempts
        ]);
    }
}
