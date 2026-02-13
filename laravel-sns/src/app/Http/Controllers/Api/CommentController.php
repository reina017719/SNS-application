<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * GET /posts/{post}/comments
     * コメント一覧
     */
    public function index(Post $post)
    {
        return $post->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn ($comment) => [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
            ],
            'message' => $comment->message,
            'user_id' =>$comment->user_id,
        ]);
    }

    /**
     * POST /posts/{post}/comments
     * コメント作成
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'message' => 'required|max:120',
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        return response()->json([
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
            ],
            'message' => $comment->message,
            'user_id' => $comment->user_id,
        ], 201);
    }

    /**
     * DELETE /comments/{comment}
     * コメント削除（shallow）
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }
}
