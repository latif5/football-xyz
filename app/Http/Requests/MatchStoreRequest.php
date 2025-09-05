<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'home_team_id' => ['required','integer','exists:teams,id','different:away_team_id'],
            'away_team_id' => ['required','integer','exists:teams,id','different:home_team_id'],
            'start_time' => ['required','date'],
        ];
    }
}
