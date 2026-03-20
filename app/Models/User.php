<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 一括代入を許可するカラム一覧
     * Mass Assignment（一括代入）攻撃を防ぐためのセキュリティ設定
     * create() や fill() で代入できるカラムをここで明示的に指定する
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * JSONに変換する際に隠すカラム（APIレスポンス等で非表示）
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * カラムのデータ型を変換する設定
     * 'hashed' → パスワードを自動的にBcryptでハッシュ化
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * リレーション: ユーザーは複数のTodoを持つ（1対多）
     * hasMany = 「私は多くの〇〇を持つ」という意味
     */
    public function todos()
    {
        return $this->hasMany(Todo::class);
    }
}
