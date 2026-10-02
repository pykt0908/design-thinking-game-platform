<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('theme')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['character', 'background', 'object', 'item', 'ui', 'icon', 'audio'])->index();
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->foreignId('asset_pack_id')->nullable()->constrained('asset_packs')->nullOnDelete();
            $table->string('file_path');
            $table->string('preview_url')->nullable();
            $table->string('style')->nullable();
            $table->string('theme')->nullable();
            $table->json('metadata')->nullable();
            $table->string('license')->default('CC-BY');
            $table->string('author')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_packs');
        Schema::dropIfExists('asset_categories');
    }
};
