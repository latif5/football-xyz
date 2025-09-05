<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255','unique:teams,name'],
            'logo' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'founded_year' => ['nullable','integer','between:1850,'.date('Y')],
            'stadium_address' => ['nullable','string','max:255'],
            'city' => ['nullable','string','max:120'],
        ];
    }
}
