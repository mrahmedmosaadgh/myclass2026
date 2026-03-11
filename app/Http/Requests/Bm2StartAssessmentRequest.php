<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Bm2StartAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Student can start assessments
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:placement,progress,final',
            'grade_level' => 'nullable|in:K,1,2',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Assessment type is required',
            'type.in' => 'Invalid assessment type. Must be placement, progress, or final',
            'grade_level.in' => 'Grade level must be K, 1, or 2',
        ];
    }
}
