# バックエンド研修課題 - コメント機能 API 実装

## 取り組んだ課題

### 【基本課題】API の実装

**実施状況：完了**

以下の 4 つの API を設計書に従って実装しました：

-   **CMT-001** コメント一覧取得（GET `/api/v1/articles/{article}/comments`）
-   **CMT-002** コメント作成（POST `/api/v1/articles/{article}/comments`）
-   **CMT-003** コメント更新（PUT `/api/v1/comments/{comment}`）
-   **CMT-004** コメント削除（DELETE `/api/v1/comments/{comment}`）

### 【基本課題】Seeder の実装

**実施状況：完了**

-   `ArticleFactory` - faker を使ってランダムなタイトル・本文を生成
-   `CommentFactory` - faker を使ってランダムなコメント内容を生成
-   `DatabaseSeeder` - 6 ユーザー、複数記事、各記事に 3〜10 件のコメントを投入

## 各実装の工夫点・アピールポイント

### 1. Route Model Binding の採用

**変更前：**

```php
Route::put('/comments/{id}', [CommentController::class, 'update']);

public function update(Request $request, int $id)
{
    $comment = Comment::findOrFail($id);
    // ...
}
```

**変更後：**

```php
Route::put('/comments/{comment}', [CommentController::class, 'update']);

public function update(Request $request, Comment $comment)
{
    // $comment は既にインスタンス化されている
    // ...
}
```

### 2. N+1 問題への対策

コメント一覧取得時に、Eager Loading を使用：

```php
$comments = $article->comments()
    ->with('user')  // N+1問題を回避
    ->orderBy('created_at', 'desc')
    ->orderBy('id', 'desc')
    ->paginate($perPage);
```

### 3. 論理削除（SoftDeletes）の実装

物理削除ではなく論理削除

### 4. Swagger/OpenAPI ドキュメントの作成

`docs/api/openapi-sample.yml` に全 API の仕様を記載：

-   リクエスト/レスポンスの例を明記
-   バリデーションルールを明記
-   http://localhost:8002/ で API をテスト可能

## 取り組んだ課題ごとの質問・疑問と解決

### Q1: `.env` ファイルは誰が作成したのか？

**疑問：** `.env` ファイルが存在していたが、自分で作った記憶がない

**解決：** `./setup.sh` スクリプトが `.env.example` から自動コピーしていた。これは Laravel の標準的な環境構築手順で、以下の流れ：

1. `cp .env.example .env`
2. `composer install`
3. `php artisan key:generate`

### Q2: テストデータはどうやって投入する？

**疑問：** API の動作確認にデータが必要だが、どうやって用意するのか

**解決：** Seeder を使用してテストデータを一括投入

```bash
./sail artisan migrate:fresh --seed
```

Factory + faker を活用することで大量のランダムデータを簡単に生成できることを学んだ。

### Q3: Route Model Binding とは何か？

**疑問：** `/comments/{comment}` と `/comments/{id}` の違いは何か

**解決：**

-   `{id}` の場合：手動で `Comment::findOrFail($id)` が必要
-   `{comment}` の場合：Laravel が自動で Comment モデルを取得し、存在しない場合は 404 を返す

Laravel の機能を最大限活用する方が、コードが短く安全になることを学んだ。

## 得た学び

### 1. Laravel Sail の理解

Docker 環境での Laravel 開発の標準的な方法を理解できた。`./sail` コマンドで一貫した環境が構築できる便利さを実感。

### 2. Route Model Binding の威力

フレームワークの機能を理解し活用することで、コードがシンプルかつ安全になることを体験できた。

### 3. Eloquent ORM の便利さ

-   リレーションシップの定義（`hasMany`, `belongsTo`）
-   Eager Loading による N+1 問題の解決
-   SoftDeletes による論理削除

Eloquent の機能を使いこなすことで、SQL を書かずに複雑なクエリを実現できることを学んだ。
