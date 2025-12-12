<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseTeacherAssignment;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $courses = Course::with(['teachers', 'levels'])->get();
        $teachers = Teacher::with(['user', 'courses'])->get();
        $assignments = CourseTeacherAssignment::with(['course', 'teacher.user', 'assignedBy'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('CourseManagement/Teacher/Index', [
            'courses' => $courses,
            'teachers' => $teachers,
            'assignments' => $assignments,
        ]);
    }

    public function assignByCourse()
    {
        $courses = Course::with(['levels', 'teachers'])->get();
        $teachers = Teacher::with('user')->get();

        return Inertia::render('CourseManagement/Teacher/AssignByCourse', [
            'courses' => $courses,
            'teachers' => $teachers,
        ]);
    }

    public function assignByTeacher()
    {
        $teachers = Teacher::with(['user', 'courses'])->get();
        $courses = Course::with('levels')->get();

        return Inertia::render('CourseManagement/Teacher/AssignByTeacher', [
            'teachers' => $teachers,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:teachers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Check if assignment already exists
            $existingAssignment = CourseTeacherAssignment::where('course_id', $request->course_id)
                ->where('teacher_id', $request->teacher_id)
                ->first();

            if ($existingAssignment) {
                if ($existingAssignment->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This teacher is already assigned to this course.',
                    ], 422);
                } else {
                    // Reactivate existing assignment
                    $existingAssignment->update([
                        'is_active' => true,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => $request->notes,
                    ]);
                    $assignment = $existingAssignment;
                }
            } else {
                // Create new assignment
                $assignment = CourseTeacherAssignment::create([
                    'course_id' => $request->course_id,
                    'teacher_id' => $request->teacher_id,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                    'notes' => $request->notes,
                    'is_active' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned to course successfully.',
                'assignment' => $assignment->load(['course', 'teacher.user', 'assignedBy']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign teacher to course: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.course_id' => 'required|exists:courses,id',
            'assignments.*.teacher_id' => 'required|exists:teachers,id',
            'assignments.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($request->assignments as $assignmentData) {
                $existingAssignment = CourseTeacherAssignment::where('course_id', $assignmentData['course_id'])
                    ->where('teacher_id', $assignmentData['teacher_id'])
                    ->first();

                if ($existingAssignment) {
                    if ($existingAssignment->is_active) {
                        $skipped++;
                    } else {
                        $existingAssignment->update([
                            'is_active' => true,
                            'assigned_by' => Auth::id(),
                            'assigned_at' => now(),
                            'notes' => $assignmentData['notes'] ?? null,
                        ]);
                        $updated++;
                    }
                } else {
                    CourseTeacherAssignment::create([
                        'course_id' => $assignmentData['course_id'],
                        'teacher_id' => $assignmentData['teacher_id'],
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => $assignmentData['notes'] ?? null,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk assignment completed successfully.',
                'stats' => [
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete bulk assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignCoursesToTeacher(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($request->course_ids as $courseId) {
                $existingAssignment = CourseTeacherAssignment::where('course_id', $courseId)
                    ->where('teacher_id', $request->teacher_id)
                    ->first();

                if ($existingAssignment) {
                    if ($existingAssignment->is_active) {
                        $skipped++;
                    } else {
                        $existingAssignment->update([
                            'is_active' => true,
                            'assigned_by' => Auth::id(),
                            'assigned_at' => now(),
                            'notes' => $request->notes,
                        ]);
                        $updated++;
                    }
                } else {
                    CourseTeacherAssignment::create([
                        'course_id' => $courseId,
                        'teacher_id' => $request->teacher_id,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => $request->notes,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }

            DB::commit();

            // Return back with success message for Inertia
            return back()->with('success', [
                'message' => 'Courses assigned to teacher successfully!',
                'stats' => [
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to assign courses to teacher: ' . $e->getMessage()
            ]);
        }
    }

    public function assignTeachersToCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($request->teacher_ids as $teacherId) {
                $existingAssignment = CourseTeacherAssignment::where('course_id', $request->course_id)
                    ->where('teacher_id', $teacherId)
                    ->first();

                if ($existingAssignment) {
                    if ($existingAssignment->is_active) {
                        $skipped++;
                    } else {
                        $existingAssignment->update([
                            'is_active' => true,
                            'assigned_by' => Auth::id(),
                            'assigned_at' => now(),
                            'notes' => $request->notes,
                        ]);
                        $updated++;
                    }
                } else {
                    CourseTeacherAssignment::create([
                        'course_id' => $request->course_id,
                        'teacher_id' => $teacherId,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => $request->notes,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }

            DB::commit();

            // Return back with success message for Inertia
            return back()->with('success', [
                'message' => 'Teachers assigned to course successfully!',
                'stats' => [
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Failed to assign teachers to course: ' . $e->getMessage()
            ]);
        }
    }

    public function removeAssignment(CourseTeacherAssignment $assignment)
    {
        try {
            $assignment->update(['is_active' => false]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Teacher assignment removed successfully.',
                ]);
            }
            
            return back()->with('success', 'Teacher assignment removed successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove assignment: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to remove assignment: ' . $e->getMessage()]);
        }
    }



    public function removeAssignmentByIds(Request $request)
    {
        // Get data from query parameters for DELETE requests
        $teacherId = $request->query('teacher_id') ?? $request->input('teacher_id');
        $courseId = $request->query('course_id') ?? $request->input('course_id');
        
        $request->merge([
            'teacher_id' => $teacherId,
            'course_id' => $courseId,
        ]);
        
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            $assignment = CourseTeacherAssignment::where('teacher_id', $request->teacher_id)
                ->where('course_id', $request->course_id)
                ->where('is_active', true)
                ->first();

            if (!$assignment) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment not found.',
                    ], 404);
                }
                
                return back()->withErrors(['error' => 'Assignment not found.']);
            }

            $assignment->update(['is_active' => false]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Teacher assignment removed successfully.',
                ]);
            }
            
            return back()->with('success', 'Teacher assignment removed successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove assignment: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to remove assignment: ' . $e->getMessage()]);
        }
    }

    public function toggleAssignment(CourseTeacherAssignment $assignment)
    {
        try {
            $assignment->update(['is_active' => !$assignment->is_active]);
            $status = $assignment->is_active ? 'activated' : 'deactivated';

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment ' . $status . '.',
                    'assignment' => $assignment->load(['course', 'teacher.user', 'assignedBy']),
                ]);
            }
            
            return back()->with('success', 'Teacher assignment ' . $status . ' successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to toggle assignment: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to toggle assignment: ' . $e->getMessage()]);
        }
    }

    public function getCourseTeachers(Course $course)
    {
        $teachers = $course->teachers()->with('user')->get();
        
        return response()->json([
            'success' => true,
            'teachers' => $teachers,
        ]);
    }

    public function getTeacherCourses(Teacher $teacher)
    {
        $courses = $teacher->courses()->with('levels')->get();
        
        return response()->json([
            'success' => true,
            'courses' => $courses,
        ]);
    }

    public function previewCourse()
    {
        $user = auth()->user();
        
        // Get the teacher record for this user
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return Inertia::render('CourseManagement/Teacher/preview_course/Demo', [
                'courses' => []
            ]);
        }
        
        $courses = Course::with(['levels.sections.lessons'])
            ->whereHas('teacherAssignments', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->where('is_active', true);
            })
            ->orderBy('name')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'levels' => $course->levels->map(function ($level) {
                        return [
                            'id' => $level->id,
                            'name' => $level->title,
                            'order' => $level->order,
                            'sections' => $level->sections->map(function ($section) {
                                return [
                                    'id' => $section->id,
                                    'name' => $section->title,
                                    'order' => $section->order,
                                    'lessons' => $section->lessons->map(function ($lesson) {
                                        return [
                                            'id' => $lesson->id,
                                            'name' => $lesson->title,
                                            'order' => $lesson->order,
                                            'text' => $lesson->text,
                                        ];
                                    })->sortBy('order')->values()
                                ];
                            })->sortBy('order')->values()
                        ];
                    })->sortBy('order')->values()
                ];
            });

        return Inertia::render('CourseManagement/Teacher/preview_course/Demo', [
            'courses' => $courses
        ]);
    }
}