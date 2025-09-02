<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MatchSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure teams exist
        $teamA = Team::where('name', 'Manchester United')->first();
        $teamB = Team::where('name', 'Liverpool')->first();
        $teamC = Team::where('name', 'Chelsea')->first();
        $teamD = Team::where('name', 'Arsenal')->first();
        $teamE = Team::where('name', 'Manchester City')->first();
        $teamF = Team::where('name', 'Tottenham Hotspur')->first();

        if (!($teamA && $teamB && $teamC && $teamD && $teamE && $teamF)) {
            return; // teams not ready
        }

        $fixtures = [
            [
                'home' => $teamA->id, 'away' => $teamB->id,
                'start_time' => Carbon::now()->subDays(7)->setTime(19, 30),
                'status' => 'finished', 'home_score' => 2, 'away_score' => 1,
            ],
            [
                'home' => $teamC->id, 'away' => $teamD->id,
                'start_time' => Carbon::now()->subDays(3)->setTime(20, 0),
                'status' => 'finished', 'home_score' => 1, 'away_score' => 1,
            ],
            [
                'home' => $teamE->id, 'away' => $teamF->id,
                'start_time' => Carbon::now()->addDays(2)->setTime(18, 0),
                'status' => 'scheduled', 'home_score' => null, 'away_score' => null,
            ],
        ];

        foreach ($fixtures as $fx) {
            FootballMatch::query()->updateOrCreate(
                [
                    'home_team_id' => $fx['home'],
                    'away_team_id' => $fx['away'],
                    'start_time' => $fx['start_time'],
                ],
                [
                    'home_team_id' => $fx['home'],
                    'away_team_id' => $fx['away'],
                    'start_time' => $fx['start_time'],
                    'home_score' => $fx['home_score'],
                    'away_score' => $fx['away_score'],
                    'status' => $fx['status'],
                ]
            );
        }
    }
}
