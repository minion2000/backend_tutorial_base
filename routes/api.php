<?php

use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1
Route::prefix('v1')->group(function () {
    // コメント一覧取得（認証不要）
    Route::get('/articles/{article}/comments', [CommentController::class, 'index']);

    // コメント作成（認証必須）
    Route::post('/articles/{article}/comments', [CommentController::class, 'store'])
        ->middleware('auth:sanctum');

    // コメント更新（認証必須）
    Route::put('/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('auth:sanctum');

    // コメント削除（認証必須）
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('auth:sanctum');
});
