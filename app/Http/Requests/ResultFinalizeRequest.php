<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResultFinalizeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'home_score' => ['required','integer','min:0'],
            'away_score' => ['required','integer','min:0'],
            'goals' => ['required','array','min:0'],
            'goals.*.player_id' => ['required','integer','exists:players,id'],
            'goals.*.team_id' => ['required','integer','exists:teams,id'],
            'goals.*.minute' => ['required','integer','between:1,120'],
            'goals.*.own_goal' => ['required','boolean'],
        ];
    }
}
