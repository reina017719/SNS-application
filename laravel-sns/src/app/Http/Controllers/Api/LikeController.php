<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * POST /posts/{post}/likes
     * いいねする
     */
    public function store(Post $post)
    {
        $userId = Auth::id();

        // すでにいいねしてたら何もしない
        if ($post->likes()->where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'Already liked',
            ], 409);
        }

        $post->likes()->create([
            'user_id' => $userId,
        ]);

        return response()->json([
            'liked' => true,
            'like_count' => $post->likes()->count(),
        ], 201);
    }

    /**
     * DELETE /posts/{post}/likes
     * いいね解除
     */
    public function destroy(Post $post)
    {
        $userId = Auth::id();

        $post->likes()
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'liked' => false,
            'like_count' => $post->likes()->count(),
        ], 200);
    }
}