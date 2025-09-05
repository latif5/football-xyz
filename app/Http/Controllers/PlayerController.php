<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerStoreRequest;
use App\Http\Requests\PlayerUpdateRequest;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    public function index(Team $team, Request $request)
    {
        $perPageInput = (int) $request->query('per_page', 15);
        $perPage = in_array($perPageInput, [15, 25, 50, 100], true) ? $perPageInput : 15;

        $players = $team->players()
            ->orderBy('shirt_number')
            ->paginate($perPage);
        return response()->json([
            'status' => 'ok',
            'data' => $players->items(),
            'meta' => [
                'current_page' => $players->currentPage(),
                'per_page' => $players->perPage(),
                'total' => $players->total(),
                'last_page' => $players->lastPage(),
            ],
        ]);
    }

    public function store(Team $team, PlayerStoreRequest $request)
    {
        $data = $request->validated();
        if (Player::where('team_id', $team->id)->where('shirt_number', $data['shirt_number'])->exists()) {
            return response()->json(['status' => 'error', 'errors' => ['shirt_number' => ['Shirt number must be unique within a team.']]], 422);
        }
        $data['team_id'] = $team->id;
        $player = Player::create($data);
        return response()->json(['status' => 'ok', 'data' => $player], 201);
    }

    public function update(Team $team, Player $player, PlayerUpdateRequest $request)
    {
        if ($player->team_id !== $team->id) {
            return response()->json(['status' => 'error', 'message' => 'Player does not belong to the team'], 404);
        }
        $data = $request->validated();
        if (isset($data['shirt_number'])) {
            $exists = Player::where('team_id', $team->id)
                ->where('shirt_number', $data['shirt_number'])
                ->where('id', '!=', $player->id)
                ->exists();
            if ($exists) {
                return response()->json(['status' => 'error', 'errors' => ['shirt_number' => ['Shirt number must be unique within a team.']]], 422);
            }
        }
        $player->update($data);
        return response()->json(['status' => 'ok', 'data' => $player]);
    }

    public function destroy(Team $team, Player $player)
    {
        if ($player->team_id !== $team->id) {
            return response()->json(['status' => 'error', 'message' => 'Player does not belong to the team'], 404);
        }
        $player->delete();
        return response()->json(['status' => 'ok']);
    }

    public function restore(Team $team, $player)
    {
        $restored = Player::withTrashed()->where('team_id', $team->id)->findOrFail($player);
        $restored->restore();
        return response()->json(['status' => 'ok', 'data' => $restored]);
    }
}
