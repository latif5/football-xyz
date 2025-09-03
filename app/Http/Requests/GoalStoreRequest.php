<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'player_id' => ['required','integer','exists:players,id'],
            'team_id' => ['required','integer','exists:teams,id'],
            'minute' => ['required','integer','between:1,180'],
            'own_goal' => ['required','boolean'],
        ];
    }
}
