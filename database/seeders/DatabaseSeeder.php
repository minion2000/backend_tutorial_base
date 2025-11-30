<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // テストユーザーを作成
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 追加のユーザーを作成
        $users = User::factory(5)->create();
        $allUsers = $users->push($testUser);

        // 記事を作成（各ユーザーが1〜3記事を投稿）
        $articles = collect();
        foreach ($allUsers as $user) {
            $userArticles = Article::factory(rand(1, 3))->create([
                'user_id' => $user->id,
            ]);
            $articles = $articles->merge($userArticles);
        }

        // 各記事にコメントを作成（3〜10件のコメント）
        foreach ($articles as $article) {
            Comment::factory(rand(3, 10))->create([
                'article_id' => $article->id,
                'user_id' => $allUsers->random()->id,
            ]);
        }
    }
}
