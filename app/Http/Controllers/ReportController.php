<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function matchReport(FootballMatch $match)
    {
        $match->load(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }]);
        $data = [
            'id' => $match->id,
            'status' => $match->status,
            'start_time' => $match->start_time,
            'home_team' => $match->homeTeam,
            'away_team' => $match->awayTeam,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'goals' => $match->goals,
        ];
        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    public function topScorers(Request $request)
    {
        $limit = (int) $request->integer('limit', 10);
        $rows = Goal::query()
            ->select('player_id', DB::raw('count(*) as goals'))
            ->groupBy('player_id')
            ->orderByDesc('goals')
            ->limit($limit)
            ->get();
        $players = Player::whereIn('id', $rows->pluck('player_id'))->get()->keyBy('id');
        $result = $rows->map(fn($r) => [
            'player' => $players[$r->player_id] ?? null,
            'goals' => (int) $r->goals,
        ]);
        return response()->json(['status' => 'ok', 'data' => $result]);
    }

    public function teamWins(Request $request)
    {
        $limit = (int) $request->integer('limit', 10);
        $wins = FootballMatch::query()
            ->select(DB::raw("CASE WHEN home_score > away_score THEN home_team_id WHEN away_score > home_score THEN away_team_id END as team_id"), DB::raw('count(*) as wins'))
            ->where('status', 'finished')
            ->whereRaw('(home_score <> away_score)')
            ->groupBy('team_id')
            ->orderByDesc('wins')
            ->limit($limit)
            ->get();
        $teams = Team::whereIn('id', $wins->pluck('team_id'))->get()->keyBy('id');
        $result = $wins->map(fn($w) => [
            'team' => $teams[$w->team_id] ?? null,
            'wins' => (int) $w->wins,
        ]);
        return response()->json(['status' => 'ok', 'data' => $result]);
    }
}
