<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * マイグレーションクラス
 *
 * このクラスはtodosテーブルの構造を定義します。
 * up()   → マイグレーション実行時に動く（テーブル作成）
 * down() → ロールバック時に動く（テーブル削除）
 *
 * 実行コマンド: ./php-ext.sh artisan migrate
 * 元に戻すコマンド: ./php-ext.sh artisan migrate:rollback
 */
return new class extends Migration
{
    /**
     * マイグレーション実行
     * このメソッドでテーブルの「設計図」を書きます
     */
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            // id() → BIGINT UNSIGNED AUTO_INCREMENT（主キー）
            // Laravelは自動的に id カラムを主キーにする
            $table->id();

            // user_id → どのユーザーのTodoかを管理する外部キー
            // constrained() → usersテーブルのidを参照
            // onDelete('cascade') → ユーザー削除時にTodoも削除
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // タイトル（必須、最大255文字）
            $table->string('title');

            // 詳細説明（任意、長文テキスト）
            // nullable() → NULLを許可（入力しなくても良い）
            $table->text('body')->nullable();

            // 優先度: 1=低, 2=中, 3=高
            // default(2) → デフォルトは「中」
            $table->tinyInteger('priority')->default(2);

            // 期限（任意）
            $table->date('due_date')->nullable();

            // 完了フラグ（true/false）
            // boolean型は MySQLでTINYINT(1)として保存
            $table->boolean('is_completed')->default(false);

            // ソフトデリート用カラム
            // softDeletes() → deleted_at カラムを追加
            // delete()を呼ぶとこのカラムに削除日時が入る（実際には削除されない）
            $table->softDeletes();

            // created_at と updated_at を自動管理
            $table->timestamps();
        });
    }

    /**
     * マイグレーションを元に戻す
     */
    public function down(): void
    {
        // テーブルが存在する場合のみ削除
        Schema::dropIfExists('todos');
    }
};
