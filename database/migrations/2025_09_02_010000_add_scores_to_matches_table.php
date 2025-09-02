<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (!Schema::hasColumn('matches', 'home_score')) {
                $table->unsignedInteger('home_score')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('matches', 'away_score')) {
                $table->unsignedInteger('away_score')->nullable()->after('home_score');
            }
            if (!Schema::hasColumn('matches', 'finished_at')) {
                $table->timestampTz('finished_at')->nullable()->after('away_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['home_score','away_score','finished_at']);
        });
    }
};
