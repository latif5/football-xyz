<?php

namespace App\Support;

use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ReportUtils
{
    public static function buildMatchSummary(FootballMatch $match): array
    {
        $match->loadMissing(['homeTeam','awayTeam','goals' => function($q){ $q->orderBy('minute'); }, 'goals.player', 'goals.team']);

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

        if ($match->status === 'finished') {
            if ((int)$match->home_score > (int)$match->away_score) {
                $finalStatus = 'Tim Home Menang';
            } elseif ((int)$match->away_score > (int)$match->home_score) {
                $finalStatus = 'Tim Away Menang';
            } else {
                $finalStatus = 'Draw';
            }
        } else {
            $finalStatus = ucfirst($match->status ?? '');
        }

        $topScorer = null;
        if ($match->goals && $match->goals->count() > 0) {
            $counts = [];
            foreach ($match->goals as $g) {
                $pid = (int) ($g->player_id ?? 0);
                if ($pid <= 0) { continue; }
                $counts[$pid] = ($counts[$pid] ?? 0) + 1;
            }
            if (!empty($counts)) {
                arsort($counts);
                $topPlayerId = array_key_first($counts);
                $topGoals = $counts[$topPlayerId] ?? 0;
                $player = $match->goals->firstWhere('player_id', $topPlayerId)?->player;
                $topScorer = [
                    'player_name' => $player->name ?? 'N/A',
                    'goals' => (int) $topGoals,
                ];
            }
        }

        $cutoff = $match->start_time ?? now();
        $homeWinsUpTo = FootballMatch::query()
            ->where('status', 'finished')
            ->where('start_time', '<=', $cutoff)
            ->where(function($q) use ($homeId) {
                $q->where(function($qq) use ($homeId) {
                    $qq->where('home_team_id', $homeId)->whereColumn('home_score', '>', 'away_score');
                })->orWhere(function($qq) use ($homeId) {
                    $qq->where('away_team_id', $homeId)->whereColumn('away_score', '>', 'home_score');
                });
            })
            ->count();
        $awayWinsUpTo = FootballMatch::query()
            ->where('status', 'finished')
            ->where('start_time', '<=', $cutoff)
            ->where(function($q) use ($awayId) {
                $q->where(function($qq) use ($awayId) {
                    $qq->where('home_team_id', $awayId)->whereColumn('home_score', '>', 'away_score');
                })->orWhere(function($qq) use ($awayId) {
                    $qq->where('away_team_id', $awayId)->whereColumn('away_score', '>', 'home_score');
                });
            })
            ->count();

        return [
            'goalRows' => $goalRows,
            'finalStatus' => $finalStatus,
            'topScorer' => $topScorer,
            'homeWinsUpTo' => (int) $homeWinsUpTo,
            'awayWinsUpTo' => (int) $awayWinsUpTo,
        ];
    }

    public static function getTeamLogoDataUri(Team $team, string $bgHex = '1f77b4', ?string $letterSource = null): ?string
    {
        $dataUri = null;
        try {
            if (!empty($team->logo)) {
                $path = $team->logo;
                if (Storage::disk('public')->exists($path)) {
                    $contents = Storage::disk('public')->get($path);
                    $mime = null;
                    try { $mime = Storage::disk('public')->mimeType($path); } catch (\Throwable $e) { /* ignore */ }
                    if ($mime !== 'image/webp') {
                        if (!$mime) { $mime = 'image/png'; }
                        $dataUri = 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            }

            if (empty($dataUri)) {
                $letter = mb_strtoupper(mb_substr((string)($letterSource ?? $team->name ?? 'A'), 0, 1));
                $gen = self::makeLetterPngDataUri($letter, $bgHex, 'ffffff');
                if (!empty($gen)) {
                    $dataUri = $gen;
                }
            }

            if (empty($dataUri)) {
                $url = 'https://ui-avatars.com/api/?name=' . urlencode($team->name ?? 'Team') . '&background=' . ltrim($bgHex,'#') . '&color=fff&rounded=true&size=128&format=png';
                $res = Http::timeout(10)->get($url);
                if ($res->successful() && !empty($res->body())) {
                    $dataUri = 'data:image/png;base64,' . base64_encode($res->body());
                }
            }
        } catch (\Throwable $e) {
            
        }
        return $dataUri;
    }

    protected static function makeLetterPngDataUri(string $letter, string $bgHex, string $fgHex = 'ffffff'): ?string
    {
        if (!function_exists('imagecreatetruecolor')) { return null; }
        $size = 128;
        $im = imagecreatetruecolor($size, $size);
        if (!$im) { return null; }
        $bgHex = ltrim($bgHex, '#');
        $fgHex = ltrim($fgHex, '#');
        $bg = imagecolorallocate($im, hexdec(substr($bgHex,0,2)), hexdec(substr($bgHex,2,2)), hexdec(substr($bgHex,4,2)));
        $fg = imagecolorallocate($im, hexdec(substr($fgHex,0,2)), hexdec(substr($fgHex,2,2)), hexdec(substr($fgHex,4,2)));
        imagefilledrectangle($im, 0, 0, $size, $size, $bg);
        if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
            imagealphablending($im, true);
            imagesavealpha($im, true);
        }
        $letter = mb_strtoupper(mb_substr($letter, 0, 1));
        $font = 5;
        $textW = imagefontwidth($font) * strlen($letter);
        $textH = imagefontheight($font);
        $x = (int) (($size - $textW) / 2);
        $y = (int) (($size - $textH) / 2);
        imagestring($im, $font, $x, $y, $letter, $fg);
        ob_start();
        imagepng($im);
        $pngData = ob_get_clean();
        imagedestroy($im);
        if (!$pngData) { return null; }
        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
