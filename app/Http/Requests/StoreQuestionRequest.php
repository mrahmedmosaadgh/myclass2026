<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\QuestionType;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to create questions
        return $this->user() && $this->user()->hasRole(['teacher', 'admin', 'super-admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_type_id' => [
                'required',
                'integer',
                Rule::exists('question_types', 'id'),
            ],
            'question_text' => [
                'required',
                'string',
                'max:5000',
                'min:10',
            ],
            'grade_id' => [
                'required',
                'integer',
                Rule::exists('grades', 'id'),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
            ],
            'topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id'),
            ],
            'bloom_level' => [
                'nullable',
                'integer',
                'between:1,6',
            ],
            'difficulty_level' => [
                'nullable',
                'integer',
                'between:1,5',
            ],
            'estimated_time_sec' => [
                'nullable',
                'integer',
                'min:1',
                'max:3600',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['draft', 'active', 'archived', 'review']),
            ],
            'hints' => [
                'nullable',
                'array',
                'max:5',
            ],
            'hints.*' => [
                'string',
                'max:1000',
                'min:5',
            ],
            'explanation' => [
                'nullable',
                'array',
            ],
            'explanation.text' => [
                'nullable',
                'string',
                'max:5000',
                'min:10',
            ],
            'explanation.revealed_after_attempt' => [
                'nullable',
                'boolean',
            ],
            'options' => [
                'nullable',
                'array',
                'min:2',
                'max:10',
            ],
            'options.*.option_key' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Z]$/',
            ],
            'options.*.option_text' => [
                'required',
                'string',
                'max:1000',
                'min:1',
            ],
            'options.*.is_correct' => [
                'required',
                'boolean',
            ],
            'options.*.distractor_strength' => [
                'nullable',
                'numeric',
                'between:0,1',
            ],
            'options.*.order_index' => [
                'required',
                'integer',
                'min:0',
            ],
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
            'question_type_id.required' => 'Please select a question type.',
            'question_type_id.exists' => 'The selected question type is invalid.',
            'question_text.required' => 'Question text is required.',
            'question_text.min' => 'Question text must be at least 10 characters.',
            'question_text.max' => 'Question text cannot exceed 5000 characters.',
            'grade_id.required' => 'Please select a grade level.',
            'grade_id.exists' => 'The selected grade level is invalid.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'topic_id.exists' => 'The selected topic is invalid.',
            'bloom_level.between' => 'Bloom level must be between 1 and 6.',
            'difficulty_level.between' => 'Difficulty level must be between 1 and 5.',
            'estimated_time_sec.min' => 'Estimated time must be at least 1 second.',
            'estimated_time_sec.max' => 'Estimated time cannot exceed 1 hour (3600 seconds).',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
            'hints.max' => 'You can provide a maximum of 5 hints.',
            'hints.*.min' => 'Each hint must be at least 5 characters.',
            'hints.*.max' => 'Each hint cannot exceed 1000 characters.',
            'explanation.text.min' => 'Explanation must be at least 10 characters.',
            'explanation.text.max' => 'Explanation cannot exceed 5000 characters.',
            'options.min' => 'At least 2 options are required.',
            'options.max' => 'Maximum 10 options allowed.',
            'options.*.option_key.regex' => 'Option key must be a single uppercase letter (A-Z).',
            'options.*.option_text.required' => 'Option text is required.',
            'options.*.option_text.min' => 'Option text must be at least 1 character.',
            'options.*.option_text.max' => 'Option text cannot exceed 1000 characters.',
            'options.*.is_correct.required' => 'Please specify if this option is correct.',
            'options.*.distractor_strength.between' => 'Distractor strength must be between 0 and 1.',
        ];
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
            // Validate question type supports options
            if ($this->has('question_type_id') && $this->has('options')) {
                $questionType = QuestionType::find($this->question_type_id);
                
                if ($questionType && !$questionType->has_options) {
                    $validator->errors()->add(
                        'options',
                        'This question type does not support options.'
                    );
                }
            }

            // Validate question type requires options
            if ($this->has('question_type_id') && !$this->has('options')) {
                $questionType = QuestionType::find($this->question_type_id);
                
                if ($questionType && $questionType->has_options) {
                    $validator->errors()->add(
                        'options',
                        'This question type requires options.'
                    );
                }
            }

            // Validate at least one correct option
            if ($this->has('options')) {
                $hasCorrectOption = collect($this->options)->contains('is_correct', true);
                
                if (!$hasCorrectOption) {
                    $validator->errors()->add(
                        'options',
                        'At least one option must be marked as correct.'
                    );
                }
            }

            // Validate unique option keys
            if ($this->has('options')) {
                $optionKeys = collect($this->options)->pluck('option_key');
                $duplicates = $optionKeys->duplicates();
                
                if ($duplicates->isNotEmpty()) {
                    $validator->errors()->add(
                        'options',
                        'Option keys must be unique. Duplicate keys found: ' . $duplicates->implode(', ')
                    );
                }
            }

            // Validate hints if question type supports them
            if ($this->has('hints') && $this->has('question_type_id')) {
                $questionType = QuestionType::find($this->question_type_id);
                
                if ($questionType && !$questionType->supports_hints) {
                    $validator->errors()->add(
                        'hints',
                        'This question type does not support hints.'
                    );
                }
            }

            // Validate explanation if question type supports it
            if ($this->has('explanation') && $this->has('question_type_id')) {
                $questionType = QuestionType::find($this->question_type_id);
                
                if ($questionType && !$questionType->supports_explanation) {
                    $validator->errors()->add(
                        'explanation',
                        'This question type does not support explanations.'
                    );
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'question_type_id' => 'question type',
            'question_text' => 'question text',
            'grade_id' => 'grade level',
            'subject_id' => 'subject',
            'topic_id' => 'topic',
            'bloom_level' => 'Bloom level',
            'difficulty_level' => 'difficulty level',
            'estimated_time_sec' => 'estimated time',
            'options.*.option_key' => 'option key',
            'options.*.option_text' => 'option text',
            'options.*.is_correct' => 'correct flag',
            'options.*.distractor_strength' => 'distractor strength',
        ];
    }
}
