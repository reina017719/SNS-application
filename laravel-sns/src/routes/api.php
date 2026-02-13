<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // 新規登録
    Route::post('/register', [AuthController::class, 'register']);

    // ログイン
    Route::post('/login', [AuthController::class, 'login']);

    //認証必須
    Route::middleware('auth:api')->group(function () {
        // ログイン中ユーザー
        Route::get('/me', [AuthController::class, 'me']);

        // ログアウト
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    // 投稿
    Route::apiResource('posts', PostController::class)
        ->only(['index', 'store', 'show', 'destroy']);

    // コメント(投稿に紐づく)
    Route::apiResource('posts.comments', CommentController::class)
        ->only(['index', 'store'])
        ->shallow();

    // コメント削除
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // いいね機能
    Route::post('/posts/{post}/likes', [LikeController::class, 'store']);
    Route::delete('/posts/{post}/likes', [LikeController::class, 'destroy']);
});