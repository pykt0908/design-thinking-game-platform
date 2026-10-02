<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->text('description')->nullable();
            $table->string('academic_year', 16)->nullable();
            $table->string('semester', 16)->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });

        Schema::create('classroom_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['classroom_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_students');
        Schema::dropIfExists('classrooms');
    }
};
