<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Match Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #1a1a1a; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { color: #555; font-size: 12px; }
        .score { font-size: 22px; font-weight: bold; margin-top: 6px; }
        .section { margin-top: 16px; }
        .section h3 { margin: 0 0 8px 0; font-size: 14px; }
        .table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .muted { color: #777; }
        .chip { display: inline-block; padding: 2px 6px; border-radius: 10px; background: #eee; font-size: 10px; }
        .chart { width: 100%; margin-top: 8px; }
        .scoreboard { display: table; width: 100%; border: 1px solid #eee; padding: 12px; border-radius: 8px; box-sizing: border-box; }
        .scoreboard-row { display: table-row; }
        .scoreboard-cell { display: table-cell; vertical-align: middle; }
        .scoreboard-cell.left { text-align: left; }
        .scoreboard-cell.center { text-align: center; width: 1%; white-space: nowrap; }
        .scoreboard-cell.right { text-align: right; }
        .team { display: inline-block; }
        .team .logo { height: 40px; border-radius: 50%; object-fit: cover; object-position: center; display: block; }
        .team .name { display: block; margin-top: 6px; font-size: 12px; color: #555; line-height: 1.2; }
        .team.left .name { text-align: left; }
        .team.right .name { text-align: right; }
        .team.left .logo { margin-left: 0; margin-right: auto; }
        .team.right .logo { margin-left: auto; margin-right: 0; }
        .final { font-size: 28px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Match Report</div>
            <div class="subtitle">ID #{{ $match->id }} • {{ $match->start_time?->format('Y-m-d H:i') }} • Status: {{ ucfirst($match->status) }}</div>
        </div>
        <div class="score">{{ $homeTeam->name }} {{ $match->home_score }} — {{ $match->away_score }} {{ $awayTeam->name }}</div>
    </div>

    <div class="section">
        <div class="scoreboard">
            <div class="scoreboard-row">
                <div class="scoreboard-cell left">
                    <div class="team left">
                        @if(!empty($homeLogoDataUri))
                            <img class="logo" src="{{ $homeLogoDataUri }}" alt="{{ $homeTeam->name }}" />
                        @endif
                        <span class="name">{{ $homeTeam->name }}</span>
                    </div>
                </div>
                <div class="scoreboard-cell center">
                    <div class="final">{{ $match->home_score }} — {{ $match->away_score }}</div>
                </div>
                <div class="scoreboard-cell right">
                    <div class="team right">
                        @if(!empty($awayLogoDataUri))
                            <img class="logo" src="{{ $awayLogoDataUri }}" alt="{{ $awayTeam->name }}" />
                        @endif
                        <span class="name">{{ $awayTeam->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Goals</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Minute</th>
                    <th>Player</th>
                    <th>Team</th>
                    <th>Type</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($goalRows ?? []) as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['minute'] }}'</td>
                        <td>{{ $row['player_name'] }}</td>
                        <td>{{ $row['team_name'] }}</td>
                        <td>
                            @if(($row['type'] ?? '') === 'Own Goal')
                                <span class="chip">Own Goal</span>
                            @else
                                <span class="muted">Regular</span>
                            @endif
                        </td>
                        <td>{{ $row['score'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No goals recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
