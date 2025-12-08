<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FetchQuizRequest extends FormRequest
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
                'sometimes',
                'array',
                'max:100',
            ],
            'question_ids.*' => [
                'integer',
                'exists:questions,id',
            ],
            'grade_level_id' => [
                'sometimes',
                'integer',
                'exists:grades,id',
            ],
            'subject_id' => [
                'sometimes',
                'integer',
                'exists:subjects,id',
            ],
            'topic_id' => [
                'sometimes',
                'integer',
                'exists:topics,id',
            ],
            'difficulty_level' => [
                'sometimes',
                'integer',
                'min:1',
                'max:5',
            ],
            'bloom_level' => [
                'sometimes',
                'integer',
                'min:1',
                'max:6',
            ],
            'status' => [
                'sometimes',
                'string',
                'in:draft,active,archived,review',
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
            'shuffle' => [
                'sometimes',
                'boolean',
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
            'question_ids.array' => 'Question IDs must be provided as an array.',
            'question_ids.max' => 'Cannot fetch more than 100 questions at once.',
            'question_ids.*.integer' => 'Each question ID must be an integer.',
            'question_ids.*.exists' => 'One or more questions do not exist.',
            'grade_level_id.integer' => 'Grade level ID must be an integer.',
            'grade_level_id.exists' => 'The specified grade level does not exist.',
            'subject_id.integer' => 'Subject ID must be an integer.',
            'subject_id.exists' => 'The specified subject does not exist.',
            'topic_id.integer' => 'Topic ID must be an integer.',
            'topic_id.exists' => 'The specified topic does not exist.',
            'difficulty_level.integer' => 'Difficulty level must be an integer.',
            'difficulty_level.min' => 'Difficulty level must be between 1 and 5.',
            'difficulty_level.max' => 'Difficulty level must be between 1 and 5.',
            'bloom_level.integer' => 'Bloom level must be an integer.',
            'bloom_level.min' => 'Bloom level must be between 1 and 6.',
            'bloom_level.max' => 'Bloom level must be between 1 and 6.',
            'status.in' => 'Status must be one of: draft, active, archived, review.',
            'limit.integer' => 'Limit must be an integer.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 100.',
            'shuffle.boolean' => 'Shuffle must be true or false.',
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
            'grade_level_id' => 'grade level',
            'subject_id' => 'subject',
            'topic_id' => 'topic',
            'difficulty_level' => 'difficulty',
            'bloom_level' => 'Bloom level',
            'status' => 'question status',
            'limit' => 'result limit',
            'shuffle' => 'shuffle option',
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
                    'message' => 'The provided quiz parameters are invalid.',
                    'details' => $validator->errors(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 422)
        );
    }
}
