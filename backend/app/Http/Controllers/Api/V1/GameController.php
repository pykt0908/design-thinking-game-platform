<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameVersion;
use App\Models\DesignProject;
use App\Models\DesignEmpathize;
use App\Models\DesignDefine;
use App\Models\DesignIdeate;
use App\Models\DesignPrototype;
use App\Models\DesignTest;
use App\Models\TeacherAiCredential;
use App\Services\AI\AIService;
use App\Services\AI\HTML5GameGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected AIService $aiService;
    protected HTML5GameGenerator $html5Generator;

    public function __construct(AIService $aiService, HTML5GameGenerator $html5Generator)
    {
        $this->aiService = $aiService;
        $this->html5Generator = $html5Generator;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Game::with(['currentVersion', 'project']);

        if (!$user->isAdmin()) {
            $query->where('teacher_id', $user->id);
        }

        $games = $query->orderBy('updated_at', 'desc')->paginate(15);
        return response()->json($games);
    }

    /**
     * Create a new game directly without requiring a Design Thinking Project
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme' => 'nullable|string',
            'genre' => 'nullable|string',
            'game_mode' => 'nullable|string',
            'subject' => 'nullable|string',
            'grade_level' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'use_ai' => 'nullable|boolean',
            'question_count' => 'nullable|integer|min:1|max:10',
            'api_key' => 'nullable|string',
        ]);

        $apiKey = $this->handleUserApiKey($request->input('api_key'), $request->user()?->id);

        $title = $request->input('title');
        $desc = $request->input('description', '');
        $theme = $request->input('theme', 'school');
        $genre = $request->input('genre', 'rpg_quest');
        $gameMode = $request->input('game_mode', 'single');
        $subject = $request->input('subject', 'ทั่วไป');
        $gradeLevel = $request->input('grade_level', 'ทุกระดับชั้น');
        $useAi = $request->boolean('use_ai', false);

        $defaultCovers = [
            'school' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80',
            'space' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80',
            'fantasy' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
            'science' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80',
        ];
        $coverImage = $request->input('cover_image') ?: ($defaultCovers[$theme] ?? $defaultCovers['school']);

        $format = $request->input('format', 'html5');
        $prompt = $request->input('prompt') ?: ($desc ?: $title);
        $features = $request->input('features', ['map', 'health_bar', 'scoreboard', 'controls', 'sound_fx']);
        $assets = $request->input('assets', []);

        if ($format === 'html5') {
            $gameSchema = $this->html5Generator->generateGame([
                'title' => $title,
                'prompt' => $prompt,
                'genre' => $genre,
                'features' => $features,
                'assets' => $assets,
                'api_key' => $apiKey,
            ], $request->user()->id);
        } elseif ($useAi) {
            $gameSchema = $this->aiService->generateDirectGameSchema([
                'title' => $title,
                'description' => $desc,
                'subject' => $subject,
                'grade_level' => $gradeLevel,
                'theme' => $theme,
                'game_genre' => $genre,
                'game_mode' => $gameMode,
                'question_count' => $request->input('question_count', 3),
            ]);
        } else {
            $gameSchema = [
                'version' => '1.0',
                'title' => $title,
                'description' => $desc,
                'theme' => $theme,
                'mode' => $gameMode,
                'genre' => $genre,
                'settings' => array_merge([
                    'duration' => 300,
                    'maxAttempts' => 3,
                    'passingScore' => 60,
                ], $request->input('settings', [])),
                'scenes' => [
                    [
                        'id' => 'scene_01',
                        'title' => 'ฉากที่ 1: แนะนำภารกิจ',
                        'background' => 'school_campus',
                        'elements' => [
                            [
                                'id' => 'welcome_text',
                                'type' => 'text',
                                'title' => 'คำชี้แจงภารกิจ',
                                'x' => 10,
                                'y' => 15,
                                'width' => 80,
                                'height' => 15,
                                'content' => "ยินดีต้อนรับสู่เกม '{$title}' อ่านโจทย์และเลือกคำตอบที่ถูกต้องเพื่อสะสมคะแนน",
                                'fontSize' => 22,
                                'color' => '#ffffff',
                                'bgColor' => '#3d0066',
                                'borderRadius' => 12,
                            ],
                            [
                                'id' => 'quiz_starter',
                                'type' => 'question',
                                'questionType' => 'multiple_choice',
                                'question' => "คำถามข้อแรกในวิชา {$subject}: ข้อใดคือคำตอบที่ถูกต้องที่สุด?",
                                'image' => $coverImage,
                                'options' => [
                                    ['id' => 'opt_1', 'text' => 'ตัวเลือกที่ 1 (คำตอบที่ถูกต้อง)', 'isCorrect' => true],
                                    ['id' => 'opt_2', 'text' => 'ตัวเลือกที่ 2', 'isCorrect' => false],
                                    ['id' => 'opt_3', 'text' => 'ตัวเลือกที่ 3', 'isCorrect' => false],
                                    ['id' => 'opt_4', 'text' => 'ตัวเลือกที่ 4', 'isCorrect' => false],
                                ],
                                'points' => 50,
                                'explanation' => 'ยินดีด้วย! คุณตอบคำถามได้ถูกต้อง',
                                'nextScene' => 'scene_finish',
                            ],
                        ],
                    ],
                    [
                        'id' => 'scene_finish',
                        'title' => 'ฉากสรุปผลและรับรางวัล',
                        'background' => 'victory_stage',
                        'elements' => [
                            [
                                'id' => 'finish_card',
                                'type' => 'completion',
                                'title' => 'ยินดีด้วย คุณผ่านภารกิจแล้ว!',
                                'message' => "คุณทำภารกิจใน '{$title}' สำเร็จเรียบร้อยแล้ว!",
                            ],
                        ],
                    ],
                ],
                'scoring' => [
                    'initialScore' => 0,
                    'maxScore' => 100,
                    'passingScore' => 60,
                ],
                'completion' => [
                    'type' => 'mission_complete',
                    'message' => 'คุณผ่านภารกิจการเรียนรู้เรียบร้อยแล้ว!',
                ],
            ];
        }

        // Create linked Design Project for unified traceability
        $dtData = $request->input('design_thinking', []);
        $empathizeData = $dtData['empathize'] ?? $request->input('empathize', []);
        $defineData = $dtData['define'] ?? $request->input('define', []);
        $ideateData = $dtData['ideate'] ?? $request->input('ideate', []);
        $prototypeData = $dtData['prototype'] ?? $request->input('prototype', []);
        $testData = $dtData['test'] ?? $request->input('test', []);

        $project = DesignProject::create([
            'teacher_id' => $request->user()->id,
            'title' => $title,
            'description' => $desc ?: $prompt,
            'subject' => $subject,
            'grade_level' => $gradeLevel,
            'game_mode' => $gameMode,
            'game_genre' => $genre,
            'theme_pack' => $theme,
            'current_step' => 5,
            'status' => 'generated',
        ]);

        if (!empty($empathizeData)) {
            DesignEmpathize::create([
                'project_id' => $project->id,
                'target_learner' => $empathizeData['target_learner'] ?? null,
                'age_group' => $empathizeData['age_group'] ?? null,
                'grade_level' => $gradeLevel,
                'subject' => $subject,
                'learner_characteristics' => $empathizeData['learner_characteristics'] ?? null,
                'pain_points' => $empathizeData['pain_points'] ?? null,
                'learning_environment' => $empathizeData['learning_environment'] ?? null,
            ]);
        }

        if (!empty($defineData)) {
            DesignDefine::create([
                'project_id' => $project->id,
                'problem_statement' => $defineData['problem_statement'] ?? null,
                'expected_outcomes' => $defineData['expected_outcomes'] ?? null,
                'knowledge_goals' => $defineData['knowledge_goals'] ?? null,
                'skill_goals' => $defineData['skill_goals'] ?? null,
            ]);
        }

        if (!empty($ideateData)) {
            DesignIdeate::create([
                'project_id' => $project->id,
                'game_concept' => $ideateData['game_concept'] ?? $title,
                'game_genre' => $genre,
                'theme' => $theme,
                'story' => $ideateData['story'] ?? null,
                'game_mechanics' => $features,
                'missions' => $ideateData['missions'] ?? null,
                'challenges' => $ideateData['challenges'] ?? null,
            ]);
        }

        DesignPrototype::create([
            'project_id' => $project->id,
            'core_rules' => $prompt,
            'character_roles' => $assets,
            'feedback_mechanisms' => 'Instant sound & animation feedback on Canvas',
        ]);

        DesignTest::create([
            'project_id' => $project->id,
            'observations' => $testData['observations'] ?? 'เกม HTML5 Canvas สามารถรันและมีปฏิสัมพันธ์ได้เรียบร้อย',
            'difficulty_rating' => $testData['difficulty_rating'] ?? 3,
            'feedback_summary' => $testData['feedback_summary'] ?? 'ระบบควบคุมลื่นไหล รองรับทั้งคีย์บอร์ดและสัมผัส',
        ]);

        $game = Game::create([
            'teacher_id' => $request->user()->id,
            'project_id' => $project->id, // Linked Design Thinking project
            'public_id' => Game::generateUniquePublicId(),
            'title' => $title,
            'description' => $desc ?: $prompt,
            'theme' => $theme,
            'genre' => $genre,
            'cover_image' => $coverImage,
            'status' => 'draft',
            'settings' => $gameSchema['settings'] ?? [],
        ]);

        $version = GameVersion::create([
            'game_id' => $game->id,
            'version_number' => '1.0',
            'schema_data' => json_encode($gameSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'changelog' => ($format === 'html5') ? 'HTML5 Interactive Game Engine' : 'Initial Studio Template',
            'is_published' => false,
        ]);

        $game->update(['current_version_id' => $version->id]);

        return response()->json([
            'message' => 'สร้างเกม HTML5 และบันทึกโปรเจกต์ Design Thinking สำเร็จพร้อมเล่นทันที!',
            'game' => $game->load('currentVersion'),
            'project_id' => $project->id,
            'is_ai_generated' => $gameSchema['is_ai_generated'] ?? true,
            'generation_note' => $gameSchema['fallback_reason'] ?? null,
        ], 201);
    }

    /**
     * AI generate single step for unified game creation
     */
    public function aiGenerateStep(Request $request): JsonResponse
    {
        $request->validate([
            'step' => 'required|string|in:empathize,define,ideate,prototype,test',
        ]);

        $step = $request->input('step');
        $params = $request->all();
        $teacherId = $request->user()?->id;

        $userApiKey = $this->handleUserApiKey($request->input('api_key'), $teacherId);
        if ($userApiKey) {
            $params['api_key'] = $userApiKey;
        }

        $data = $this->aiService->generateStepFromParams($step, $params, $teacherId);

        return response()->json([
            'step' => $step,
            'data' => $data,
        ]);
    }

    /**
     * AI auto-fill all 5 Design Thinking steps in 1 click
     */
    public function aiAutofillAll(Request $request): JsonResponse
    {
        $params = $request->all();
        $teacherId = $request->user()?->id;

        $userApiKey = $this->handleUserApiKey($request->input('api_key'), $teacherId);
        if ($userApiKey) {
            $params['api_key'] = $userApiKey;
        }

        $data = $this->aiService->generateAllStepsFromParams($params, $teacherId);

        return response()->json([
            'title' => $params['title'] ?? 'เกมการเรียนรู้',
            'data' => $data,
        ]);
    }

    /**
     * Preview HTML5 game generation on the fly
     */
    public function previewHtml5(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'prompt' => 'nullable|string',
            'genre' => 'nullable|string',
            'features' => 'nullable|array',
            'assets' => 'nullable|array',
            'api_key' => 'nullable|string',
        ]);

        $params = $request->all();
        $apiKey = $this->handleUserApiKey($request->input('api_key'), $request->user()?->id);
        if ($apiKey) {
            $params['api_key'] = $apiKey;
        }

        $gameSchema = $this->html5Generator->generateGame($params, $request->user()->id);

        return response()->json([
            'schema' => $gameSchema,
        ]);
    }

    /**
     * Refine/Tweak an existing HTML5 game using AI or new feature toggles
     */
    public function refineHtml5(Request $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'prompt' => 'required|string',
            'features' => 'nullable|array',
            'assets' => 'nullable|array',
            'api_key' => 'nullable|string',
        ]);

        $features = $request->input('features', ['map', 'health_bar', 'scoreboard', 'controls', 'sound_fx']);
        $assets = $request->input('assets', []);
        $apiKey = $this->handleUserApiKey($request->input('api_key'), $request->user()?->id);

        $newSchema = $this->html5Generator->generateGame([
            'title' => $game->title,
            'prompt' => $request->input('prompt'),
            'genre' => $game->genre ?? 'custom',
            'features' => $features,
            'assets' => $assets,
            'api_key' => $apiKey,
        ], $request->user()->id);

        $currentVer = (float)($game->currentVersion?->version_number ?? '1.0');
        $version = GameVersion::create([
            'game_id' => $game->id,
            'version_number' => number_format($currentVer + 0.1, 1, '.', ''),
            'schema_data' => json_encode($newSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'changelog' => 'AI Game Refinement: ' . mb_substr($request->input('prompt'), 0, 80, 'UTF-8'),
            'is_published' => false,
        ]);

        $game->update([
            'current_version_id' => $version->id,
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'ปรับปรุงเกมด้วย AI เรียบร้อยแล้ว!',
            'version' => $version,
            'game' => $game->load('currentVersion'),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $game = Game::with(['currentVersion', 'versions', 'project', 'assignments.classroom'])
            ->findOrFail($id);

        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($game);
    }

    /**
     * Save Game Schema from Visual Game Editor
     */
    public function saveSchema(Request $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'schema_data' => 'required',
            'changelog' => 'nullable|string',
            'create_new_version' => 'nullable|boolean',
        ]);

        $schemaInput = is_string($request->schema_data) ? $request->schema_data : json_encode($request->schema_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // If game is already published or user requested new version, create new draft version
        if ($request->create_new_version && $game->status === 'published') {
            $prevVersion = $game->currentVersion;
            $nextVersionNumber = $prevVersion ? (string)((float)$prevVersion->version_number + 0.1) : '1.1';

            $newVersion = GameVersion::create([
                'game_id' => $game->id,
                'version_number' => $nextVersionNumber,
                'schema_data' => $schemaInput,
                'changelog' => $request->changelog ?? 'อัปเดตเวอร์ชันใหม่จาก Game Builder',
                'is_published' => false,
            ]);

            $game->update([
                'current_version_id' => $newVersion->id,
                'status' => 'draft',
            ]);

            return response()->json([
                'message' => "สร้างเวอร์ชันใหม่ {$nextVersionNumber} สำเร็จ",
                'version' => $newVersion,
                'game' => $game->load('currentVersion'),
            ]);
        }

        // Otherwise update current version
        if ($game->current_version_id && $version = GameVersion::find($game->current_version_id)) {
            $version->update([
                'schema_data' => $schemaInput,
                'changelog' => $request->changelog ?? $version->changelog,
            ]);
        } else {
            $version = GameVersion::create([
                'game_id' => $game->id,
                'version_number' => '1.0',
                'schema_data' => $schemaInput,
                'is_published' => false,
            ]);
            $game->update(['current_version_id' => $version->id]);
        }

        if ($request->has('title')) {
            $game->update(['title' => $request->title]);
        }

        return response()->json([
            'message' => 'บันทึก Game Schema เรียบร้อยแล้ว (Saved)',
            'version' => $version,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Publish game to make it playable by assigned classrooms
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $game = Game::with('currentVersion')->findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->update(['status' => 'published']);
        if ($game->currentVersion) {
            $game->currentVersion->update(['is_published' => true]);
        }

        return response()->json([
            'message' => 'เผยแพร่เกมเรียบร้อยแล้ว (Game Published!)',
            'public_url' => "/play/{$game->public_id}",
            'game' => $game,
        ]);
    }

    /**
     * Duplicate a game
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $game = Game::with('currentVersion')->findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $newGame = $game->replicate(['public_id', 'status', 'created_at', 'updated_at']);
        $newGame->title = $game->title . ' (สำเนา)';
        $newGame->public_id = Game::generateUniquePublicId();
        $newGame->status = 'draft';
        $newGame->save();

        if ($game->currentVersion) {
            $newVersion = $game->currentVersion->replicate(['game_id', 'is_published', 'created_at', 'updated_at']);
            $newVersion->game_id = $newGame->id;
            $newVersion->is_published = false;
            $newVersion->save();

            $newGame->update(['current_version_id' => $newVersion->id]);
        }

        return response()->json([
            'message' => 'คัดลอกเกมเรียบร้อยแล้ว',
            'game' => $newGame->load('currentVersion'),
        ], 201);
    }

    /**
     * Public runtime loader for Student Game Player
     */
    public function publicShow(string $publicId): JsonResponse
    {
        $game = Game::with(['currentVersion', 'teacher:id,name'])->where('public_id', $publicId)->firstOrFail();

        $user = auth('sanctum')->user();
        $isTeacherOrAdmin = $user && ($user->id === $game->teacher_id || $user->isAdmin());
        $isPreview = request()->boolean('preview') 
            || request()->has('preview') 
            || request()->query('preview') === 'true'
            || request()->input('preview') === 'true';

        // Also check if assigned to a classroom the student is a member of
        $isAssignedStudent = false;
        if ($user && $user->isStudent()) {
            $isAssignedStudent = \App\Models\GameAssignment::where('game_id', $game->id)
                ->whereHas('classroom.students', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->exists();
        }

        if ($game->status !== 'published' && !$isPreview && !$isTeacherOrAdmin && !$isAssignedStudent) {
            return response()->json(['message' => 'เกมนี้ยังไม่ได้เปิดให้เล่นสาธารณะ (Unpublished)'], 403);
        }

        $schema = $game->currentVersion ? json_decode($game->currentVersion->schema_data, true) : null;

        return response()->json([
            'id' => $game->id,
            'public_id' => $game->public_id,
            'title' => $game->title,
            'description' => $game->description,
            'theme' => $game->theme,
            'cover_image' => $game->cover_image,
            'teacher_name' => $game->teacher?->name,
            'version' => $game->currentVersion?->version_number ?? '1.0',
            'schema' => $schema,
        ]);
    }

    /**
     * Delete a game
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($game) {
            \App\Models\GameSession::where('game_id', $game->id)->delete();
            \App\Models\GameAssignment::where('game_id', $game->id)->delete();
            $game->update(['current_version_id' => null]);
            \App\Models\GameVersion::where('game_id', $game->id)->delete();
            $game->delete();
        });

        return response()->json([
            'message' => 'ลบเกมเรียบร้อยแล้ว (Game Deleted)',
        ]);
    }

    /**
     * Persist and validate user-provided Gemini API key
     */
    protected function handleUserApiKey(?string $userApiKey, ?int $teacherId): ?string
    {
        if (!$userApiKey || str_starts_with($userApiKey, 'mock-')) {
            return null;
        }

        $userApiKey = trim($userApiKey);

        if ($teacherId && $userApiKey) {
            $provider = str_starts_with($userApiKey, 'sk-') ? 'openai' : 'gemini';
            $defaultModel = $provider === 'openai' ? 'gpt-4o-mini' : 'gemini-flash-lite-latest';
            
            $existing = TeacherAiCredential::where('teacher_id', $teacherId)
                ->where('provider', $provider)
                ->first();

            TeacherAiCredential::updateOrCreate(
                [
                    'teacher_id' => $teacherId,
                    'provider' => $provider,
                ],
                [
                    'encrypted_api_key' => $userApiKey,
                    'model' => $existing?->model ?: $defaultModel,
                    'is_active' => true,
                ]
            );
        }

        return $userApiKey;
    }
}
