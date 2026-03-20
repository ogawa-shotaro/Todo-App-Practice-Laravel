<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Todoモデル
 *
 * このクラスは todos テーブルに対応するモデルです。
 * Eloquent ORM（Object-Relational Mapping）を使って
 * SQL文を書かずにDB操作ができます。
 *
 * 主要なCRUD操作:
 * - Todo::all()               → 全件取得
 * - Todo::find(1)             → ID=1を取得
 * - Todo::where(...)->get()   → 条件検索
 * - Todo::create([...])       → 新規作成
 * - $todo->update([...])      → 更新
 * - $todo->delete()           → 削除（ソフトデリート）
 */
class Todo extends Model
{
    // HasFactory → テスト用のダミーデータ生成に使用
    use HasFactory;

    // SoftDeletes → 論理削除（物理的にはDBから消えない）
    // delete() を呼ぶと deleted_at に日時が入る
    // 通常のクエリではdeleted_atがNULLのものだけ取得される
    use SoftDeletes;

    /**
     * 一括代入を許可するカラム
     * セキュリティ上、$fillable に指定したカラムのみ create() / fill() で代入可能
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'priority',
        'due_date',
        'is_completed',
    ];

    /**
     * カラムの型変換
     * 'boolean' → PHP の bool 型に変換（0/1 → false/true）
     * 'date'    → Carbon（日付操作クラス）に変換
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'due_date'     => 'date',
    ];

    // =============================================
    // 優先度の定数（マジックナンバーを避けるため）
    // =============================================

    const PRIORITY_LOW    = 1;  // 低
    const PRIORITY_MEDIUM = 2;  // 中
    const PRIORITY_HIGH   = 3;  // 高

    /**
     * 優先度のラベル（表示用）
     * 配列で定義しておくと、ビューでの繰り返し表示に使いやすい
     */
    public static $priorityLabels = [
        self::PRIORITY_LOW    => '低',
        self::PRIORITY_MEDIUM => '中',
        self::PRIORITY_HIGH   => '高',
    ];

    // =============================================
    // リレーション
    // =============================================

    /**
     * リレーション: このTodoはあるユーザーに属する（多対1）
     * belongsTo = 「私は〇〇に属する」という意味
     * Todo.user_id → users.id の外部キー関係
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =============================================
    // カスタムメソッド
    // =============================================

    /**
     * 優先度のラベルを返す
     * 例: getPriorityLabel() → '高'
     */
    public function getPriorityLabel(): string
    {
        return self::$priorityLabels[$this->priority] ?? '不明';
    }

    /**
     * 優先度に応じたBootstrapのバッジクラスを返す
     * ビューでの色分け表示に使用
     */
    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_HIGH   => 'badge bg-danger',    // 赤
            self::PRIORITY_MEDIUM => 'badge bg-warning',   // 黄
            self::PRIORITY_LOW    => 'badge bg-secondary', // 灰
            default               => 'badge bg-secondary',
        };
    }

    /**
     * 期限切れかどうかを確認
     * 期限が設定されていて、今日より前なら true
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return !$this->is_completed && $this->due_date->isPast();
    }
}
