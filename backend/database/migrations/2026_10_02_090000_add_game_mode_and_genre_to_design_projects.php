<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_projects', function (Blueprint $table) {
            $table->string('game_mode', 32)->default('single')->after('grade_level');
            $table->string('game_genre', 64)->default('rpg_quest')->after('game_mode');
            $table->string('theme_pack', 64)->default('fantasy')->after('game_genre');
        });
    }

    public function down(): void
    {
        Schema::table('design_projects', function (Blueprint $table) {
            $table->dropColumn(['game_mode', 'game_genre', 'theme_pack']);
        });
    }
};
