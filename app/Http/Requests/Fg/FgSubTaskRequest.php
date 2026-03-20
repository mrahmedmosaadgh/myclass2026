<?php

namespace App\Http\Requests\Fg;

use Illuminate\Foundation\Http\FormRequest;

class FgSubTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'task_id' => 'required|uuid|exists:fg_tasks,id',
            'title' => 'required|string|max:255',
            'is_done' => 'boolean',
            'sort_order' => 'integer',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['task_id'] = 'sometimes|required|uuid|exists:fg_tasks,id';
            $rules['title'] = 'sometimes|required|string|max:255';
        }

        return $rules;
    }
}
