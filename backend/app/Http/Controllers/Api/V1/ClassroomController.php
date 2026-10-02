<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Game;
use App\Models\GameAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $classrooms = $user->belongsToMany(Classroom::class, 'classroom_students', 'student_id', 'classroom_id')
                ->with(['teacher:id,name', 'assignments.game'])
                ->get();
            return response()->json($classrooms);
        }

        $query = Classroom::with(['students', 'assignments.game']);
        if (!$user->isAdmin()) {
            $query->where('teacher_id', $user->id);
        }

        $classrooms = $query->orderBy('created_at', 'desc')->get();
        return response()->json($classrooms);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year' => 'nullable|string|max:16',
            'semester' => 'nullable|string|max:16',
        ]);

        $classroom = Classroom::create([
            'teacher_id' => $request->user()->id,
            'name' => $request->name,
            'code' => Classroom::generateUniqueCode(),
            'description' => $request->description,
            'academic_year' => $request->academic_year ?? '2569',
            'semester' => $request->semester ?? '1',
            'status' => 'active',
        ]);

        return response()->json($classroom, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $classroom = Classroom::with([
            'teacher:id,name,email',
            'students:id,name,email,student_id',
            'assignments.game.currentVersion',
        ])->findOrFail($id);

        return response()->json($classroom);
    }

    /**
     * Student join classroom with join code (e.g. DTG-SCI01)
     */
    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $classroom = Classroom::where('code', strtoupper(trim($request->code)))->first();
        if (!$classroom) {
            return response()->json(['message' => 'ไม่พบห้องเรียนตามรหัสที่ระบุ กรุณาตรวจสอบรหัสเข้าร่วมห้องเรียน'], 404);
        }

        $student = $request->user();
        if ($classroom->students()->where('student_id', $student->id)->exists()) {
            return response()->json([
                'message' => 'คุณอยู่ในห้องเรียนนี้เรียบร้อยแล้ว',
                'classroom' => $classroom,
            ]);
        }

        $classroom->students()->attach($student->id, ['joined_at' => now()]);

        return response()->json([
            'message' => "เข้าร่วมห้องเรียน '{$classroom->name}' สำเร็จ!",
            'classroom' => $classroom->load('teacher:id,name'),
        ]);
    }

    /**
     * Assign a game to this classroom
     */
    public function assignGame(Request $request, int $id): JsonResponse
    {
        $classroom = Classroom::findOrFail($id);
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'due_at' => 'nullable|date',
            'max_attempts' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        $game = Game::findOrFail($request->game_id);

        $assignment = GameAssignment::create([
            'classroom_id' => $classroom->id,
            'game_id' => $game->id,
            'game_version_id' => $game->current_version_id,
            'start_at' => now(),
            'due_at' => $request->due_at,
            'max_attempts' => $request->max_attempts ?? 3,
            'passing_score' => $request->passing_score ?? 60,
            'show_score' => true,
            'allow_replay' => true,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => "มอบหมายเกม '{$game->title}' ให้ห้องเรียนสำเร็จ!",
            'assignment' => $assignment->load('game'),
        ], 201);
    }
}
