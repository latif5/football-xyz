<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('teams');
            $table->foreignId('away_team_id')->constrained('teams');
            $table->timestamp('start_time');
            $table->unsignedSmallInteger('home_score')->nullable();
            $table->unsignedSmallInteger('away_score')->nullable();
            $table->enum('status', ['scheduled','finished'])->default('scheduled');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_time','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
