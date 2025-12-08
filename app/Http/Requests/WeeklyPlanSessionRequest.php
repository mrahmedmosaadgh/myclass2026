<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\WeeklyPlan;
use App\Models\Teacher;

class WeeklyPlanSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the teacher ID from the authenticated user
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return false;
        }
        
        $teacherId = $teacher->id;

        // Check if user owns the weekly plan through CST
        if ($this->has('weekly_plan_id')) {
            $weeklyPlan = WeeklyPlan::with('classroomSubjectTeacher')->find($this->weekly_plan_id);
            return $weeklyPlan && $weeklyPlan->classroomSubjectTeacher->teacher_id === $teacherId;
        }

        // For existing session updates, check ownership through the session's weekly plan
        if ($this->route('session')) {
            $session = $this->route('session');
            $weeklyPlan = $session->weeklyPlan->load('classroomSubjectTeacher');
            return $weeklyPlan->classroomSubjectTeacher->teacher_id === $teacherId;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'weekly_plan_id' => 'required|exists:weekly_plans,id',
            'session_index' => 'required|integer|min:1',
            'period_code' => 'required|string|max:50|regex:/^\d+\.\d+\.\d+\.\d+$/',
            'type' => 'required|in:lesson,quiz,exam,extra,note',
            'title' => 'required|string|max:255',
            'data' => 'nullable|array',
            'data.zoom_link' => 'nullable|url',
            'data.homework' => 'nullable|string|max:1000',
            'data.skill_tags' => 'nullable|array',
            'data.skill_tags.*' => 'string|max:100',
            'data.materials' => 'nullable|array',
            'data.materials.*' => 'string|max:255',
            'data.duration_minutes' => 'nullable|integer|min:1|max:300',
            'data.difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
        ];

        // For updates, make fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return str_replace('required|', 'sometimes|', $rule);
            }, $rules);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'weekly_plan_id.exists' => 'The selected weekly plan does not exist.',
            'session_index.min' => 'Session index must be at least 1.',
            'period_code.regex' => 'Period code must be in format: year.semester.week.day (e.g., 25.1.2.3)',
            'type.in' => 'Session type must be one of: lesson, quiz, exam, extra, note.',
            'data.zoom_link.url' => 'Zoom link must be a valid URL.',
            'data.skill_tags.*.string' => 'Each skill tag must be a string.',
            'data.materials.*.string' => 'Each material must be a string.',
            'data.duration_minutes.min' => 'Duration must be at least 1 minute.',
            'data.duration_minutes.max' => 'Duration cannot exceed 300 minutes.',
            'data.difficulty_level.in' => 'Difficulty level must be beginner, intermediate, or advanced.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation: check session index uniqueness within the weekly plan
            if ($this->has('weekly_plan_id') && $this->has('session_index')) {
                $existingSession = \App\Models\WeeklyPlanSession::where('weekly_plan_id', $this->weekly_plan_id)
                    ->where('session_index', $this->session_index)
                    ->when($this->route('session'), function ($query) {
                        return $query->where('id', '!=', $this->route('session')->id);
                    })
                    ->first();

                if ($existingSession) {
                    $validator->errors()->add('session_index', 'A session with this index already exists in this weekly plan.');
                }
            }
        });
    }
}