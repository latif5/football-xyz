<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string','max:255', Rule::unique('teams','name')->ignore($this->route('team'))],
            'logo' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'founded_year' => ['nullable','integer','between:1850,'.date('Y')],
            'stadium_address' => ['nullable','string','max:255'],
            'city' => ['nullable','string','max:120'],
        ];
    }
}
