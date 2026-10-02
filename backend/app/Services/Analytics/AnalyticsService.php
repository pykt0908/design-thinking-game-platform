<?php

namespace App\Services\Analytics;

use App\Models\Classroom;
use App\Models\Game;
use App\Models\GameAnswer;
use App\Models\GameSession;
use App\Models\User;

class AnalyticsService
{
    /**
     * Get overview metrics for Teacher Dashboard
     */
    public function getTeacherDashboardMetrics(User $teacher): array
    {
        $classroomsCount = Classroom::where('teacher_id', $teacher->id)->count();
        $gamesCount = Game::where('teacher_id', $teacher->id)->count();
        $publishedGamesCount = Game::where('teacher_id', $teacher->id)->where('status', 'published')->count();
        $draftGamesCount = Game::where('teacher_id', $teacher->id)->where('status', 'draft')->count();

        // Students in all teacher's classrooms
        $studentIds = \Illuminate\Support\Facades\DB::table('classroom_students')
            ->join('classrooms', 'classroom_students.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.teacher_id', $teacher->id)
            ->distinct()
            ->pluck('classroom_students.student_id');

        $studentsCount = $studentIds->count();

        // Game play sessions
        $gameIds = Game::where('teacher_id', $teacher->id)->pluck('id');
        $sessions = GameSession::whereIn('game_id', $gameIds);
        $totalPlays = (clone $sessions)->count();
        $completedPlays = (clone $sessions)->where('status', 'completed')->count();
        $averageScore = (clone $sessions)->where('status', 'completed')->avg('score') ?? 0;

        return [
            'classrooms_count' => $classroomsCount,
            'students_count' => $studentsCount,
            'games_count' => $gamesCount,
            'published_games_count' => $publishedGamesCount,
            'draft_games_count' => $draftGamesCount,
            'total_plays' => $totalPlays,
            'completed_plays' => $completedPlays,
            'average_score' => round($averageScore, 1),
            'completion_rate' => $totalPlays > 0 ? round(($completedPlays / $totalPlays) * 100, 1) : 0,
        ];
    }

    /**
     * Get detailed analytics for a single game
     */
    public function getGameAnalytics(Game $game): array
    {
        $sessions = GameSession::where('game_id', $game->id)->get();
        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'completed');
        $completedCount = $completedSessions->count();

        $avgScore = $completedSessions->avg('score') ?? 0;
        $avgDuration = $completedSessions->avg('duration_seconds') ?? 0;
        $completionRate = $totalSessions > 0 ? round(($completedCount / $totalSessions) * 100, 1) : 0;

        // Question difficulty breakdown
        $sessionIds = $sessions->pluck('id');
        $answers = GameAnswer::whereIn('session_id', $sessionIds)->get();

        $questionStats = [];
        $answersByQuestion = $answers->groupBy('question_id');

        foreach ($answersByQuestion as $qid => $group) {
            $total = $group->count();
            $correct = $group->where('is_correct', true)->count();
            $rate = $total > 0 ? round(($correct / $total) * 100, 1) : 0;

            $first = $group->first();
            $questionStats[] = [
                'question_id' => $qid,
                'question_text' => $first?->question_text ?? $qid,
                'total_answers' => $total,
                'correct_answers' => $correct,
                'correct_rate' => $rate,
                'is_difficult' => $rate < 50,
            ];
        }

        // AI Improvement Suggestions
        $aiImprovements = [];
        foreach ($questionStats as $q) {
            if ($q['is_difficult'] && $q['total_answers'] >= 2) {
                $aiImprovements[] = [
                    'type' => 'difficult_question',
                    'title' => "ข้อคำถามที่ผู้เรียนตอบผิดบ่อย ({$q['correct_rate']}%)",
                    'description' => "คำถาม '{$q['question_text']}' มีอัตราตอบถูกต่ำ แนะนำให้เพิ่มตัวอย่างหรือคำใบ้ (Hint) ก่อนเข้าสู่คำถาม",
                    'action' => 'Apply to New Version',
                ];
            }
        }

        if (empty($aiImprovements) && $totalSessions > 0) {
            $aiImprovements[] = [
                'type' => 'good_balance',
                'title' => 'ระดับความยากเหมาะสม',
                'description' => 'ผู้เรียนทำคะแนนเฉลี่ยอยู่ในเกณฑ์ที่น่าพึงพอใจ แนะนำให้เพิ่มโจทย์ระดับ Advance สำหรับผู้เรียนที่ต้องการความท้าทาย',
                'action' => 'Add Advanced Level',
            ];
        }

        return [
            'total_players' => $sessions->unique('student_id')->count(),
            'total_plays' => $totalSessions,
            'completion_rate' => $completionRate,
            'average_score' => round($avgScore, 1),
            'average_duration_seconds' => round($avgDuration),
            'questions' => $questionStats,
            'ai_improvements' => $aiImprovements,
        ];
    }
}
