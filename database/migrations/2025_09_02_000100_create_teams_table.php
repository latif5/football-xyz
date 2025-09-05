<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('stadium_address')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
