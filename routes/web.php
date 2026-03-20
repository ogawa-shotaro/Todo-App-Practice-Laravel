<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;

/**
 * ルーティング定義ファイル
 *
 * URLとコントローラーのメソッドを紐付けます。
 * 書式: Route::HTTPメソッド('URL', [コントローラー::class, 'メソッド名'])->name('ルート名');
 *
 * ルート名のメリット:
 * - URLを変更しても name() は変わらないため、ビューやリダイレクト先を1箇所修正するだけで済む
 * - ビューでは {{ route('login') }} のように使用する
 */

// =============================================
// トップページ（ログイン状態に応じてリダイレクト）
// =============================================
Route::get('/', function () {
    // auth()->check() → ログイン済みなら true
    if (auth()->check()) {
        return redirect()->route('todos.index');
    }
    return redirect()->route('login');
});

// =============================================
// 認証が不要なルート（ゲスト用）
// =============================================
// middleware('guest') → ログイン済みの場合は /todos にリダイレクト
Route::middleware('guest')->group(function () {
    // 会員登録
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // ログイン
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// =============================================
// 認証が必要なルート（ログイン済みユーザー用）
// =============================================
// middleware('auth') → 未ログインの場合は /login にリダイレクト
Route::middleware('auth')->group(function () {

    // ログアウト（POSTメソッドを使用 - CSRFトークンで保護するため）
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // =============================================
    // Todoのルート
    // =============================================

    // ゴミ箱と復元のルートは resource より前に定義する
    // （PHPのルーティングは上から順に評価されるため）
    Route::get('/todos/trash', [TodoController::class, 'trash'])->name('todos.trash');
    Route::post('/todos/{id}/restore', [TodoController::class, 'restore'])->name('todos.restore');
    Route::delete('/todos/{id}/force-delete', [TodoController::class, 'forceDelete'])->name('todos.force-delete');

    // 完了状態のトグル
    Route::post('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');

    // リソースルート（1行で7つのCRUDルートを定義）
    // GET    /todos          → index
    // GET    /todos/create   → create
    // POST   /todos          → store
    // GET    /todos/{todo}   → show
    // GET    /todos/{todo}/edit → edit
    // PUT    /todos/{todo}   → update
    // DELETE /todos/{todo}   → destroy
    Route::resource('todos', TodoController::class);
});
