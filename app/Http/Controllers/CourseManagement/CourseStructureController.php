<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\CourseManagement\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseStructureController extends Controller
{
    /**
     * Get all courses with their complete structure (levels, sections, lessons)
     */
    public function index()
    {
        try {
            $user = auth()->user();
            
            // Get courses based on user role
            if ($user->hasRole('admin')) {
                $courses = Course::with(['levels.sections.lessons'])
                    ->orderBy('name')
                    ->get();
            } else {
                // Get teacher's assigned courses
                $teacher = $user->teacher;
                if (!$teacher) {
                    return response()->json([
                        'success' => true,
                        'data' => []
                    ]);
                }
                
                $courses = Course::with(['levels.sections.lessons'])
                    ->whereHas('teacherAssignments', function ($query) use ($teacher) {
                        $query->where('teacher_id', $teacher->id)
                              ->where('is_active', true);
                    })
                    ->orderBy('name')
                    ->get();
            }

            $structuredData = $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'levels' => $course->levels->map(function ($level) {
                        return [
                            'id' => $level->id,
                            'title' => $level->title,
                            'order' => $level->order,
                            'sections' => $level->sections->map(function ($section) {
                                return [
                                    'id' => $section->id,
                                    'title' => $section->title,
                                    'order' => $section->order,
                                    'lessons' => $section->lessons->map(function ($lesson) {
                                        return [
                                            'id' => $lesson->id,
                                            'title' => $lesson->title,
                                            'text' => $lesson->text,
                                            'order' => $lesson->order,
                                            'data' => $lesson->data,
                                        ];
                                    })->sortBy('order')->values()
                                ];
                            })->sortBy('order')->values()
                        ];
                    })->sortBy('order')->values()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load course structure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific course with its complete structure
     */
    public function show(Course $course)
    {
        try {
            $course->load(['levels.sections.lessons']);

            $structuredData = [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'levels' => $course->levels->map(function ($level) {
                    return [
                        'id' => $level->id,
                        'title' => $level->title,
                        'order' => $level->order,
                        'sections' => $level->sections->map(function ($section) {
                            return [
                                'id' => $section->id,
                                'title' => $section->title,
                                'order' => $section->order,
                                'lessons' => $section->lessons->map(function ($lesson) {
                                    return [
                                        'id' => $lesson->id,
                                        'title' => $lesson->title,
                                        'text' => $lesson->text,
                                        'order' => $lesson->order,
                                        'data' => $lesson->data,
                                    ];
                                })->sortBy('order')->values()
                            ];
                        })->sortBy('order')->values()
                    ];
                })->sortBy('order')->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses for the authenticated teacher
     */
    public function teacherCourses()
    {
        try {
            $user = auth()->user();
            $teacher = $user->teacher;
            
            if (!$teacher) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $courses = Course::with(['levels.sections.lessons'])
                ->whereHas('teacherAssignments', function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                          ->where('is_active', true);
                })
                ->orderBy('name')
                ->get();

            $structuredData = $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'levels' => $course->levels->map(function ($level) {
                        return [
                            'id' => $level->id,
                            'title' => $level->title,
                            'order' => $level->order,
                            'sections' => $level->sections->map(function ($section) {
                                return [
                                    'id' => $section->id,
                                    'title' => $section->title,
                                    'order' => $section->order,
                                    'lessons' => $section->lessons->map(function ($lesson) {
                                        return [
                                            'id' => $lesson->id,
                                            'title' => $lesson->title,
                                            'text' => $lesson->text,
                                            'order' => $lesson->order,
                                            'data' => $lesson->data,
                                        ];
                                    })->sortBy('order')->values()
                                ];
                            })->sortBy('order')->values()
                        ];
                    })->sortBy('order')->values()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load teacher courses: ' . $e->getMessage()
            ], 500);
        }
    }
}