<?php

namespace App\Services\Game;

use App\Models\GameAnswer;
use App\Models\GameScore;
use App\Models\GameSession;

class ScoringService
{
    /**
     * Validate an answer against the game version schema
     */
    public function recordAnswer(GameSession $session, string $questionId, string $selectedOptionId, int $timeSpent = 0): array
    {
        $version = $session->gameVersion;
        $schema = is_array($version->schema_data) ? $version->schema_data : json_decode($version->schema_data, true);

        // Find question element in schema
        $targetQuestion = null;
        if (!empty($schema['scenes'])) {
            foreach ($schema['scenes'] as $scene) {
                if (!empty($scene['elements'])) {
                    foreach ($scene['elements'] as $element) {
                        if (($element['type'] ?? '') === 'question' && ($element['id'] ?? '') === $questionId) {
                            $targetQuestion = $element;
                            break 2;
                        }
                    }
                }
            }
        }

        $isCorrect = false;
        $points = 0;
        $explanation = '';
        $questionText = $questionId;

        if ($targetQuestion) {
            $questionText = $targetQuestion['question'] ?? $questionId;
            $options = $targetQuestion['options'] ?? [];
            $maxPoints = (int)($targetQuestion['points'] ?? 10);
            $explanation = $targetQuestion['explanation'] ?? '';

            foreach ($options as $opt) {
                if ($opt['id'] === $selectedOptionId) {
                    if (!empty($opt['isCorrect'])) {
                        $isCorrect = true;
                        $points = $maxPoints;
                    }
                    break;
                }
            }
        }

        // Record into GameAnswer
        $answer = GameAnswer::create([
            'session_id' => $session->id,
            'question_id' => $questionId,
            'question_text' => $questionText,
            'selected_answer' => $selectedOptionId,
            'is_correct' => $isCorrect,
            'score_awarded' => $points,
            'time_spent_seconds' => $timeSpent,
        ]);

        // Accumulate session score
        $newScore = $session->answers()->sum('score_awarded');
        $session->update([
            'score' => $newScore,
        ]);

        return [
            'is_correct' => $isCorrect,
            'points_awarded' => $points,
            'total_score' => $newScore,
            'explanation' => $explanation,
        ];
    }

    /**
     * Mark session completed and compute final score record
     */
    public function completeSession(GameSession $session, int $durationSeconds = 0): GameScore
    {
        $version = $session->gameVersion;
        $schema = is_array($version->schema_data) ? $version->schema_data : json_decode($version->schema_data, true);

        $maxScore = (int)($schema['scoring']['maxScore'] ?? 100);
        $passingScore = (int)($schema['scoring']['passingScore'] ?? 60);

        $totalScore = (int)$session->answers()->sum('score_awarded');
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $passed = $totalScore >= $passingScore;

        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => $durationSeconds ?: (now()->diffInSeconds($session->started_at)),
            'score' => $totalScore,
            'max_score' => $maxScore,
            'progress_percent' => 100,
        ]);

        return GameScore::updateOrCreate(
            ['session_id' => $session->id],
            [
                'student_id' => $session->student_id,
                'total_score' => $totalScore,
                'max_possible_score' => $maxScore,
                'percentage' => $percentage,
                'passed' => $passed,
            ]
        );
    }
}
