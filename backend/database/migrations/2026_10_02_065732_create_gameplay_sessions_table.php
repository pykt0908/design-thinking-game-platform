<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('game_version_id')->nullable()->constrained('game_versions')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('game_assignments')->nullOnDelete();
            $table->string('session_token', 64)->unique();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->integer('score')->default(0);
            $table->integer('max_score')->default(100);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->unsignedInteger('attempt_number')->default(1);
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->timestamps();
        });

        Schema::create('game_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->string('event_type', 64);
            $table->string('scene_id', 64)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->string('question_id', 64);
            $table->text('question_text')->nullable();
            $table->text('selected_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('score_awarded')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('max_possible_score')->default(100);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('passed')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
        Schema::dropIfExists('game_answers');
        Schema::dropIfExists('game_events');
        Schema::dropIfExists('game_sessions');
    }
};
