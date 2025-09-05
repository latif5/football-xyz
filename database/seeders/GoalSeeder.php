<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\Goal;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        $mu = Team::where('name', 'Manchester United')->first();
        $liv = Team::where('name', 'Liverpool')->first();
        $match1 = FootballMatch::where('home_team_id', optional($mu)->id)
            ->where('away_team_id', optional($liv)->id)
            ->where('status', 'finished')
            ->first();
        if ($match1) {
            $rashford = Player::where('team_id', $mu->id)->where('name', 'Marcus Rashford')->first();
            $bruno = Player::where('team_id', $mu->id)->where('name', 'Bruno Fernandes')->first();
            $salah = Player::where('team_id', $liv->id)->where('name', 'Mohamed Salah')->first();

            $this->createGoal($match1->id, $rashford?->id, $mu->id, 23, false);
            $this->createGoal($match1->id, $salah?->id, $liv->id, 45, false);
            $this->createGoal($match1->id, $bruno?->id, $mu->id, 88, false);
        }

        $che = Team::where('name', 'Chelsea')->first();
        $ars = Team::where('name', 'Arsenal')->first();
        $match2 = FootballMatch::where('home_team_id', optional($che)->id)
            ->where('away_team_id', optional($ars)->id)
            ->where('status', 'finished')
            ->first();
        if ($match2) {
            $sterling = Player::where('team_id', $che->id)->where('name', 'Raheem Sterling')->first();
            $saka = Player::where('team_id', $ars->id)->where('name', 'Bukayo Saka')->first();

            $this->createGoal($match2->id, $sterling?->id, $che->id, 12, false);
            $this->createGoal($match2->id, $saka?->id, $ars->id, 67, false);
        }
    }

    private function createGoal(int $matchId, ?int $playerId, int $teamId, int $minute, bool $own = false): void
    {
        Goal::updateOrCreate(
            [
                'match_id' => $matchId,
                'player_id' => $playerId,
                'team_id' => $teamId,
                'minute' => $minute,
            ],
            [
                'match_id' => $matchId,
                'player_id' => $playerId,
                'team_id' => $teamId,
                'minute' => $minute,
                'own_goal' => $own,
            ]
        );
    }
}
