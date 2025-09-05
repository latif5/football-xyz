<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Support\ReportUtils;

class ReportController extends Controller
{
    public function matchReport(FootballMatch $match)
    {
        $cacheKey = "match:{$match->id}:report";
        $tags = ["match:{$match->id}", "team:{$match->home_team_id}", "team:{$match->away_team_id}"];
        $ttl = now()->addMinutes(10);
        $cache = Cache::store();
        if (method_exists($cache, 'supportsTags') && $cache->supportsTags()) {
            $cache = Cache::tags($tags);
        }
        $data = $cache->remember($cacheKey, $ttl, function () use ($match) {
            $match->load(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }, 'goals.player', 'goals.team']);
            $summary = ReportUtils::buildMatchSummary($match);
            $homeLogoDataUri = ReportUtils::getTeamLogoDataUri($match->homeTeam, '1f77b4');
            $awayLogoDataUri = ReportUtils::getTeamLogoDataUri($match->awayTeam, 'ff7f0e');
            return [
                'id' => $match->id,
                'status' => $match->status,
                'start_time' => $match->start_time,
                'home_team' => $match->homeTeam,
                'away_team' => $match->awayTeam,
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
                'goals' => $match->goals,
                'goal_timeline' => $summary['goalRows'],
                'final_status' => $summary['finalStatus'],
                'top_scorer' => $summary['topScorer'],
                'home_wins_upto' => $summary['homeWinsUpTo'],
                'away_wins_upto' => $summary['awayWinsUpTo'],
                'home_logo_data_uri' => $homeLogoDataUri,
                'away_logo_data_uri' => $awayLogoDataUri,
            ];
        });
        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    public function matchReportPdf(Request $request, FootballMatch $match)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF generator not installed. Please run: composer require barryvdh/laravel-dompdf',
            ], 501);
        }

        $cacheKey = "match:{$match->id}:pdfdata";
        $tags = ["match:{$match->id}", "team:{$match->home_team_id}", "team:{$match->away_team_id}"];
        $ttl = now()->addMinutes(10);
        $cache = Cache::store();
        if (method_exists($cache, 'supportsTags') && $cache->supportsTags()) {
            $cache = Cache::tags($tags);
        }
        $payload = $cache->remember($cacheKey, $ttl, function () use ($match) {
            $match->load(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }, 'goals.player', 'goals.team']);
            $summary = ReportUtils::buildMatchSummary($match);
            return [
                'goalRows' => $summary['goalRows'],
                'finalStatus' => $summary['finalStatus'],
                'topScorer' => $summary['topScorer'],
                'homeWinsUpTo' => $summary['homeWinsUpTo'],
                'awayWinsUpTo' => $summary['awayWinsUpTo'],
                'homeLogoDataUri' => ReportUtils::getTeamLogoDataUri($match->homeTeam, '1f77b4'),
                'awayLogoDataUri' => ReportUtils::getTeamLogoDataUri($match->awayTeam, 'ff7f0e'),
            ];
        });
        $goalRows = $payload['goalRows'];
        $finalStatus = $payload['finalStatus'];
        $topScorer = $payload['topScorer'];
        $homeWinsUpTo = $payload['homeWinsUpTo'];
        $awayWinsUpTo = $payload['awayWinsUpTo'];
        $homeLogoDataUri = $payload['homeLogoDataUri'];
        $awayLogoDataUri = $payload['awayLogoDataUri'];

        $viewData = [
            'match' => $match,
            'homeTeam' => $match->homeTeam,
            'awayTeam' => $match->awayTeam,
            'goalRows' => $goalRows,
            'homeLogoDataUri' => $homeLogoDataUri,
            'awayLogoDataUri' => $awayLogoDataUri,
            'finalStatus' => $finalStatus,
            'topScorer' => $topScorer,
            'homeWinsUpTo' => (int) $homeWinsUpTo,
            'awayWinsUpTo' => (int) $awayWinsUpTo,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.match', $viewData)
            ->setPaper('a4');
        $filename = sprintf('match-%d-%s-vs-%s.pdf', $match->id, str_replace(' ', '-', strtolower($match->homeTeam->name)), str_replace(' ', '-', strtolower($match->awayTeam->name)));
        return $pdf->download($filename);
    }

    public function topScorers(Request $request)
    {
        $limit = (int) $request->integer('limit', 10);
        $cacheKey = "leaderboard:top_scorers:{$limit}";
        $cache = Cache::store();
        if (method_exists($cache, 'supportsTags') && $cache->supportsTags()) {
            $cache = Cache::tags(['leaderboard:top_scorers']);
        }
        $result = $cache->remember($cacheKey, now()->addMinutes(5), function () use ($limit) {
            $rows = Goal::query()
                ->select('player_id', DB::raw('count(*) as goals'))
                ->groupBy('player_id')
                ->orderByDesc('goals')
                ->limit($limit)
                ->get();
            $players = Player::whereIn('id', $rows->pluck('player_id'))->get()->keyBy('id');
            return $rows->map(fn($r) => [
                'player' => $players[$r->player_id] ?? null,
                'goals' => (int) $r->goals,
            ]);
        });
        return response()->json(['status' => 'ok', 'data' => $result]);
    }

    public function teamWins(Request $request)
    {
        $limit = (int) $request->integer('limit', 10);
        $cacheKey = "leaderboard:team_wins:{$limit}";
        $cache = Cache::store();
        if (method_exists($cache, 'supportsTags') && $cache->supportsTags()) {
            $cache = Cache::tags(['leaderboard:team_wins']);
        }
        $result = $cache->remember($cacheKey, now()->addMinutes(5), function () use ($limit) {
            $wins = FootballMatch::query()
                ->select(DB::raw("CASE WHEN home_score > away_score THEN home_team_id WHEN away_score > home_score THEN away_team_id END as team_id"), DB::raw('count(*) as wins'))
                ->where('status', 'finished')
                ->whereRaw('(home_score <> away_score)')
                ->groupBy('team_id')
                ->orderByDesc('wins')
                ->limit($limit)
                ->get();
            $teams = Team::whereIn('id', $wins->pluck('team_id'))->get()->keyBy('id');
            return $wins->map(fn($w) => [
                'team' => $teams[$w->team_id] ?? null,
                'wins' => (int) $w->wins,
            ]);
        });
        return response()->json(['status' => 'ok', 'data' => $result]);
    }
}
