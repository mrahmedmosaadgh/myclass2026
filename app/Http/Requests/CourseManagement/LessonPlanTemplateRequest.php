<?php

namespace App\Http\Requests\CourseManagement;

use Illuminate\Foundation\Http\FormRequest;

class LessonPlanTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'structure' => 'required|array|min:1',
            'structure.*.label' => 'required|string|max:255',
            'structure.*.type' => 'required|string|in:text,textarea,select',
        ];

        // For update requests, make some fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = 'sometimes|required|string|max:255';
            $rules['structure'] = 'sometimes|required|array|min:1';
            $rules['is_active'] = 'sometimes|boolean';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The template name is required.',
            'name.max' => 'The template name must not exceed 255 characters.',
            'structure.required' => 'At least one field is required.',
            'structure.array' => 'The structure must be an array.',
            'structure.min' => 'At least one field is required.',
            'structure.*.label.required' => 'Each field must have a label.',
            'structure.*.label.max' => 'Each field label must not exceed 255 characters.',
            'structure.*.type.required' => 'Each field must have a type.',
            'structure.*.type.in' => 'Each field type must be one of: text, textarea, or select.',
        ];
    }
}