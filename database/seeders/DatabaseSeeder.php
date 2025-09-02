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
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed initial admin user per project requirements
        User::query()->updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin',
                'password' => bcrypt('passwordDefault'),
            ]
        );

        // Domain seeders with realistic data
        $this->call([
            TeamSeeder::class,
            PlayerSeeder::class,
            MatchSeeder::class,
            GoalSeeder::class,
        ]);
    }
}
