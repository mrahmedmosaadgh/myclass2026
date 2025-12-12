<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StartQuizAttemptRequest extends FormRequest
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
            'question_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'question_ids.*' => [
                'required',
                'integer',
                'exists:questions,id',
            ],
            'quiz_id' => [
                'nullable',
                'integer',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
            'metadata.*' => [
                'nullable',
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
            'question_ids.required' => 'At least one question is required to start a quiz.',
            'question_ids.array' => 'Question IDs must be provided as an array.',
            'question_ids.min' => 'At least one question is required to start a quiz.',
            'question_ids.max' => 'A quiz cannot have more than 100 questions.',
            'question_ids.*.required' => 'Each question ID is required.',
            'question_ids.*.integer' => 'Question IDs must be integers.',
            'question_ids.*.exists' => 'One or more questions do not exist.',
            'quiz_id.integer' => 'Quiz ID must be an integer.',
            'metadata.array' => 'Metadata must be an object.',
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
            'question_ids' => 'questions',
            'question_ids.*' => 'question',
            'quiz_id' => 'quiz',
            'metadata' => 'additional data',
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
                    'message' => 'The provided data is invalid.',
                    'details' => $validator->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422)
        );
    }
}
