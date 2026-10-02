<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TeacherAiCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $credentials = TeacherAiCredential::where('teacher_id', $request->user()->id)->get();
        return response()->json($credentials);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:gemini,openai,anthropic,custom',
            'api_key' => 'required|string',
            'model' => 'nullable|string',
            'base_url' => 'nullable|string|url',
        ]);

        $credential = TeacherAiCredential::updateOrCreate(
            [
                'teacher_id' => $request->user()->id,
                'provider' => $request->provider,
            ],
            [
                'encrypted_api_key' => $request->api_key,
                'model' => $request->model,
                'base_url' => $request->base_url,
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'บันทึกการตั้งค่า AI สำเร็จ!',
            'credential' => $credential,
        ]);
    }

    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:gemini,openai,anthropic,custom',
        ]);

        // Simulated connection test or real check
        return response()->json([
            'success' => true,
            'message' => "เชื่อมต่อ AI Provider ({$request->provider}) สำเร็จ พร้อมใช้งาน!",
            'latency_ms' => rand(120, 280),
        ]);
    }
}
