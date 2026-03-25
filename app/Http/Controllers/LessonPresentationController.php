<?php

namespace App\Http\Controllers;

use App\Models\free\LessonPresentation;
use App\Models\free\LessonPresentationSlide;
use App\Models\CourseManagement\LessonPlanTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonPresentationController extends Controller
{
    public function index(Request $request)
    {
        // Return list of presentations for the current teacher/school
        $query = LessonPresentation::with(['slides'])->withCount('slides');

        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        return $query->get();
    }



    public function getTeacherGrades(Request $request)
    {
        $user = $request->user();

        if (!$user->teacher) {
            return response()->json(['error' => 'User is not a teacher'], 403);
        }

        $assignments = \App\Models\ClassroomSubjectTeacher::where('teacher_id', $user->teacher->id)
            ->with(['classroom.grade', 'subject'])
            ->get();

        $data = $assignments->groupBy('classroom.grade_id')->map(function ($group) {
            $grade = $group->first()->classroom->grade;

            return [
                'grade' => [
                    'id' => $grade?->id,
                    'name' => $grade?->name ?? 'Unknown Grade',
                ],
                'classrooms' => $group->groupBy('classroom_id')->map(function ($classroomGroup) {
                    $classroom = $classroomGroup->first()->classroom;

                    return [
                        'id' => $classroom->id,
                        'name' => $classroom->name,
                        'subjects' => $classroomGroup->map(fn($item) => [
                            'id' => $item->subject->id,
                            'name' => $item->subject->name,
                        ])->unique('id')->values()->toArray(),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'error' => null
        ]);
    }



    public function show($id)
    {
        $presentation = LessonPresentation::with(['slides', 'lessonPlanTemplate'])->findOrFail($id);

        $templates = \App\Models\CourseManagement\LessonPlanTemplate::query()
            ->where(function ($q) use ($presentation) {
                $q->whereNull('subject_id')
                    ->orWhere('subject_id', $presentation->subject_id);
            })
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'presentation' => $presentation,
            'templates' => $templates,
        ]);
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_id' => 'required|exists:grades,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quiz_id' => 'nullable|integer',
            'lesson_plan_template_id' => 'nullable|exists:lesson_plan_templates,id',
        ]);

        // Copy sections from active template if lesson_plan_template_id is not provided
        if (empty($validated['lesson_plan_template_id'])) {
            $activeTemplate = \App\Models\CourseManagement\LessonPlanTemplate::where('is_active', true)->first();
            if ($activeTemplate && isset($activeTemplate->structure['sections'])) {
                $validated['sections'] = $activeTemplate->structure['sections'];
            }
        }

        $presentation = LessonPresentation::create($validated);

        // If a lesson plan template is provided, apply it (create slides and store snapshot)
        if (!empty($validated['lesson_plan_template_id'])) {
            $this->applyTemplateToPresentation($presentation, $validated['lesson_plan_template_id']);
        } elseif (!empty($validated['sections'])) {
            // Add a default "Welcome" slide to the first section (usually Objectives)
            $presentation->slides()->create([
                'section' => $validated['sections'][0]['id'] ?? 'objectives',
                'slide_type' => 'text',
                'slide_content' => [
                    'title' => 'Welcome to ' . $validated['name'],
                    'content' => $validated['description'] ?? 'Lets get started!',
                ],
            ]);
        }

        // Assign to all students based on school, grade, subject
        // For efficiency, use the school_id from validated to restrict assigning students
        $students = \App\Models\Student::where('school_id', $validated['school_id'])
            ->where('grade_id', $validated['grade_id'])
            ->get();

        $progressData = $students->map(function ($student) {
            return [
                'student_id' => $student->id,
                'status' => 'locked',
                'color_status' => 'gray',
                'opened_by_teacher_id' => null,
                'opened_at' => null,
            ];
        })->toArray();

        // Use correct relationship and createMany for efficiency
        $presentation->studentProgress()->createMany($progressData);
        return response()->json($presentation->load('slides'), 201);
    }

    public function update(Request $request, $id)
    {
        $presentation = LessonPresentation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'sometimes|exists:grades,id',
            'quiz_id' => 'nullable|integer',
            'lesson_plan_template_id' => 'nullable|exists:lesson_plan_templates,id',
            'apply_template' => 'sometimes|boolean',
        ]);

        $presentation->update($validated);

        // Apply template if passed
        if ($request->has('lesson_plan_template_id') && $request->input('lesson_plan_template_id')) {
            $this->applyTemplateToPresentation($presentation, $request->input('lesson_plan_template_id'));
        }

        return response()->json($presentation->load('slides'));
    }

    /**
     * Create slides for a presentation based on a lesson plan template.
     * If the template has a sections array, use it; otherwise, use template's structure directly.
     */
    private function applyTemplateToPresentation(LessonPresentation $presentation, $templateId, $overwrite = true)
    {
        $template = LessonPlanTemplate::find($templateId);
        if (!$template) {
            return;
        }

        $structure = $template->structure ?? null;
        if (!$structure) {
            return;
        }

        // Optionally clear existing slides if overwrite is requested
        if ($overwrite) {
            foreach ($presentation->slides as $s) {
                $s->delete();
            }
        }

        $orderIndex = 1;

        $sections = $structure['sections'] ?? null;
        // If structure uses a flat array of fields, try to map them to sections
        if (is_array($sections)) {
            foreach ($sections as $section) {
                $sectionId = $section['id'] ?? ($section['section'] ?? null);
                $slidesCount = isset($section['slides']) ? intval($section['slides']) : 1;
                $defaultSlideType = $section['default_slide_type'] ?? 'text';
                $defaults = $section['defaults'] ?? [];

                for ($i = 0; $i < $slidesCount; $i++) {
                    $presentation->slides()->create([
                        'section' => $sectionId ?? 'learn',
                        'slide_type' => $defaultSlideType,
                        'slide_content' => $defaults,
                    ]);
                    $orderIndex++;
                }
            }
        }

        // Save snapshot and link on the presentation
        $presentation->update([
            'lesson_plan_template_id' => $template->id,
            'template_snapshot' => $template->toArray(),
            'is_template_applied' => true,
        ]);
    }

    public function destroy($id)
    {
        $presentation = LessonPresentation::findOrFail($id);
        $presentation->delete();
        return response()->json(null, 204);
    }

    public function addSlide(Request $request, $id)
    {
        $presentation = LessonPresentation::findOrFail($id);

        $validated = $request->validate([
            'slide_type' => 'required|string',
            'slide_content' => 'nullable|array', // Allow empty slide_content
            'section' => 'required|string',
            'order_index' => 'nullable|integer',
        ]);

        // Ensure slide_content is at least an empty array
        $validated['slide_content'] = $validated['slide_content'] ?? [];

        $content = $validated['slide_content'];
        if (isset($content['questions']) && is_array($content['questions'])) {
            foreach ($content['questions'] as &$question) {
                if (!isset($question['id'])) {
                    $question['id'] = 'q_' . rand(100, 999) . Str::random(3);
                }
            }
            $validated['slide_content'] = $content;
        }

        $slide = $presentation->slides()->create($validated);
        return response()->json($slide, 201);
    }

    public function updateSlide(Request $request, $id, $slideId)
    {
        $slide = LessonPresentationSlide::where('lesson_presentation_id', $id)->findOrFail($slideId);

        $validated = $request->validate([
            'slide_type' => 'sometimes|string',
            'slide_content' => 'nullable|array', // Allow empty slide_content
            'section' => 'sometimes|string',
            'order_index' => 'nullable|integer',
        ]);

        // Ensure slide_content is at least an empty array if provided
        if (isset($validated['slide_content'])) {
            $validated['slide_content'] = $validated['slide_content'] ?? [];
        }

        $content = $validated['slide_content'];
        if (isset($content['questions']) && is_array($content['questions'])) {
            foreach ($content['questions'] as &$question) {
                if (!isset($question['id'])) {
                    $question['id'] = 'q_' . rand(100, 999) . Str::random(3);
                }
            }
            $validated['slide_content'] = $content;
        }

        $slide->update($validated);
        return response()->json($slide);
    }

    public function deleteSlide($id, $slideId)
    {
        $slide = LessonPresentationSlide::where('lesson_presentation_id', $id)->findOrFail($slideId);
        $slide->delete();
        return response()->json(null, 204);
    }

    public function bulkUpdateSlides(Request $request, $id)
    {
        $presentation = LessonPresentation::findOrFail($id);

        $validated = $request->validate([
            'slides' => 'required|array',
            'slides.*.id' => 'nullable|integer|exists:lesson_presentation_slides,id,lesson_presentation_id,' . $id,
            'slides.*.slide_type' => 'required|string',
            'slides.*.slide_content' => 'nullable|array',
            'slides.*.section' => 'required|string',
            'slides.*.order_index' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $updatedSlides = [];
            $newSlides = [];

            foreach ($validated['slides'] as $slideData) {
                // Ensure slide_content is at least an empty array
                $slideData['slide_content'] = $slideData['slide_content'] ?? [];

                // Handle question ID generation for new questions
                $content = $slideData['slide_content'];
                if (isset($content['questions']) && is_array($content['questions'])) {
                    foreach ($content['questions'] as &$question) {
                        if (!isset($question['id'])) {
                            $question['id'] = 'q_' . rand(100, 999) . Str::random(3);
                        }
                    }
                    $slideData['slide_content'] = $content;
                }

                if (isset($slideData['id'])) {
                    // Update existing slide
                    $slide = LessonPresentationSlide::where('lesson_presentation_id', $id)
                        ->where('id', $slideData['id'])
                        ->firstOrFail();
                    $slide->update($slideData);
                    $updatedSlides[] = $slide;
                } else {
                    // Create new slide
                    $newSlide = $presentation->slides()->create($slideData);
                    $newSlides[] = $newSlide;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Slides updated successfully',
                'updated' => $updatedSlides,
                'created' => $newSlides
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to update slides: ' . $e->getMessage()], 500);
        }
    }

    public function proxyImage(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');

        try {
            $response = \Illuminate\Support\Facades\Http::get($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                $content = $response->body();
                $base64 = 'data:' . $contentType . ';base64,' . base64_encode($content);

                return response()->json(['base64' => $base64]);
            }

            return response()->json(['error' => 'Failed to fetch image'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Apply a template to an existing presentation: create slides and update snapshot.
     */
    public function applyTemplate(Request $request, $id)
    {
        $presentation = LessonPresentation::findOrFail($id);

        $validated = $request->validate([
            'lesson_plan_template_id' => 'required|exists:lesson_plan_templates,id',
            'overwrite' => 'sometimes|boolean',
        ]);

        $this->applyTemplateToPresentation($presentation, $validated['lesson_plan_template_id'], $validated['overwrite'] ?? true);

        return response()->json($presentation->load('slides'));
    }

    /**
     * Show teacher progress dashboard for a lesson
     */
    public function teacherProgressDashboard($lessonId)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            abort(403, 'Teacher access required');
        }

        return \Inertia\Inertia::render('my_table_mnger/lesson_presentation/TeacherProgressDashboard', [
            'lessonId' => (int) $lessonId,
            'teacherId' => $teacher->id
        ]);
    }

    /**
     * Show student lesson list
     */
    public function studentLessonList()
    {
        $user = auth()->user();

        if (!$user->teacher) {
            return response()->json(['error' => 'User is not a teacher'], 403);
        }

        $assignments = \App\Models\ClassroomSubjectTeacher::where('teacher_id', $user->teacher->id)
            ->with(['classroom.grade', 'subject'])
            ->get();

        $data = $assignments->groupBy('classroom.grade_id')->map(function ($group) {
            $grade = $group->first()->classroom->grade;

            return [
                'grade' => [
                    'id' => $grade?->id,
                    'name' => $grade?->name ?? 'Unknown Grade',
                ],
                'classrooms' => $group->groupBy('classroom_id')->map(function ($classroomGroup) {
                    $classroom = $classroomGroup->first()->classroom;

                    return [
                        'id' => $classroom->id,
                        'name' => $classroom->name,
                        'subjects' => $classroomGroup->map(fn($item) => [
                            'id' => $item->subject->id,
                            'name' => $item->subject->name,
                        ])->unique('id')->values()->toArray(),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'error' => null
        ]);
    }










    /**
     * Show student lesson list
     */
    public function studentLessonList2()
    {
        // Get student from auth (for now using first student as fallback)
        $student = \App\Models\Student::first(); // TODO: Replace with Auth::user()->student
        $grade = $student ? $student->grade : \App\Models\Grade::first();
        $subject = \App\Models\Subject::first();

        return \Inertia\Inertia::render('my_table_mnger/lesson_presentation/StudentLessonList', [
            'studentId' => $student ? $student->id : 1,
            'gradeId' => $grade ? $grade->id : 1,
            'subjectId' => $subject ? $subject->id : 1,
            'sections' => \App\Models\free\LessonPresentation::SECTIONS,
        ]);
    }
}
