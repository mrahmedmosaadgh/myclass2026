<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PresentationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $presentation = $this->route('presentation');
        $user = auth()->user();

        // User can update their own presentations or school admins can update any
        return $user && ($presentation->user_id === $user->id || $user->hasRole('school_admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:cr_presentation_categories,id',
            'cr_presentation_category_id' => 'nullable|exists:cr_presentation_categories,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'slides' => 'nullable|array',
            'slides.*.id' => 'required|string|max:255',
            'slides.*.elements' => 'nullable|array',
            'slides.*.elements.*.id' => 'required|string|max:255',
            'slides.*.elements.*.type' => 'required|string|in:text,image,shape,video,audio,live_question,interactive',
            'current_slide_index' => 'nullable|integer|min:0',
            'use_phases' => 'nullable|boolean',
            'has_initialized_phases' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,archived',
            'is_public' => 'nullable|boolean',
            'is_template' => 'nullable|boolean',
            'create_backup' => 'nullable|boolean',
            'backup_reason' => 'nullable|string|max:255'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Presentation title is required',
            'title.max' => 'Presentation title cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
            'category_id.exists' => 'Selected category does not exist',
            'cr_presentation_category_id.exists' => 'Selected category does not exist',
            'classroom_id.exists' => 'Selected classroom does not exist',
            'slides.*.id.required' => 'Each slide must have an ID',
            'slides.*.elements.*.id.required' => 'Each element must have an ID',
            'slides.*.elements.*.type.required' => 'Each element must have a type',
            'slides.*.elements.*.type.in' => 'Invalid element type',
            'current_slide_index.min' => 'Current slide index must be at least 0',
            'status.in' => 'Invalid status value',
            'backup_reason.max' => 'Backup reason cannot exceed 255 characters'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate slides structure
            if ($this->has('slides') && is_array($this->slides)) {
                foreach ($this->slides as $index => $slide) {
                    if (!isset($slide['id']) || empty($slide['id'])) {
                        $validator->errors()->add("slides.{$index}.id", 'Slide ID is required');
                    }
                    
                    if (isset($slide['elements']) && is_array($slide['elements'])) {
                        foreach ($slide['elements'] as $elementIndex => $element) {
                            if (!isset($element['type']) || empty($element['type'])) {
                                $validator->errors()->add("slides.{$index}.elements.{$elementIndex}.type", 'Element type is required');
                            }
                        }
                    }
                }
            }

            // Validate current slide index
            if ($this->has('current_slide_index') && $this->has('slides')) {
                $slideCount = count($this->slides);
                if ($slideCount > 0 && $this->current_slide_index >= $slideCount) {
                    $validator->errors()->add('current_slide_index', 'Current slide index cannot exceed total slides count');
                }
            }

            // Validate classroom access
            if ($this->has('classroom_id') && $this->classroom_id) {
                $user = auth()->user();
                if ($user && $user->school_id) {
                    $classroom = \App\Models\Classroom::find($this->classroom_id);
                    if ($classroom && $classroom->school_id !== $user->school_id) {
                        $validator->errors()->add('classroom_id', 'You can only select classrooms from your school');
                    }
                }
            }

            // Validate backup reason
            if ($this->has('create_backup') && $this->create_backup && !$this->has('backup_reason')) {
                $validator->errors()->add('backup_reason', 'Backup reason is required when creating a backup');
            }
        });
    }
}
