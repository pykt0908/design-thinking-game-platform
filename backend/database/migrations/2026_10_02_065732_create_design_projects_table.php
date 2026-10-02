<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subject')->nullable();
            $table->string('grade_level')->nullable();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->enum('status', ['draft', 'in_progress', 'ready', 'generated', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });

        Schema::create('design_empathize', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('design_projects')->cascadeOnDelete();
            $table->string('target_learner')->nullable();
            $table->string('age_group')->nullable();
            $table->string('grade_level')->nullable();
            $table->string('subject')->nullable();
            $table->text('learning_context')->nullable();
            $table->text('learner_characteristics')->nullable();
            $table->text('existing_knowledge')->nullable();
            $table->text('interests')->nullable();
            $table->text('learning_difficulties')->nullable();
            $table->text('pain_points')->nullable();
            $table->text('learning_environment')->nullable();
            $table->string('device_availability')->nullable();
            $table->json('ai_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('design_define', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('design_projects')->cascadeOnDelete();
            $table->text('problem_statement')->nullable();
            $table->text('learning_problem')->nullable();
            $table->json('learning_objectives')->nullable();
            $table->text('expected_outcomes')->nullable();
            $table->text('knowledge_goals')->nullable();
            $table->text('skill_goals')->nullable();
            $table->text('attitude_goals')->nullable();
            $table->text('success_criteria')->nullable();
            $table->json('ai_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('design_ideate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('design_projects')->cascadeOnDelete();
            $table->text('game_concept')->nullable();
            $table->string('game_genre')->nullable();
            $table->string('theme')->nullable();
            $table->text('story')->nullable();
            $table->json('game_mechanics')->nullable();
            $table->text('challenges')->nullable();
            $table->text('missions')->nullable();
            $table->text('rewards')->nullable();
            $table->string('interaction_type')->nullable();
            $table->string('difficulty')->default('medium');
            $table->unsignedInteger('duration_minutes')->default(15);
            $table->json('ai_ideas')->nullable();
            $table->timestamps();
        });

        Schema::create('design_prototype', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('design_projects')->cascadeOnDelete();
            $table->json('scene_outline')->nullable();
            $table->json('character_roles')->nullable();
            $table->json('core_rules')->nullable();
            $table->text('feedback_mechanisms')->nullable();
            $table->json('ai_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('design_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('design_projects')->cascadeOnDelete();
            $table->date('test_date')->nullable();
            $table->unsignedInteger('test_users_count')->default(0);
            $table->text('observations')->nullable();
            $table->json('recorded_bugs')->nullable();
            $table->unsignedTinyInteger('difficulty_rating')->default(3);
            $table->text('feedback_summary')->nullable();
            $table->json('ai_recommendations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_tests');
        Schema::dropIfExists('design_prototype');
        Schema::dropIfExists('design_ideate');
        Schema::dropIfExists('design_define');
        Schema::dropIfExists('design_empathize');
        Schema::dropIfExists('design_projects');
    }
};
