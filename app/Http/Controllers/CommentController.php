<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Article $article
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Article $article, Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        // コメント総数を取得（削除済み除外）
        $total = $article->comments()->count();

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
                'total' => $total,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Models\Article $article
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Article $article, Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'content' => 'required|string|min:10|max:100',
        ]);

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
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
