<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTextRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'text' => 'required|string|min:1|max:1000',
            'model' => 'sometimes|string|in:text-generator,text-generator-fast',
            'max_tokens' => 'sometimes|integer|min:10|max:512'
        ];
    }
}