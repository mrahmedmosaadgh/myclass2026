<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ClassroomSubjectTeacher;

class WeeklyPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user owns the CST (classroom subject teacher assignment)
        if ($this->has('cst_id')) {
            $cst = ClassroomSubjectTeacher::find($this->cst_id);
            return $cst && $cst->teacher_id === auth()->id();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_number' => 'required|in:1,2',
            'week_number' => 'required|integer|min:1|max:36',
            'cst_id' => 'required|exists:classroom_subject_teachers,id',
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
            'cst_id.exists' => 'The selected classroom subject teacher assignment does not exist.',
            'academic_year_id.exists' => 'The selected academic year does not exist.',
            'semester_number.in' => 'Semester must be either 1 or 2.',
            'week_number.min' => 'Week number must be at least 1.',
            'week_number.max' => 'Week number cannot exceed 36.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation: check if user owns the CST
            if ($this->has('cst_id')) {
                $cst = ClassroomSubjectTeacher::find($this->cst_id);
                if ($cst && $cst->teacher_id !== auth()->id()) {
                    $validator->errors()->add('cst_id', 'You are not authorized to manage this classroom subject assignment.');
                }
            }
        });
    }
}