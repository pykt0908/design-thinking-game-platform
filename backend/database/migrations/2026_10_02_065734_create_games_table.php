<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('design_projects')->nullOnDelete();
            $table->string('public_id', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('theme')->nullable();
            $table->string('genre')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft', 'testing', 'published', 'unpublished', 'archived'])->default('draft');
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('game_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('version_number', 16)->default('1.0');
            $table->longText('schema_data'); // JSON Game Schema
            $table->string('changelog')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('game_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('game_version_id')->nullable()->constrained('game_versions')->nullOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedInteger('passing_score')->default(60);
            $table->boolean('show_score')->default(true);
            $table->boolean('allow_replay')->default(true);
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_assignments');
        Schema::dropIfExists('game_versions');
        Schema::dropIfExists('games');
    }
};
