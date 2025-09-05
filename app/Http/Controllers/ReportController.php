<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

    public function matchReportPdf(Request $request, FootballMatch $match)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF generator not installed. Please run: composer require barryvdh/laravel-dompdf',
            ], 501);
        }

        $match->load(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }, 'goals.player', 'goals.team']);

        $homeId = $match->home_team_id;
        $awayId = $match->away_team_id;
        $homeCount = 0; $awayCount = 0;
        $goalRows = [];
        foreach ($match->goals as $g) {
            if ($g->own_goal) {
                if ((int) $g->team_id === (int) $homeId) {
                    $awayCount++;
                } else {
                    $homeCount++;
                }
            } else {
                if ((int) $g->team_id === (int) $homeId) {
                    $homeCount++;
                } else {
                    $awayCount++;
                }
            }
            $goalRows[] = [
                'minute' => (int) $g->minute,
                'player_name' => $g->player->name ?? 'N/A',
                'team_name' => $g->team->name ?? 'N/A',
                'type' => $g->own_goal ? 'Own Goal' : 'Regular',
                'score' => sprintf('%d-%d', $homeCount, $awayCount),
            ];
        }

        $homeLogoDataUri = null; $awayLogoDataUri = null;
        try {
            if (!empty($match->homeTeam->logo)) {
                $path = $match->homeTeam->logo;
                if (Storage::disk('public')->exists($path)) {
                    $contents = Storage::disk('public')->get($path);
                    $mime = null;
                    try { $mime = Storage::disk('public')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
                    if ($mime !== 'image/webp') {
                        if (!$mime) { $mime = 'image/png'; }
                        $homeLogoDataUri = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            }

            if (!empty($match->awayTeam->logo)) {
                $path = $match->awayTeam->logo;
                if (Storage::disk('public')->exists($path)) {
                    $contents = Storage::disk('public')->get($path);
                    $mime = null;
                    try { $mime = Storage::disk('public')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
                    if ($mime !== 'image/webp') {
                        if (!$mime) { $mime = 'image/png'; }
                        $awayLogoDataUri = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            }

            $makeLetterLogo = function (string $letter, string $bgHex, string $fgHex = 'ffffff'): ?string {
                if (!function_exists('imagecreatetruecolor')) { return null; }
                $size = 128;
                $im = imagecreatetruecolor($size, $size);
                if (!$im) { return null; }
                $bgHex = ltrim($bgHex, '#');
                $fgHex = ltrim($fgHex, '#');
                $bg = imagecolorallocate($im, hexdec(substr($bgHex,0,2)), hexdec(substr($bgHex,2,2)), hexdec(substr($bgHex,4,2)));
                $fg = imagecolorallocate($im, hexdec(substr($fgHex,0,2)), hexdec(substr($fgHex,2,2)), hexdec(substr($fgHex,4,2)));
                imagefilledrectangle($im, 0, 0, $size, $size, $bg);
                // Draw circle mask (to look like rounded avatar)
                if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                    imagealphablending($im, true);
                    imagesavealpha($im, true);
                }
                // Draw text (using built-in GD font)
                $letter = mb_strtoupper(mb_substr($letter, 0, 1));
                $font = 5; // built-in font size (1..5)
                $textW = imagefontwidth($font) * strlen($letter);
                $textH = imagefontheight($font);
                // Scale text by drawing larger? Built-in fonts are small; instead we can place it centered and accept smaller size
                $x = (int) (($size - $textW) / 2);
                $y = (int) (($size - $textH) / 2);
                imagestring($im, $font, $x, $y, $letter, $fg);
                // Output as PNG to buffer
                ob_start();
                imagepng($im);
                $pngData = ob_get_clean();
                imagedestroy($im);
                if (!$pngData) { return null; }
                return 'data:image/png;base64,' . base64_encode($pngData);
            };

            // Fallback to first-letter generated PNG; finally to ui-avatars
            if (empty($homeLogoDataUri)) {
                $homeLetter = (string) ($match->homeTeam->name ?? 'H');
                $homeLogoDataUri = $makeLetterLogo($homeLetter, '1f77b4', 'ffffff');
                if (empty($homeLogoDataUri)) {
                    $homeLogoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($match->homeTeam->name) . '&background=1f77b4&color=fff&rounded=true&size=128&format=png';
                    $h = Http::timeout(10)->get($homeLogoUrl);
                    if ($h->successful() && !empty($h->body())) {
                        $homeLogoDataUri = 'data:image/png;base64,' . base64_encode($h->body());
                    }
                }
            }
            if (empty($awayLogoDataUri)) {
                $awayLetter = (string) ($match->awayTeam->name ?? 'A');
                $awayLogoDataUri = $makeLetterLogo($awayLetter, 'ff7f0e', 'ffffff');
                if (empty($awayLogoDataUri)) {
                    $awayLogoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($match->awayTeam->name) . '&background=ff7f0e&color=fff&rounded=true&size=128&format=png';
                    $a = Http::timeout(10)->get($awayLogoUrl);
                    if ($a->successful() && !empty($a->body())) {
                        $awayLogoDataUri = 'data:image/png;base64,' . base64_encode($a->body());
                    }
                }
            }
        } catch (\Throwable $e) {

        }

        $viewData = [
            'match' => $match,
            'homeTeam' => $match->homeTeam,
            'awayTeam' => $match->awayTeam,
            'goalRows' => $goalRows,
            'homeLogoDataUri' => $homeLogoDataUri,
            'awayLogoDataUri' => $awayLogoDataUri,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.match', $viewData)
            ->setPaper('a4');
        $filename = sprintf('match-%d-%s-vs-%s.pdf', $match->id, str_replace(' ', '-', strtolower($match->homeTeam->name)), str_replace(' ', '-', strtolower($match->awayTeam->name)));
        return $pdf->download($filename);
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
