<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\School;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index()
    {
        // return 'okkkkk';
        $subjects = Subject::with('school')->get();
        // $subjects = Subject::with('school')->paginate(10);
        $schools = School::select('id', 'name')->get();
        $curricula = Curriculum::select('id', 'name', 'subject_id')->get();

        return Inertia::render('my_class/admin/Subjects/Index', [
            'records' => Subject::with('school')
                // ->latest()
                ->paginate(100),
            'schools' => School::select('id', 'name')
                // ->where('active', true)
                ->get(),
            'curricula' => $curricula
        ]);
    }
 
public function getCurricula($subjectId)
{
    $subject = Subject::find($subjectId);
    if (!$subject) {
        return response()->json(['error' => 'Subject not found'], 404);
    }

    $curricula = $subject->curricula; // Assuming a relationship exists
    return response()->json($curricula);
}

/**
 * Get all subjects for API (used in question bank filters).
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function apiIndex(Request $request)
{
    try {
        $query = Subject::query();
        
        // Filter by grade if provided
        if ($request->has('grade_id')) {
            $query->whereHas('grades', function($q) use ($request) {
                $q->where('grades.id', $request->grade_id);
            });
        }
        
        // Get authenticated user's school
        $user = $request->user();
        if ($user && isset($user->school_id)) {
            $query->where('school_id', $user->school_id);
        }
        
        $subjects = $query->select('id', 'name', 'school_id', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'SUBJECTS_ERROR',
                'message' => 'Failed to retrieve subjects',
                'details' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ],
        ], 500);
    }
}

/**
 * Get topics for filtering questions
 */
public function getTopics(Request $request)
{
    try {
        $query = \App\Models\Topic::query();
        
        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        $topics = $query->select('id', 'name', 'subject_id', 'description')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $topics,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'TOPICS_ERROR',
                'message' => 'Failed to retrieve topics',
                'details' => config('app.debug') ? $e->getMessage() : null,
                'timestamp' => now()->toIso8601String(),
            ],
        ], 500);
    }
}
    // public function getCurricula($subjectId)
    // {
    //     return Curriculum::where('subject_id', $subjectId)
    //         ->select('id', 'name')
    //         ->get();
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nour_name' => 'nullable|string|max:255',
            'nour_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'notes' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'color_bg' => 'nullable|string|max:22',
            'color_text' => 'nullable|string|max:22',
            'lesson_plan_templates' => 'nullable|json',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'Subject created successfully');
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nour_name' => 'nullable|string|max:255',
            'nour_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'notes' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'color_bg' => 'nullable|string|max:22',
            'color_text' => 'nullable|string|max:22',
            'lesson_plan_templates' => 'nullable|json',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Subject updated successfully');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully');
    }

    /**
     * Update lesson plan templates for a subject
     */
    public function updateLessonPlanTemplates(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'lesson_plan_templates' => 'required|string',
        ]);

        // Validate that the string is valid JSON
        $decoded = json_decode($validated['lesson_plan_templates'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'message' => 'Invalid JSON format: ' . json_last_error_msg()
            ], 422);
        }

        $subject->update([
            'lesson_plan_templates' => $validated['lesson_plan_templates']
        ]);

        // Reload the subject with the updated templates
        $subject->refresh();

        // Parse the templates for the response
        $templates = [];
        if ($subject->lesson_plan_templates) {
            if (is_string($subject->lesson_plan_templates)) {
                $templates = json_decode($subject->lesson_plan_templates, true) ?? [];
            } else {
                $templates = $subject->lesson_plan_templates;
            }
        }

        return response()->json([
            'message' => 'Lesson plan templates updated successfully',
            'subject' => $subject,
            'templates' => $templates
        ]);
    }

    /**
     * Show the lesson plan templates management page
     */
    public function manageLessonPlanTemplates(Subject $subject)
    {
        // Load the school relationship
        $subject->load('school');

        // Parse the JSON templates if they exist
        $templates = [];
        if ($subject->lesson_plan_templates) {
            // If it's already a string, parse it; if it's already an array, use it directly
            if (is_string($subject->lesson_plan_templates)) {
                $templates = json_decode($subject->lesson_plan_templates, true) ?? [];
            } else {
                $templates = $subject->lesson_plan_templates;
            }
        }

        return Inertia::render('my_class/admin/Subjects/LessonPlanTemplates', [
            'subject' => $subject,
            'templates' => $templates
        ]);
    }

    /**
     * Get lesson plan templates for a subject (AJAX)
     */
    public function getLessonPlanTemplates(Subject $subject)
    {
        // Parse the JSON templates if they exist
        $templates = [];
        if ($subject->lesson_plan_templates) {
            // If it's already a string, parse it; if it's already an array, use it directly
            if (is_string($subject->lesson_plan_templates)) {
                $templates = json_decode($subject->lesson_plan_templates, true) ?? [];
            } else {
                $templates = $subject->lesson_plan_templates;
            }
        }

        // Additionally, include DB-backed templates for this subject + global templates (subject_id == null)
        $dbTemplates = \App\Models\CourseManagement\LessonPlanTemplate::query()
            ->where(function($q) use ($subject) {
                $q->whereNull('subject_id')
                  ->orWhere('subject_id', $subject->id);
            })
            ->active()
            ->ordered()
            ->get()
            ->map(fn($t) => $t->toArray())
            ->toArray();

        // Merge arrays: keep legacy templates first, then DB templates
        $merged = array_values(array_merge($templates, $dbTemplates));

        return response()->json([
            'subject' => $subject->load('school'),
            'templates' => $merged
        ]);
    }









    public function validateImport(Request $request)
    {
        $statuses = [];

        // Find the ID column (column with is_id = true)
        $idColumn = collect($request->columns)
            ->where('is_id', true)
            ->first();

        // Get visible required columns
        $requiredColumns = collect($request->columns)
            ->where('required', true)
            ->where('hidden', false)
            ->pluck('key')
            ->toArray();

        foreach ($request->data as $row) {
            $status = 'new';

            // Check if record exists by ID field
            if ($idColumn && !empty($row[$idColumn['key']])) {
                $subject = Subject::where('id', $row[$idColumn['key']])->first();
                if ($subject) {
                    $status = 'update';
                    $statuses[] = $status;
                    continue;
                }
            }

            // Check for required fields (only visible ones)
            $missingFields = false;
            foreach ($requiredColumns as $field) {
                if (empty($row[$field])) {
                    $missingFields = true;
                    break;
                }
            }

            if ($missingFields) {
                $status = 'invalid';
                $statuses[] = $status;
                continue;
            }

            // Check if school exists (if school column is visible and required)
            if (in_array('school', $requiredColumns)) {
                $school = School::where('name', $row['school'])->first();
                if (!$school) {
                    $status = 'invalid';
                    $statuses[] = $status;
                    continue;
                }

                // Check for duplicate subject in same school
                if (in_array('name', $requiredColumns)) {
                    $existingSubject = Subject::where('name', $row['name'])
                        ->whereHas('school', function($query) use ($row) {
                            $query->where('name', $row['school']);
                        })
                        ->first();

                    if ($existingSubject) {
                        $status = 'duplicate';
                    }
                }
            }

            $statuses[] = $status;
        }

        return response()->json(['statuses' => $statuses]);
    }
    public function import(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        try {
            DB::beginTransaction();
            $importId = Str::uuid();
            $affectedSubjects = [];

            // Find the ID column
            $idColumn = collect($request->columns)
                ->where('is_id', true)
                ->first();

            // Get visible columns
            $visibleColumns = collect($request->columns)
                ->where('hidden', false)
                ->pluck('key')
                ->toArray();

            foreach ($request->data as $row) {
                try {
                    // Find or create subject
                    $subject = null;

                    // Check for existing subject by ID if provided
                    if ($idColumn && !empty($row[$idColumn['key']])) {
                        $subject = Subject::where($idColumn['key'], $row[$idColumn['key']])->first();
                    }

                    // Only process visible columns
                    $subjectData = [];
                    foreach ($visibleColumns as $column) {
                        if (isset($row[$column])) {
                            // Handle special case for school
                            if ($column === 'school') {
                                $school = School::where('name', $row[$column])->first();
                                if ($school) {
                                    $subjectData['school_id'] = $school->id;
                                }
                            } else {
                                $subjectData[$column] = $row[$column];
                            }
                        }
                    }

                    if ($subject) {
                        // Store original data for undo
                        $affectedSubjects[] = [
                            'id' => $subject->id,
                            'original_data' => $subject->toArray()
                        ];

                        $subject->update($subjectData);
                        $results['success'][] = "Updated subject: {$row['name']}";
                    } else {
                        $subject = Subject::create($subjectData);
                        $affectedSubjects[] = [
                            'id' => $subject->id,
                            'original_data' => null
                        ];
                        $results['success'][] = "Created subject: {$row['name']}";
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing subject {$row['name']}: " . $e->getMessage();
                }
            }

            Cache::put("subject_import_{$importId}", $affectedSubjects, now()->addHours(24));
            DB::commit();

            return response()->json([
                'results' => $results,
                'importId' => $importId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => [],
                'errors' => ['An error occurred while processing the data: ' . $e->getMessage()]
            ], 500);
        }
    }



    public function undoImport($importId)
    {
        try {
            DB::beginTransaction();

            $affectedSubjects = Cache::get("subject_import_{$importId}");
            if (!$affectedSubjects) {
                throw new \Exception('Import data not found or expired');
            }

            foreach ($affectedSubjects as $subjectData) {
                $subject = Subject::find($subjectData['id']);
                if (!$subject) continue;

                if ($subjectData['original_data'] === null) {
                    // This was a new record - delete it
                    $subject->delete();
                } else {
                    // This was an update - restore original data
                    $subject->update($subjectData['original_data']);
                }
            }

            Cache::forget("subject_import_{$importId}");
            DB::commit();

            return response()->json(['message' => 'Import successfully undone']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}



