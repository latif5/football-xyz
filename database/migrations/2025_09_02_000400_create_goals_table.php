<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches');
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('team_id')->constrained('teams');
            $table->unsignedSmallInteger('minute');
            $table->boolean('own_goal')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['match_id','team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
