<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * POST /auth/register
     * 新規登録
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // JWT発行
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * POST /auth/login
     * ログイン
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 認証失敗
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが違います',
            ], 401);
        }

        return response()->json([
            'token' => $token,
        ]);
    }

    /**
     * GET /auth/me
     * ログイン中ユーザー取得
     */
    public function me()
    {
        return response()->json(Auth::user());
    }

    /**
     * POST /auth/logout
     * ログアウト
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(null, 204);
    }

    /**
     * POST /auth/refresh
     * トークン更新
     */
    public function refresh()
    {
        return response()->json([
            'token' => JWTAuth::refresh(JWTAuth::getToken()),
        ]);
    }
}
