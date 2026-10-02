<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClassroomController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\GameSessionController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TeacherAiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Public game access for student runtime player
    Route::get('/public/games/{public_id}', [GameController::class, 'publicShow']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Current user
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Design Thinking Projects
        Route::apiResource('projects', ProjectController::class);
        Route::put('/projects/{id}/step/{step}', [ProjectController::class, 'updateStep']);
        Route::post('/projects/{id}/ai-assist', [ProjectController::class, 'aiAssist']);
        Route::post('/projects/{id}/generate-game', [ProjectController::class, 'generateGame']);

        // Games & Editor
        Route::apiResource('games', GameController::class);
        Route::post('/games/{id}/save-schema', [GameController::class, 'saveSchema']);
        Route::post('/games/{id}/publish', [GameController::class, 'publish']);
        Route::post('/games/{id}/duplicate', [GameController::class, 'duplicate']);

        // Classrooms
        Route::apiResource('classrooms', ClassroomController::class);
        Route::post('/classrooms/join', [ClassroomController::class, 'join']);
        Route::post('/classrooms/{id}/assign', [ClassroomController::class, 'assignGame']);

        // Game Sessions & Gameplay Telemetry
        Route::post('/game-sessions/start', [GameSessionController::class, 'start']);
        Route::post('/game-sessions/{id}/answer', [GameSessionController::class, 'submitAnswer']);
        Route::post('/game-sessions/{id}/events', [GameSessionController::class, 'recordEvent']);
        Route::post('/game-sessions/{id}/complete', [GameSessionController::class, 'complete']);

        // Assets
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/asset-packs', [AssetController::class, 'packs']);
        Route::get('/asset-categories', [AssetController::class, 'categories']);
        Route::post('/assets/upload', [AssetController::class, 'upload']);

        // Analytics
        Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/analytics/games/{id}', [AnalyticsController::class, 'game']);

        // Teacher AI Credentials
        Route::get('/teacher/ai-credentials', [TeacherAiController::class, 'index']);
        Route::post('/teacher/ai-credentials', [TeacherAiController::class, 'store']);
        Route::post('/teacher/ai-credentials/test', [TeacherAiController::class, 'testConnection']);
    });
});
