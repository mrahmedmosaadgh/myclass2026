<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
            ],
            'selected_option_id' => [
                'nullable',
                'integer',
                'exists:question_options,id',
                Rule::requiredIf(function () {
                    return empty($this->input('selected_text'));
                }),
            ],
            'selected_text' => [
                'nullable',
                'string',
                'max:5000',
                Rule::requiredIf(function () {
                    return empty($this->input('selected_option_id'));
                }),
            ],
            'time_spent_sec' => [
                'nullable',
                'integer',
                'min:0',
                'max:86400', // Max 24 hours
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
            'question_id.required' => 'Question ID is required.',
            'question_id.integer' => 'Question ID must be an integer.',
            'question_id.exists' => 'The specified question does not exist.',
            'selected_option_id.integer' => 'Selected option ID must be an integer.',
            'selected_option_id.exists' => 'The specified option does not exist.',
            'selected_option_id.required_if' => 'Either an option or text answer must be provided.',
            'selected_text.string' => 'Answer text must be a string.',
            'selected_text.max' => 'Answer text cannot exceed 5000 characters.',
            'selected_text.required_if' => 'Either an option or text answer must be provided.',
            'time_spent_sec.integer' => 'Time spent must be an integer.',
            'time_spent_sec.min' => 'Time spent cannot be negative.',
            'time_spent_sec.max' => 'Time spent cannot exceed 24 hours.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'question_id' => 'question',
            'selected_option_id' => 'selected option',
            'selected_text' => 'answer text',
            'time_spent_sec' => 'time spent',
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
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The provided answer data is invalid.',
                    'details' => $validator->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
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
            // Ensure at least one answer type is provided
            if (empty($this->input('selected_option_id')) && empty($this->input('selected_text'))) {
                $validator->errors()->add(
                    'answer',
                    'Either a selected option or text answer must be provided.'
                );
            }

            // If option is provided, verify it belongs to the question
            if ($this->input('selected_option_id') && $this->input('question_id')) {
                $optionBelongsToQuestion = \App\Models\QuestionOption::where('id', $this->input('selected_option_id'))
                    ->where('question_id', $this->input('question_id'))
                    ->exists();

                if (!$optionBelongsToQuestion) {
                    $validator->errors()->add(
                        'selected_option_id',
                        'The selected option does not belong to the specified question.'
                    );
                }
            }
        });
    }
}
