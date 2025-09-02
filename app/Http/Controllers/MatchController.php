<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatchStoreRequest;
use App\Http\Requests\ResultFinalizeRequest;
use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $perPageInput = (int) $request->query('per_page', 15);
        $perPage = in_array($perPageInput, [15, 25, 50, 100], true) ? $perPageInput : 15;

        $query = FootballMatch::query()
            ->with(['homeTeam','awayTeam'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('sort'), function ($q) use ($request) {
                $sort = $request->string('sort');
                $dir = 'asc';
                $col = $sort;
                if (str_starts_with($sort, '-')) { $dir = 'desc'; $col = substr($sort, 1); }
                $q->orderBy($col, $dir);
            }, fn($q) => $q->orderBy('start_time','desc'));

        $matches = $query->paginate($perPage);
        return response()->json([
            'status' => 'ok',
            'data' => $matches->items(),
            'meta' => [
                'current_page' => $matches->currentPage(),
                'per_page' => $matches->perPage(),
                'total' => $matches->total(),
                'last_page' => $matches->lastPage(),
            ],
        ]);
    }

    public function store(MatchStoreRequest $request)
    {
        $data = $request->validated();
        // Ensure different teams (already validated) and teams exist
        $match = FootballMatch::create([
            'home_team_id' => $data['home_team_id'],
            'away_team_id' => $data['away_team_id'],
            'start_time' => $data['start_time'],
            'status' => 'scheduled',
        ]);
        return response()->json(['status' => 'ok', 'data' => $match], 201);
    }

    public function show(FootballMatch $match)
    {
        $match->load(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }]);
        return response()->json(['status' => 'ok', 'data' => $match]);
    }

    public function update(MatchStoreRequest $request, FootballMatch $match)
    {
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Finished match is immutable'], 422);
        }
        $data = $request->validated();
        $match->update([
            'home_team_id' => $data['home_team_id'],
            'away_team_id' => $data['away_team_id'],
            'start_time' => $data['start_time'],
        ]);
        return response()->json(['status' => 'ok', 'data' => $match]);
    }

    public function finalize(ResultFinalizeRequest $request, FootballMatch $match)
    {
        if ($match->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Match already finalized'], 422);
        }

        $data = $request->validated();
        $homeId = $match->home_team_id; $awayId = $match->away_team_id;

        // Validate that each goal belongs to a player from either team and team_id matches
        foreach ($data['goals'] as $g) {
            $player = Player::find($g['player_id']);
            if (!$player || !in_array($player->team_id, [$homeId, $awayId], true)) {
                return response()->json(['status' => 'error', 'message' => 'Goal scorer must belong to one of the match teams'], 422);
            }
            if (!in_array($g['team_id'], [$homeId, $awayId], true) || $g['team_id'] !== $player->team_id) {
                return response()->json(['status' => 'error', 'message' => 'Goal team_id must match scorer team and be one of match teams'], 422);
            }
        }

        DB::transaction(function () use ($match, $data) {
            // Clear existing goals if any (in case of re-finalization attempt before status lock)
            $match->goals()->delete();

            foreach ($data['goals'] as $g) {
                Goal::create([
                    'match_id' => $match->id,
                    'player_id' => $g['player_id'],
                    'team_id' => $g['team_id'],
                    'minute' => $g['minute'],
                    'own_goal' => $g['own_goal'],
                ]);
            }

            $match->update([
                'home_score' => $data['home_score'],
                'away_score' => $data['away_score'],
                'status' => 'finished',
                'finished_at' => now(),
            ]);
        });

        $match->load('goals');
        return response()->json(['status' => 'ok', 'data' => $match]);
    }
}
