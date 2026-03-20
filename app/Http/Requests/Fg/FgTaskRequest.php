<?php

namespace App\Http\Requests\Fg;

use Illuminate\Foundation\Http\FormRequest;

class FgTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'domain_id' => 'nullable|uuid|exists:fg_domains,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'importance' => 'integer|min:0|max:10',
            'status' => 'in:inbox,active,done,cancelled',
            'source' => 'in:manual,ai_vent,quick_capture',
            'is_today' => 'boolean',
            'sort_order' => 'integer',
            'tags' => 'nullable|array',
            'due_date' => 'nullable|date',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['title'] = 'sometimes|required|string|max:255';
        }

        return $rules;
    }
}
