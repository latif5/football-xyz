<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $squads = [
            'Manchester United' => [
                ['name' => 'Andre Onana', 'position' => 'goalkeeper', 'shirt' => 24, 'height' => 190, 'weight' => 93],
                ['name' => 'Raphael Varane', 'position' => 'defender', 'shirt' => 19, 'height' => 191, 'weight' => 81],
                ['name' => 'Lisandro Martinez', 'position' => 'defender', 'shirt' => 6, 'height' => 175, 'weight' => 77],
                ['name' => 'Bruno Fernandes', 'position' => 'midfielder', 'shirt' => 8, 'height' => 179, 'weight' => 69],
                ['name' => 'Marcus Rashford', 'position' => 'forward', 'shirt' => 10, 'height' => 186, 'weight' => 70],
            ],
            'Liverpool' => [
                ['name' => 'Alisson Becker', 'position' => 'goalkeeper', 'shirt' => 1, 'height' => 191, 'weight' => 91],
                ['name' => 'Virgil van Dijk', 'position' => 'defender', 'shirt' => 4, 'height' => 193, 'weight' => 92],
                ['name' => 'Trent Alexander-Arnold', 'position' => 'defender', 'shirt' => 66, 'height' => 180, 'weight' => 69],
                ['name' => 'Alexis Mac Allister', 'position' => 'midfielder', 'shirt' => 10, 'height' => 176, 'weight' => 72],
                ['name' => 'Mohamed Salah', 'position' => 'forward', 'shirt' => 11, 'height' => 175, 'weight' => 73],
            ],
            'Chelsea' => [
                ['name' => 'Robert Sanchez', 'position' => 'goalkeeper', 'shirt' => 1, 'height' => 197, 'weight' => 90],
                ['name' => 'Thiago Silva', 'position' => 'defender', 'shirt' => 6, 'height' => 183, 'weight' => 79],
                ['name' => 'Reece James', 'position' => 'defender', 'shirt' => 24, 'height' => 182, 'weight' => 82],
                ['name' => 'Enzo Fernandez', 'position' => 'midfielder', 'shirt' => 8, 'height' => 178, 'weight' => 75],
                ['name' => 'Raheem Sterling', 'position' => 'forward', 'shirt' => 7, 'height' => 170, 'weight' => 69],
            ],
            'Arsenal' => [
                ['name' => 'Aaron Ramsdale', 'position' => 'goalkeeper', 'shirt' => 1, 'height' => 191, 'weight' => 88],
                ['name' => 'William Saliba', 'position' => 'defender', 'shirt' => 2, 'height' => 192, 'weight' => 92],
                ['name' => 'Gabriel Magalhaes', 'position' => 'defender', 'shirt' => 6, 'height' => 190, 'weight' => 87],
                ['name' => 'Martin Odegaard', 'position' => 'midfielder', 'shirt' => 8, 'height' => 178, 'weight' => 68],
                ['name' => 'Bukayo Saka', 'position' => 'forward', 'shirt' => 7, 'height' => 178, 'weight' => 72],
            ],
            'Manchester City' => [
                ['name' => 'Ederson', 'position' => 'goalkeeper', 'shirt' => 31, 'height' => 188, 'weight' => 86],
                ['name' => 'Ruben Dias', 'position' => 'defender', 'shirt' => 3, 'height' => 187, 'weight' => 83],
                ['name' => 'Kyle Walker', 'position' => 'defender', 'shirt' => 2, 'height' => 183, 'weight' => 83],
                ['name' => 'Kevin De Bruyne', 'position' => 'midfielder', 'shirt' => 17, 'height' => 181, 'weight' => 70],
                ['name' => 'Erling Haaland', 'position' => 'forward', 'shirt' => 9, 'height' => 195, 'weight' => 94],
            ],
            'Tottenham Hotspur' => [
                ['name' => 'Guglielmo Vicario', 'position' => 'goalkeeper', 'shirt' => 13, 'height' => 194, 'weight' => 83],
                ['name' => 'Cristian Romero', 'position' => 'defender', 'shirt' => 17, 'height' => 185, 'weight' => 79],
                ['name' => 'Micky van de Ven', 'position' => 'defender', 'shirt' => 37, 'height' => 193, 'weight' => 81],
                ['name' => 'James Maddison', 'position' => 'midfielder', 'shirt' => 10, 'height' => 175, 'weight' => 73],
                ['name' => 'Son Heung-min', 'position' => 'forward', 'shirt' => 7, 'height' => 183, 'weight' => 77],
            ],
        ];

        foreach ($squads as $teamName => $players) {
            $team = Team::query()->where('name', $teamName)->first();
            if (!$team) {
                continue;
            }
            foreach ($players as $p) {
                Player::query()->updateOrCreate(
                    ['team_id' => $team->id, 'shirt_number' => $p['shirt']],
                    [
                        'team_id' => $team->id,
                        'name' => $p['name'],
                        'height' => $p['height'],
                        'weight' => $p['weight'],
                        'position' => $p['position'],
                        'shirt_number' => $p['shirt'],
                    ]
                );
            }
        }
    }
}
