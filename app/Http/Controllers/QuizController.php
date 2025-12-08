<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes.
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['grade', 'subject', 'createdBy'])
            ->withCount('questions');

        // Get authenticated user's school information
        $user_id = Auth::user()->id;
        $teacher = Teacher::where('user_id', $user_id)->first();
        $school_id = $teacher->school_id;
$schoolId= $teacher->school_id;
        // Load school with grades and subjects for filter options
        $school = School::where('id', $school_id)
            ->with(['grades', 'subjects'])
            ->first();

        // Filter by school (required for multi-tenant systems)
        // Use school_id from request, or fall back to authenticated user's school
        // $schoolId = $request->input('school_id') ?? $school_id;
        
        if ($schoolId) {
            $query->forSchool($schoolId);
        }

        // Filter by grade
        if ($request->has('grade_id')) {
            $query->forGrade($request->grade_id);
        }

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        // If status is 'all' or not provided, show all quizzes (no filter)

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Order by most recent
        $query->orderBy('created_at', 'desc');

        // Return quizzes along with filter options

                $quizzes = Quiz::with(['grade', 'subject', 'createdBy'])
            ->withCount('questions')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'quizzes' => $quizzes,
            'quizzes2' => $query->get(),
            'filters' => [
                'grades' => $school->grades ?? [],
                'subjects' => $school->subjects ?? []
            ]
        ]);
    }

    /**
     * Display the specified quiz with questions.
     */
    public function show($id)
    {
        $quiz = Quiz::with([
            'questions.questionType',
            'questions.options',
            'grade',
            'subject'
        ])->findOrFail($id);

        return response()->json($quiz);
    }

    /**
     * Store a newly created quiz.
     */
    public function store(Request $request)
    {
        $user_id= Auth::user()->id;
         $teacher= Teacher::where('user_id',$user_id)->first();
       $school_id=$teacher->school_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'school_id' => 'nullable|exists:schools,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_id' => 'nullable|exists:grades,id',
            'status' => 'nullable|in:draft,active,archived',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'allow_review' => 'nullable|boolean',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        // Use provided school_id or fall back to authenticated user's school
        // if (!isset($validated['school_id'])) {
        //     $validated['school_id'] = Auth::user()->school_id ?? null;
        // }

        $validated['school_id'] = $school_id;
        $validated['created_by_id'] = Auth::id();

        DB::beginTransaction();
        try {
            $quiz = Quiz::create($validated);

            // Attach questions if provided
            if (!empty($validated['question_ids'])) {
                $questionsWithOrder = [];
                foreach ($validated['question_ids'] as $index => $questionId) {
                    $questionsWithOrder[$questionId] = ['order_index' => $index];
                }
                $quiz->questions()->attach($questionsWithOrder);
            }

            DB::commit();

            // Reload quiz with questions_count for consistent response format
            $quiz->loadCount('questions');
            
            return response()->json($quiz, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create quiz'], 500);
        }
    }

    /**
     * Update the specified quiz.
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_id' => 'nullable|exists:grades,id',
            'status' => 'nullable|in:draft,active,archived',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'allow_review' => 'nullable|boolean',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        DB::beginTransaction();
        try {
            $quiz->update($validated);

            // Update questions if provided
            if (isset($validated['question_ids'])) {
                $questionsWithOrder = [];
                foreach ($validated['question_ids'] as $index => $questionId) {
                    $questionsWithOrder[$questionId] = ['order_index' => $index];
                }
                $quiz->questions()->sync($questionsWithOrder);
            }

            DB::commit();

            // Reload quiz with questions_count for consistent response format
            $quiz->loadCount('questions');
            
            return response()->json($quiz);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update quiz'], 500);
        }
    }

    /**
     * Remove the specified quiz.
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    /**
     * Duplicate a quiz.
     */
    public function duplicate($id)
    {
        $originalQuiz = Quiz::with('questions')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            $newQuiz = $originalQuiz->replicate();
            $newQuiz->name = $originalQuiz->name . ' (Copy)';
            $newQuiz->status = 'draft';
            $newQuiz->created_by_id = Auth::id();
            $newQuiz->save();
            
            // Copy questions with their order
            if ($originalQuiz->questions->isNotEmpty()) {
                $questionsWithOrder = [];
                foreach ($originalQuiz->questions as $question) {
                    $questionsWithOrder[$question->id] = [
                        'order_index' => $question->pivot->order_index
                    ];
                }
                $newQuiz->questions()->attach($questionsWithOrder);
            }
            
            DB::commit();
            
            // Reload quiz with questions_count for consistent response format
            $newQuiz->loadCount('questions');
            
            return response()->json($newQuiz, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to duplicate quiz'], 500);
        }
    }

    /**
     * Export quiz as JSON.
     */
    public function export($id)
    {
        $quiz = Quiz::with([
            'questions.questionType',
            'questions.options',
            'grade',
            'subject'
        ])->findOrFail($id);

        $filename = str_replace(' ', '_', $quiz->name) . '_' . date('Y-m-d') . '.json';

        return response()->json($quiz)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get filter options (grades and subjects) for quiz creation/filtering.
     */
    public function filterOptions(Request $request)
    {
        $user_id = Auth::user()->id;
        $teacher = Teacher::where('user_id', $user_id)->first();
        $school_id = $teacher->school_id;

        $school = School::where('id', $school_id)
            ->with(['grades', 'subjects'])
            ->first();

        return response()->json([
            'grades' => $school->grades ?? [],
            'subjects' => $school->subjects ?? []
        ]);
    }

    /**
     * Get quiz analytics.
     */
    public function analytics($id)
    {
        $quiz = Quiz::with(['attempts', 'questions'])->findOrFail($id);
        
        // Calculate analytics
        $totalAttempts = $quiz->attempts->count();
        $completedAttempts = $quiz->attempts->where('completed_at', '!=', null);
        
        $avgScore = $completedAttempts->avg('percentage') ?? 0;
        $avgTime = $completedAttempts->avg(function ($attempt) {
            if ($attempt->completed_at && $attempt->started_at) {
                return $attempt->completed_at->diffInSeconds($attempt->started_at);
            }
            return 0;
        }) ?? 0;
        
        $completionRate = $totalAttempts > 0 
            ? ($completedAttempts->count() / $totalAttempts) * 100 
            : 0;

        // Top performers
        $topPerformers = $completedAttempts
            ->sortByDesc('percentage')
            ->take(5)
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user->name ?? 'Unknown',
                    'score' => round($attempt->percentage, 2),
                    'time' => $attempt->completed_at->diffInSeconds($attempt->started_at)
                ];
            })
            ->values();

        // Struggling students (lowest scores)
        $strugglingStudents = $completedAttempts
            ->where('percentage', '<', 60)
            ->sortBy('percentage')
            ->take(5)
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user->name ?? 'Unknown',
                    'score' => round($attempt->percentage, 2),
                    'attempts' => $quiz->attempts->where('user_id', $attempt->user_id)->count()
                ];
            })
            ->values();

        // Question analysis (mock data - implement based on your needs)
        $questionAnalysis = $quiz->questions->map(function ($question, $index) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'success_rate' => rand(50, 95),
                'avg_time' => rand(15, 90),
                'difficulty' => $question->difficulty ?? 'Medium'
            ];
        });

        return response()->json([
            'quiz' => $quiz,
            'analytics' => [
                'totalAttempts' => $totalAttempts,
                'avgScore' => round($avgScore, 2),
                'avgTime' => round($avgTime),
                'completionRate' => round($completionRate, 2),
                'scoreTrend' => 0, // Implement trend calculation
                'completionTrend' => 0 // Implement trend calculation
            ],
            'questions' => $questionAnalysis,
            'topPerformers' => $topPerformers,
            'strugglingStudents' => $strugglingStudents
        ]);
    }
}
