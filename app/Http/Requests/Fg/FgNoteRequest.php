<?php

namespace App\Http\Requests\Fg;

use Illuminate\Foundation\Http\FormRequest;

class FgNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'domain_id' => 'nullable|uuid|exists:fg_domains,id',
            'body' => 'required|string',
            'source' => 'in:manual,ai_vent,quick_capture',
            'tags' => 'nullable|array',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['body'] = 'sometimes|required|string';
        }

        return $rules;
    }
}
