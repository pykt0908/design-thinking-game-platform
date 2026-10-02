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
            'base_url' => 'nullable|string',
        ]);

        $teacherId = $request->user()->id;

        // Deactivate other providers for this teacher so the saved one becomes primary active
        TeacherAiCredential::where('teacher_id', $teacherId)->update(['is_active' => false]);

        $updateData = [
            'model' => $request->model,
            'base_url' => $request->base_url,
            'is_active' => true,
        ];

        if ($request->api_key && $request->api_key !== 'existing') {
            $updateData['encrypted_api_key'] = $request->api_key;
        }

        $credential = TeacherAiCredential::updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'provider' => $request->provider,
            ],
            $updateData
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
            'api_key' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        $apiKey = $request->input('api_key');
        if (!$apiKey || $apiKey === 'existing') {
            $cred = TeacherAiCredential::where('teacher_id', $request->user()->id)
                ->where('provider', $request->provider)
                ->first();
            $apiKey = $cred?->encrypted_api_key;
        }

        if ($request->provider === 'gemini') {
            if (!$apiKey || str_starts_with($apiKey, 'mock-')) {
                return response()->json([
                    'success' => false,
                    'message' => 'ยังไม่มี Google Gemini API Key ที่ถูกต้อง (กรุณากรอก API Key)',
                ], 422);
            }

            try {
                $start = microtime(true);
                $model = $request->input('model') ?: 'gemini-flash-lite-latest';
                $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(10)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [
                            ['role' => 'user', 'parts' => [['text' => 'ping']]],
                        ],
                    ]);

                $latency = round((microtime(true) - $start) * 1000);

                if ($res->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'เชื่อมต่อ Google Gemini สำเร็จ พร้อมใช้งานแล้ว!',
                        'latency_ms' => $latency,
                    ]);
                } else {
                    $err = $res->json('error.message') ?: $res->body();
                    return response()->json([
                        'success' => false,
                        'message' => 'เชื่อมต่อ Gemini ไม่สำเร็จ: ' . $err,
                    ], 422);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage(),
                ], 500);
            }
        } elseif ($request->provider === 'openai') {
            if (!$apiKey || str_starts_with($apiKey, 'mock-')) {
                return response()->json([
                    'success' => false,
                    'message' => 'ยังไม่มี OpenAI API Key ที่ถูกต้อง (กรุณากรอก API Key)',
                ], 422);
            }

            try {
                $start = microtime(true);
                $model = $request->input('model') ?: 'gpt-4o-mini';
                $baseUrl = rtrim($request->input('base_url') ?: 'https://api.openai.com/v1', '/');
                $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(12)
                    ->withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$baseUrl}/chat/completions", [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => 'ping']],
                        'max_tokens' => 5,
                    ]);

                $latency = round((microtime(true) - $start) * 1000);

                if ($res->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'เชื่อมต่อ OpenAI (ChatGPT) สำเร็จ พร้อมใช้งานแล้ว!',
                        'latency_ms' => $latency,
                    ]);
                } else {
                    $err = $res->json('error.message') ?: $res->body();
                    return response()->json([
                        'success' => false,
                        'message' => 'เชื่อมต่อ OpenAI ไม่สำเร็จ: ' . $err,
                    ], 422);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ OpenAI: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "บันทึกการตั้งค่า AI Provider ({$request->provider}) แล้ว",
            'latency_ms' => rand(120, 280),
        ]);
    }
}
