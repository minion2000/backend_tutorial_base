<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import {file : CSVファイルのパス}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CSVファイルからユーザーを一括インポートします';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        // ファイル存在チェック
        if (!file_exists($filePath)) {
            $this->error("ファイルが見つかりません: {$filePath}");
            return Command::FAILURE;
        }

        // CSV読み込み
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->error("ファイルを開けませんでした: {$filePath}");
            return Command::FAILURE;
        }

        // ヘッダー行をスキップ
        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error("CSVファイルが空です");
            fclose($handle);
            return Command::FAILURE;
        }

        $this->info('ユーザーのインポートを開始します...');

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $lineNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // CSVの列: name, email, password
                if (count($row) < 3) {
                    $errors[] = "行 {$lineNumber}: 列数が不足しています";
                    $errorCount++;
                    continue;
                }

                $data = [
                    'name' => trim($row[0]),
                    'email' => trim($row[1]),
                    'password' => trim($row[2]),
                ];

                // バリデーション
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|string|min:8',
                ]);

                if ($validator->fails()) {
                    $errorMessages = implode(', ', $validator->errors()->all());
                    $errors[] = "行 {$lineNumber}: {$errorMessages}";
                    $errorCount++;
                    continue;
                }

                // ユーザー作成
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                $successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("エラーが発生しました: " . $e->getMessage());
            fclose($handle);
            return Command::FAILURE;
        }

        fclose($handle);

        // 結果表示
        $this->newLine();
        $this->info("インポート完了!");
        $this->info("成功: {$successCount}件");
        $this->info("失敗: {$errorCount}件");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn("エラー詳細:");
            foreach ($errors as $error) {
                $this->warn("  - {$error}");
            }
        }

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
