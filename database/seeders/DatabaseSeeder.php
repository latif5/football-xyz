<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TeamSeeder;
use Database\Seeders\PlayerSeeder;
use Database\Seeders\MatchSeeder;
use Database\Seeders\GoalSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin',
                'password' => bcrypt('passwordDefault'),
            ]
        );

        $this->call([
            TeamSeeder::class,
            PlayerSeeder::class,
            MatchSeeder::class,
            GoalSeeder::class,
        ]);
    }
}
