<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'player_id' => ['sometimes','required','integer','exists:players,id'],
            'team_id' => ['sometimes','required','integer','exists:teams,id'],
            'minute' => ['sometimes','required','integer','between:1,180'],
            'own_goal' => ['sometimes','required','boolean'],
        ];
    }
}
