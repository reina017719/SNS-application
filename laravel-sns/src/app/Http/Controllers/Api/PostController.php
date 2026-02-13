<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * GET /posts
     * 投稿一覧（liked / like_count 付き）
     */
    public function index()
    {
        $posts = Post::with('user')
            ->withCount('likes')
            ->withExists([
                'likes as is_liked' => fn ($q) =>
                    $q->where('user_id', Auth::id())
            ])
            ->latest()
            ->get();

        return response()->json($posts);
    }

    /**
     * POST /posts
     * 投稿作成
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|max:120'
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json($post, 201);
    }

    /**
     * GET /posts/{post}
     * 投稿詳細（※将来用）
     */
    public function show(Post $post)
    {
        $post->load('user')
            ->loadCount('likes')
            ->loadExists([
                'likes as is_liked' => fn ($q) =>
                    $q->where('user_id', Auth::id())
            ]);

        return response()->json($post);
    }

    /**
     * DELETE /posts/{post}
     * 投稿削除
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $post->delete();

        return response()->json(null, 204);
    }
}
