<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DesignDefine;
use App\Models\DesignEmpathize;
use App\Models\DesignIdeate;
use App\Models\DesignProject;
use App\Models\DesignPrototype;
use App\Models\DesignTest;
use App\Models\Game;
use App\Models\GameVersion;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(protected AIService $aiService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = DesignProject::with(['empathize', 'define', 'ideate', 'prototype', 'test']);

        if (!$user->isAdmin()) {
            $query->where('teacher_id', $user->id);
        }

        $projects = $query->orderBy('updated_at', 'desc')->paginate(15);
        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'grade_level' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'game_mode' => 'nullable|string|max:32',
            'game_genre' => 'nullable|string|max:64',
            'theme_pack' => 'nullable|string|max:64',
        ]);

        $project = DesignProject::create([
            'teacher_id' => $request->user()->id,
            'title' => $request->title,
            'subject' => $request->subject,
            'grade_level' => $request->grade_level,
            'description' => $request->description,
            'game_mode' => $request->game_mode ?? 'single',
            'game_genre' => $request->game_genre ?? 'rpg_quest',
            'theme_pack' => $request->theme_pack ?? 'fantasy',
            'current_step' => 1,
            'status' => 'in_progress',
        ]);

        // Initialize 5 steps with synced genre and theme
        DesignEmpathize::create(['project_id' => $project->id, 'grade_level' => $request->grade_level, 'subject' => $request->subject]);
        DesignDefine::create(['project_id' => $project->id]);
        DesignIdeate::create([
            'project_id' => $project->id,
            'game_genre' => $request->game_genre ?? '2D RPG Quest',
            'theme' => $request->theme_pack ?? 'fantasy',
        ]);
        DesignPrototype::create(['project_id' => $project->id]);
        DesignTest::create(['project_id' => $project->id]);

        $project->load(['empathize', 'define', 'ideate', 'prototype', 'test']);
        return response()->json($project, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::with(['empathize', 'define', 'ideate', 'prototype', 'test', 'games'])
            ->findOrFail($id);

        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($project);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->update($request->only([
            'title', 'description', 'subject', 'grade_level',
            'game_mode', 'game_genre', 'theme_pack',
            'current_step', 'status'
        ]));
        return response()->json($project);
    }

    /**
     * AI Assistant for a specific input field in Design Thinking Studio
     */
    public function aiFieldAssist(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'step' => 'required|string',
            'field' => 'required|string',
            'current_value' => 'nullable|string',
        ]);

        $step = $request->input('step');
        $field = $request->input('field');
        $currentValue = $request->input('current_value', '');

        $result = $this->aiService->generateFieldSuggestion($project, $step, $field, $currentValue);

        return response()->json($result);
    }

    /**
     * Auto-fill entire step fields using AI
     */
    public function aiStepAutoFill(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'step' => 'required|string',
        ]);

        $step = $request->input('step');
        $result = $this->aiService->generateStepAutoFill($project, $step);

        return response()->json($result);
    }

    /**
     * Auto-save a specific Design Thinking step (spec Section 39: Debounce auto-save)
     */
    public function updateStep(Request $request, int $id, string $step): JsonResponse
    {
        $project = DesignProject::findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stepData = $request->input('data', []);

        switch ($step) {
            case 'empathize':
                $record = DesignEmpathize::updateOrCreate(['project_id' => $project->id], $stepData);
                break;
            case 'define':
                $record = DesignDefine::updateOrCreate(['project_id' => $project->id], $stepData);
                break;
            case 'ideate':
                $record = DesignIdeate::updateOrCreate(['project_id' => $project->id], $stepData);
                break;
            case 'prototype':
                $record = DesignPrototype::updateOrCreate(['project_id' => $project->id], $stepData);
                break;
            case 'test':
                $record = DesignTest::updateOrCreate(['project_id' => $project->id], $stepData);
                break;
            default:
                return response()->json(['message' => 'Invalid step name'], 422);
        }

        $project->touch();

        return response()->json([
            'message' => 'บันทึกสำเร็จ (Saved)',
            'step' => $step,
            'data' => $record,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * AI Contextual Assistant for current step
     */
    public function aiAssist(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::with(['empathize', 'define', 'ideate', 'prototype', 'test'])->findOrFail($id);
        $step = $request->input('step', 'empathize');

        $suggestion = $this->aiService->getStepSuggestion($project, $step, $request->all());
        return response()->json($suggestion);
    }

    /**
     * AI Game Generation pipeline (spec Section 14)
     */
    public function generateGame(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::with(['empathize', 'define', 'ideate', 'prototype'])->findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Generate schema through AI Service
        $gameSchema = $this->aiService->generateGameSchema($project);

        $game = Game::create([
            'teacher_id' => $request->user()->id,
            'project_id' => $project->id,
            'public_id' => Game::generateUniquePublicId(),
            'title' => $gameSchema['title'] ?? $project->title,
            'description' => $gameSchema['description'] ?? $project->description,
            'theme' => $gameSchema['theme'] ?? 'school',
            'genre' => $gameSchema['genre'] ?? 'Scenario & Quiz',
            'cover_image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80',
            'status' => 'draft',
            'settings' => $gameSchema['settings'] ?? [],
        ]);

        $version = GameVersion::create([
            'game_id' => $game->id,
            'version_number' => '1.0',
            'schema_data' => json_encode($gameSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'changelog' => 'Generated by AI Game Studio from Design Thinking Project',
            'is_published' => false,
        ]);

        $game->update(['current_version_id' => $version->id]);
        $project->update(['status' => 'generated']);

        return response()->json([
            'message' => 'สร้างเกมการเรียนรู้สำเร็จ!',
            'game' => $game->load('currentVersion'),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $project = DesignProject::findOrFail($id);
        if (!$request->user()->isAdmin() && $project->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();
        return response()->json(['message' => 'ลบโปรเจกต์สำเร็จ']);
    }
}
