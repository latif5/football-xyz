<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayerUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string','max:255'],
            'height' => ['nullable','integer','between:120,250'],
            'weight' => ['nullable','integer','between:30,200'],
            'position' => ['sometimes','required','in:forward,midfielder,defender,goalkeeper'],
            'shirt_number' => ['sometimes','required','integer','between:1,99'],
        ];
    }
}
