<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Manchester United',
                'city' => 'Manchester',
                'founded_year' => 1878,
                'stadium_address' => 'Sir Matt Busby Way, Old Trafford, Manchester',
                'logo' => null,
            ],
            [
                'name' => 'Liverpool',
                'city' => 'Liverpool',
                'founded_year' => 1892,
                'stadium_address' => 'Anfield Rd, Liverpool',
                'logo' => null,
            ],
            [
                'name' => 'Chelsea',
                'city' => 'London',
                'founded_year' => 1905,
                'stadium_address' => 'Fulham Rd, Stamford Bridge, London',
                'logo' => null,
            ],
            [
                'name' => 'Arsenal',
                'city' => 'London',
                'founded_year' => 1886,
                'stadium_address' => 'Hornsey Rd, Emirates Stadium, London',
                'logo' => null,
            ],
            [
                'name' => 'Manchester City',
                'city' => 'Manchester',
                'founded_year' => 1880,
                'stadium_address' => 'Ashton New Rd, Etihad Stadium, Manchester',
                'logo' => null,
            ],
            [
                'name' => 'Tottenham Hotspur',
                'city' => 'London',
                'founded_year' => 1882,
                'stadium_address' => '782 High Rd, Tottenham Hotspur Stadium, London',
                'logo' => null,
            ],
        ];

        foreach ($teams as $data) {
            Team::query()->updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
