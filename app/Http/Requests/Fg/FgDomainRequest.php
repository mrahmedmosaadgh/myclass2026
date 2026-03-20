<?php

namespace App\Http\Requests\Fg;

use Illuminate\Foundation\Http\FormRequest;

class FgDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'emoji' => 'nullable|string|max:10',
            'color_hex' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
