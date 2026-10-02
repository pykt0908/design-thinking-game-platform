<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $metrics = $this->analyticsService->getTeacherDashboardMetrics($request->user());
        return response()->json($metrics);
    }

    public function game(Request $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);
        if (!$request->user()->isAdmin() && $game->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $analytics = $this->analyticsService->getGameAnalytics($game);
        return response()->json([
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'public_id' => $game->public_id,
                'version' => $game->currentVersion?->version_number ?? '1.0',
            ],
            'analytics' => $analytics,
        ]);
    }
}
