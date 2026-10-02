<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
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
                ->whereHas('classroom.members', function ($q) use ($user) {
                    $q->where('student_id', $user->id);
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
}
