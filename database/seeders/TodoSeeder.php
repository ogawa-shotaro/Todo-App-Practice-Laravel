<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * シーダー
 *
 * テスト・開発用のサンプルデータをDBに投入します。
 * 実行コマンド: ./php-ext.sh artisan db:seed
 * またはマイグレーションと同時: ./php-ext.sh artisan migrate:fresh --seed
 */
class TodoSeeder extends Seeder
{
    public function run(): void
    {
        // テスト用ユーザーを作成（既に存在する場合はそのまま使用）
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'テストユーザー',
                'password' => 'password123',  // $castsで自動ハッシュ化される
            ]
        );

        // サンプルTodoデータ
        $todos = [
            [
                'title'        => 'Laravelの基礎を学ぶ',
                'body'         => 'ルーティング、コントローラー、ビュー、モデルを理解する',
                'priority'     => Todo::PRIORITY_HIGH,
                'due_date'     => now()->addDays(7),
                'is_completed' => false,
            ],
            [
                'title'        => 'マイグレーションを理解する',
                'body'         => 'up()とdown()の役割を学ぶ',
                'priority'     => Todo::PRIORITY_MEDIUM,
                'due_date'     => now()->addDays(3),
                'is_completed' => true,
            ],
            [
                'title'        => 'Bladeテンプレートを使いこなす',
                'body'         => '@foreach, @if, @extends, @yieldなどのディレクティブを学ぶ',
                'priority'     => Todo::PRIORITY_HIGH,
                'due_date'     => now()->addDays(5),
                'is_completed' => false,
            ],
            [
                'title'        => 'Eloquent ORMを理解する',
                'body'         => null,
                'priority'     => Todo::PRIORITY_MEDIUM,
                'due_date'     => now()->subDays(2),  // 期限切れ
                'is_completed' => false,
            ],
            [
                'title'        => '認証機能を実装する',
                'body'         => 'ログイン・ログアウト・会員登録の仕組みを理解する',
                'priority'     => Todo::PRIORITY_LOW,
                'due_date'     => null,
                'is_completed' => false,
            ],
        ];

        foreach ($todos as $todoData) {
            // user_id を追加してTodoを作成
            $user->todos()->create($todoData);
        }

        $this->command->info("テストユーザーを作成しました:\nEmail: test@example.com\nPassword: password123");
    }
}
