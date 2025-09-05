<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalStoreRequest;
use App\Http\Requests\GoalUpdateRequest;
use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(FootballMatch $match, Request $request)
    {
        $goals = $match->goals()->with(['player:id,name,team_id','team:id,name'])->orderBy('minute')->get();
        return response()->json(['status' => 'ok', 'data' => $goals]);
    }

    public function store(FootballMatch $match, GoalStoreRequest $request)
    {
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Finished match is immutable'], 422);
        }
        $data = $request->validated();
        $player = Player::find($data['player_id']);
        if (!$player || !in_array($player->team_id, [$match->home_team_id, $match->away_team_id], true)) {
            return response()->json(['status' => 'error', 'message' => 'Player must belong to one of the match teams'], 422);
        }
        if (!in_array($data['team_id'], [$match->home_team_id, $match->away_team_id], true) || $data['team_id'] !== $player->team_id) {
            return response()->json(['status' => 'error', 'message' => 'Goal team_id must match scorer team and be one of match teams'], 422);
        }
        $goal = $match->goals()->create($data);
        return response()->json(['status' => 'ok', 'data' => $goal], 201);
    }

    public function update(FootballMatch $match, Goal $goal, GoalUpdateRequest $request)
    {
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Finished match is immutable'], 422);
        }
        if ($goal->match_id !== $match->id) {
            return response()->json(['status' => 'error', 'message' => 'Goal does not belong to this match'], 404);
        }
        $data = $request->validated();
        if (isset($data['player_id'])) {
            $player = Player::find($data['player_id']);
            if (!$player || !in_array($player->team_id, [$match->home_team_id, $match->away_team_id], true)) {
                return response()->json(['status' => 'error', 'message' => 'Player must belong to one of the match teams'], 422);
            }
            if (isset($data['team_id']) && $data['team_id'] !== $player->team_id) {
                return response()->json(['status' => 'error', 'message' => 'team_id must match player team'], 422);
            }
            $data['team_id'] = $data['team_id'] ?? $player->team_id;
        } elseif (isset($data['team_id'])) {
            if (!in_array($data['team_id'], [$match->home_team_id, $match->away_team_id], true)) {
                return response()->json(['status' => 'error', 'message' => 'team_id must be one of the match teams'], 422);
            }
        }
        $goal->update($data);
        return response()->json(['status' => 'ok', 'data' => $goal]);
    }

    public function destroy(FootballMatch $match, Goal $goal)
    {
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Finished match is immutable'], 422);
        }
        if ($goal->match_id !== $match->id) {
            return response()->json(['status' => 'error', 'message' => 'Goal does not belong to this match'], 404);
        }
        $goal->delete();
        return response()->json(['status' => 'ok']);
    }

    public function restore(FootballMatch $match, $goal)
    {
        $goalModel = Goal::withTrashed()->where('match_id', $match->id)->findOrFail($goal);
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Finished match is immutable'], 422);
        }
        $goalModel->restore();
        return response()->json(['status' => 'ok', 'data' => $goalModel]);
    }
}
