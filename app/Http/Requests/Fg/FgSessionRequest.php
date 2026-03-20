<?php

namespace App\Http\Requests\Fg;

use Illuminate\Foundation\Http\FormRequest;

class FgSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'task_id' => 'required|uuid|exists:fg_tasks,id',
            'intention' => 'nullable|string|max:255',
            'energy_level' => 'nullable|in:high,medium,low',
            'status' => 'in:active,completed,drifted',
            'check_in_answer' => 'nullable|in:on_track,drifted,done',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['task_id'] = 'sometimes|required|uuid|exists:fg_tasks,id';
        }

        return $rules;
    }
}
