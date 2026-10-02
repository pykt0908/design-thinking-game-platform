<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string', // can be email, username, or student_id
            'password' => 'required|string',
        ]);

        $login = $request->input('login');

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('student_id', $login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['ข้อมูลเข้าสู่ระบบไม่ถูกต้อง (อีเมล/ชื่อผู้ใช้/รหัสผ่าน)'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $user->student_id,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:teacher,student',
            'student_id' => 'nullable|string|max:64',
            'school_name' => 'nullable|string|max:255',
        ]);

        $role = $request->role ?? 'teacher';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => explode('@', $request->email)[0] . '_' . rand(100, 999),
            'password' => Hash::make($request->password),
            'role' => $role,
            'student_id' => $request->student_id,
        ]);

        if ($role === 'teacher') {
            TeacherProfile::create([
                'user_id' => $user->id,
                'school_name' => $request->school_name,
            ]);
        } else {
            StudentProfile::create([
                'user_id' => $user->id,
                'student_code' => $request->student_id,
                'school_name' => $request->school_name,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'ลงทะเบียนสำเร็จ',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $user->student_id,
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $user->student_id,
                'avatar' => $user->avatar,
                'teacher_profile' => $user->teacherProfile,
                'student_profile' => $user->studentProfile,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['message' => 'ออกจากระบบเรียบร้อยแล้ว']);
    }
}
