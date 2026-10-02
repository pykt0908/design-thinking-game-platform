<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameAssignment;
use App\Models\GameEvent;
use App\Models\GameSession;
use App\Services\Game\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameSessionController extends Controller
{
    public function __construct(protected ScoringService $scoringService) {}

    /**
     * Start a new gameplay session
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'assignment_id' => 'nullable|exists:game_assignments,id',
        ]);

        $student = $request->user();
        $game = Game::with('currentVersion')->findOrFail($request->game_id);

        $attemptNumber = GameSession::where('student_id', $student->id)
            ->where('game_id', $game->id)
            ->count() + 1;

        $session = GameSession::create([
            'student_id' => $student->id,
            'game_id' => $game->id,
            'game_version_id' => $game->current_version_id,
            'assignment_id' => $request->assignment_id,
            'session_token' => Str::random(40),
            'started_at' => now(),
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress',
        ]);

        // Record initial event
        GameEvent::create([
            'session_id' => $session->id,
            'event_type' => 'game_started',
            'payload' => ['attempt' => $attemptNumber],
        ]);

        return response()->json([
            'message' => 'เริ่มเซสชันการเล่นเกมสำเร็จ',
            'session' => $session,
        ], 201);
    }

    /**
     * Submit answer for a question with immediate evaluation
     */
    public function submitAnswer(Request $request, int $sessionId): JsonResponse
    {
        $session = GameSession::with('gameVersion')->findOrFail($sessionId);
        if ($session->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'question_id' => 'required|string',
            'selected_answer' => 'required|string',
            'time_spent_seconds' => 'nullable|integer',
        ]);

        $result = $this->scoringService->recordAnswer(
            $session,
            $request->question_id,
            $request->selected_answer,
            $request->time_spent_seconds ?? 0
        );

        return response()->json($result);
    }

    /**
     * Record telemetry event
     */
    public function recordEvent(Request $request, int $sessionId): JsonResponse
    {
        $session = GameSession::findOrFail($sessionId);
        $request->validate([
            'event_type' => 'required|string',
            'scene_id' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        $event = GameEvent::create([
            'session_id' => $session->id,
            'event_type' => $request->event_type,
            'scene_id' => $request->scene_id,
            'payload' => $request->payload,
        ]);

        return response()->json(['message' => 'Event recorded', 'event' => $event]);
    }

    /**
     * Complete game session and calculate final results
     */
    public function complete(Request $request, int $sessionId): JsonResponse
    {
        $session = GameSession::with(['gameVersion', 'answers'])->findOrFail($sessionId);
        if ($session->student_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $duration = $request->input('duration_seconds', 0);
        $scoreRecord = $this->scoringService->completeSession($session, $duration);

        return response()->json([
            'message' => 'จบภารกิจเรียบร้อยแล้ว!',
            'score_record' => $scoreRecord,
            'session' => $session->fresh(),
        ]);
    }
}
