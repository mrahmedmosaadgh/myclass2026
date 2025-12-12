<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportQuestionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to import questions
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
            'file' => [
                'required',
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:10240', // 10MB max
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
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be a CSV or Excel file (csv, txt, xlsx, xls).',
            'file.max' => 'The file size cannot exceed 10MB.',
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
            'file' => 'import file',
        ];
    }
}
