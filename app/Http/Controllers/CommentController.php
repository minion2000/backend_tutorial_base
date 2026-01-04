<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentIndexRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Article $article
     * @param \App\Http\Requests\CommentIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Article $article, CommentIndexRequest $request)
    {
        $validated = $request->validated();
        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        // コメント一覧を取得（N+1回避、削除済み除外、並び順）
        $comments = $article->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => CommentResource::collection($comments->items()),
            'pagination' => [
                'page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Models\Article $article
     * @param \App\Http\Requests\CommentStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Article $article, CommentStoreRequest $request)
    {
        $validated = $request->validated();

        // コメント作成
        $comment = $article->comments()->create([
            'user_id' => $request->user()->id,
            'content' => trim($validated['content']),
        ]);

        // ユーザー情報をロード
        $comment->load('user');

        return response()->json([
            'data' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CommentUpdateRequest $request
     * @param \App\Models\Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CommentUpdateRequest $request, Comment $comment)
    {
        // 所有者確認（違えば404）
        if ($comment->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validated();

        // コメント更新
        $comment->update([
            'content' => trim($validated['content']),
        ]);

        // ユーザー情報をロード
        $comment->load('user');

        return response()->json([
            'data' => new CommentResource($comment),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Comment $comment)
    {
        // 所有者確認（違えば404）
        if ($comment->user_id !== request()->user()->id) {
            abort(404);
        }

        // 論理削除（deleted_at が自動設定される）
        $comment->delete();

        // 204 No Content を返却
        return response()->json(null, 204);
    }
}
