<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Bm2SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Student can submit answers
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:bm2_questions_bank,id',
            'student_answer' => 'required|string',
            'time_taken_seconds' => 'nullable|integer|min:0|max:3600',
            'hints_used' => 'nullable|integer|min:0|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question_id.required' => 'Question ID is required',
            'question_id.exists' => 'Invalid question',
            'student_answer.required' => 'Please provide an answer',
            'time_taken_seconds.min' => 'Time taken cannot be negative',
            'time_taken_seconds.max' => 'Time taken seems too long (max 1 hour)',
            'hints_used.min' => 'Hints used cannot be negative',
            'hints_used.max' => 'Too many hints used',
        ];
    }
}
