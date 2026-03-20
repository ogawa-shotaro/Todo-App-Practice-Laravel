# Laravel Todo App

Laravel の入門資料（[参考資料](https://iloveudemy.banana-quantum.com/basic/index.html)）をもとに作成した学習用Todoアプリです。

## 実装機能

| 機能 | 説明 |
|---|---|
| ユーザー認証 | 会員登録・ログイン・ログアウト |
| Todo CRUD | 作成・一覧・詳細・編集・削除 |
| ソフトデリート | ゴミ箱・復元・完全削除 |
| 検索・フィルタ | キーワード・状態・優先度で絞り込み |
| ページネーション | 一覧を10件ずつ分割表示 |
| 完了トグル | ワンクリックで完了/未完了を切り替え |

## 技術スタック

- **PHP** 8.3
- **Laravel** 13.x
- **データベース** SQLite（学習用）
- **CSS** Bootstrap 5（CDN）

---

## 環境構築

### 前提条件

以下がインストールされていること：

- PHP 8.3
- Composer 2.x

> **注意（このリポジトリ固有の事情）**
> このシステムには `php8.3-xml` と `php8.3-sqlite3` パッケージが未インストールです。
> `php-ext.sh` スクリプトがその回避策として機能します。
> 通常の環境では `sudo apt-get install php8.3-xml php8.3-sqlite3` で解決します。

### セットアップ手順

```bash
# 1. リポジトリをクローン
git clone <リポジトリURL>
cd Todo-App-Practice-Laravel

# 2. PHP拡張のセットアップ（このシステム固有の手順）
mkdir -p /tmp/php-ext
cd /tmp
apt-get download php8.3-xml php8.3-sqlite3
dpkg --extract php8.3-xml_*.deb /tmp/php-ext
dpkg --extract php8.3-sqlite3_*.deb /tmp/php-ext
cd -

# システムiniをベースにカスタムphp.iniを生成
mkdir -p /home/ogawa/.php
cp /etc/php/8.3/cli/php.ini /home/ogawa/.php/php.ini
for f in /etc/php/8.3/cli/conf.d/*.ini; do cat "$f" >> /home/ogawa/.php/php.ini; done
cat >> /home/ogawa/.php/php.ini << 'EOF'
extension=/tmp/php-ext/usr/lib/php/20230831/xml.so
extension=/tmp/php-ext/usr/lib/php/20230831/dom.so
extension=/tmp/php-ext/usr/lib/php/20230831/simplexml.so
extension=/tmp/php-ext/usr/lib/php/20230831/sqlite3.so
extension=/tmp/php-ext/usr/lib/php/20230831/pdo_sqlite.so
EOF

# 3. Composerパッケージをインストール
PHPRC=/home/ogawa/.php PHP_INI_SCAN_DIR="" composer install

# 4. .envを設定
cp .env.example .env
./php-ext.sh artisan key:generate

# 5. DBを作成してマイグレーションを実行
touch database/database.sqlite
./php-ext.sh artisan migrate

# 6. テストデータを投入（任意）
./php-ext.sh artisan db:seed
```

### 開発サーバーの起動

```bash
./php-ext.sh artisan serve --host=0.0.0.0 --port=8000
```

ブラウザで http://localhost:8000 にアクセス

### テストアカウント（シーダー実行後）

| 項目 | 値 |
|---|---|
| Email | test@example.com |
| Password | password123 |

---

## プロジェクト構成

```
app/
├── Http/Controllers/
│   ├── AuthController.php    # 認証（登録・ログイン・ログアウト）
│   └── TodoController.php    # Todo CRUD + ゴミ箱・完了トグル
├── Models/
│   ├── User.php              # ユーザーモデル（1対多リレーション）
│   └── Todo.php              # Todoモデル（SoftDeletes・カスタムメソッド）
└── Providers/
    └── AppServiceProvider.php  # BootstrapページネーションUI設定

database/
├── migrations/
│   └── 2024_01_01_000000_create_todos_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── TodoSeeder.php

resources/views/
├── layouts/app.blade.php     # マスターレイアウト
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── todos/
    ├── index.blade.php       # 一覧（検索・フィルタ・ページネーション）
    ├── create.blade.php
    ├── show.blade.php
    ├── edit.blade.php
    └── trash.blade.php       # ゴミ箱

routes/web.php                # 全ルート定義
php-ext.sh                    # PHP拡張ラッパースクリプト
```

---

## Git Flow での開発手順

このプロジェクトをゼロから作り直す場合の**Git Flow**を使った開発フローです。

### Git Flow のブランチ戦略

```
main          本番環境に常にデプロイ可能な状態を保つ
develop       開発の統合ブランチ。各featureはここにマージ
feature/*     機能単位の作業ブランチ（developから分岐）
release/*     リリース準備ブランチ（developから分岐→mainにマージ）
hotfix/*      本番バグ修正ブランチ（mainから分岐→main/developにマージ）
```

### 初期セットアップ

```bash
# git-flow をインストール（未インストールの場合）
sudo apt-get install git-flow   # Ubuntu/WSL
brew install git-flow           # macOS

# リポジトリを初期化
git init
git flow init
# ブランチ名はデフォルト（main/develop/feature/ など）でEnterを押す
```

---

### Feature 1: Laravelプロジェクト作成・基本設定

```bash
git flow feature start initial-laravel-setup
```

**作業内容:**

```bash
# Laravelプロジェクトを作成
composer create-project laravel/laravel . --prefer-dist

# .env を設定（SQLite・fileセッション）
cp .env.example .env
```

`.env` の変更箇所：
```env
APP_NAME="Todo App"
APP_URL=http://localhost:8000
APP_LOCALE=ja
DB_CONNECTION=sqlite
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

```bash
# アプリケーションキーを生成
./php-ext.sh artisan key:generate

# SQLiteのDBファイルを作成
touch database/database.sqlite

git add .env.example .env php-ext.sh
git commit -m "feat: Laravelプロジェクトの初期設定（SQLite・日本語ロケール）"

git flow feature finish initial-laravel-setup
```

---

### Feature 2: データベースマイグレーション

```bash
git flow feature start database-migration
```

**作業内容:**

`database/migrations/2024_01_01_000000_create_todos_table.php` を作成：

```php
Schema::create('todos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('body')->nullable();
    $table->tinyInteger('priority')->default(2);  // 1:低 2:中 3:高
    $table->date('due_date')->nullable();
    $table->boolean('is_completed')->default(false);
    $table->softDeletes();   // deleted_at カラム（論理削除用）
    $table->timestamps();    // created_at, updated_at
});
```

```bash
./php-ext.sh artisan migrate

git add database/migrations/
git commit -m "feat: todosテーブルのマイグレーション追加（SoftDeletes・外部キー制約）"

git flow feature finish database-migration
```

**学習ポイント:**
- `foreignId('user_id')->constrained()` → usersテーブルへの外部キー制約
- `softDeletes()` → `deleted_at` カラムを追加（論理削除）
- `timestamps()` → `created_at` / `updated_at` を自動管理

---

### Feature 3: Todoモデル

```bash
git flow feature start todo-model
```

**作業内容:**

`app/Models/Todo.php` を作成。
`app/Models/User.php` にリレーションを追加。

```php
// Todo.php のポイント
class Todo extends Model
{
    use SoftDeletes;  // 論理削除を有効化

    protected $fillable = ['user_id', 'title', 'body', 'priority', 'due_date', 'is_completed'];

    protected $casts = [
        'is_completed' => 'boolean',  // 0/1 → false/true に自動変換
        'due_date'     => 'date',     // 文字列 → Carbonオブジェクトに自動変換
    ];

    // リレーション: このTodoはあるユーザーに属する（多対1）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

```php
// User.php に追加
// リレーション: ユーザーは複数のTodoを持つ（1対多）
public function todos()
{
    return $this->hasMany(Todo::class);
}
```

```bash
git add app/Models/
git commit -m "feat: TodoモデルとUserモデルのリレーション定義"

git flow feature finish todo-model
```

**学習ポイント:**
- `$fillable` → Mass Assignment攻撃を防ぐセキュリティ設定
- `$casts` → DBの値を適切なPHP型に自動変換
- `hasMany` / `belongsTo` → Eloquentのリレーション定義

---

### Feature 4: 認証機能（AuthController）

```bash
git flow feature start auth-controller
```

**作業内容:**

`app/Http/Controllers/AuthController.php` を作成。

```php
// ログイン処理のポイント
public function login(Request $request)
{
    $credentials = $request->validate([...]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();  // セッション固定攻撃の防止
        return redirect()->intended(route('todos.index'));
    }

    return back()->withErrors(['email' => '認証に失敗しました'])->onlyInput('email');
}

// ログアウト: 3ステップでセキュアに処理
public function logout(Request $request)
{
    Auth::logout();                         // 認証情報を削除
    $request->session()->invalidate();      // セッションを無効化
    $request->session()->regenerateToken(); // CSRFトークンを再生成
    return redirect()->route('login');
}
```

```bash
git add app/Http/Controllers/AuthController.php
git commit -m "feat: 認証コントローラー（登録・ログイン・ログアウト）"

git flow feature finish auth-controller
```

**学習ポイント:**
- `Auth::attempt()` → DBでメール/パスワードを検証し、セッションを生成
- `session()->regenerate()` → ログイン後にセッションIDを変更（セキュリティ）
- `withErrors()` → バリデーションエラーをビューに渡す

---

### Feature 5: TodoController（CRUD）

```bash
git flow feature start todo-controller
```

**作業内容:**

`app/Http/Controllers/TodoController.php` を作成。

```php
// 一覧取得（検索・フィルタ・ページネーション）
public function index(Request $request)
{
    $query = Todo::where('user_id', Auth::id());

    // キーワード検索（タイトルまたは本文）
    if ($request->filled('keyword')) {
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'LIKE', "%{$keyword}%")
              ->orWhere('body', 'LIKE', "%{$keyword}%");
        });
    }

    $todos = $query->orderByDesc('priority')
                   ->paginate(10)
                   ->appends($request->query()); // ページ遷移時も検索条件を維持

    return view('todos.index', compact('todos'));
}
```

```php
// ルートモデルバインディング（Laravelが自動的にIDをモデルに変換）
public function show(Todo $todo)  // {todo} → Todo::find($id) を自動実行
{
    $this->authorizeAccess($todo);  // 他のユーザーのTodoへのアクセス禁止
    return view('todos.show', compact('todo'));
}
```

```php
// ソフトデリート関連
public function destroy(Todo $todo)
{
    $todo->delete();  // deleted_at に日時をセット（物理削除ではない）
    return redirect()->route('todos.index')->with('success', 'ゴミ箱に移動しました');
}

public function restore($id)
{
    Todo::withTrashed()->findOrFail($id)->restore();  // deleted_at を NULL に戻す
}

public function forceDelete($id)
{
    Todo::withTrashed()->findOrFail($id)->forceDelete();  // DBから完全削除
}
```

```bash
git add app/Http/Controllers/TodoController.php
git commit -m "feat: TodoリソースコントローラーのCRUD・ソフトデリート・検索実装"

git flow feature finish todo-controller
```

**学習ポイント:**
- `paginate(10)` → 自動でページネーションを処理
- `appends()` → ページ遷移時にURLパラメータ（検索条件）を維持
- `withTrashed()` → 削除済みレコードも含めて取得

---

### Feature 6: ルーティング設定

```bash
git flow feature start routing
```

**作業内容:**

`routes/web.php` を設定。

```php
// ゲスト専用ルート（ログイン済みはリダイレクト）
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// 認証必須ルート（未ログインは /login にリダイレクト）
Route::middleware('auth')->group(function () {
    // ゴミ箱関連（resourceより前に定義すること）
    Route::get('/todos/trash', [TodoController::class, 'trash'])->name('todos.trash');
    Route::post('/todos/{id}/restore', [TodoController::class, 'restore'])->name('todos.restore');
    Route::delete('/todos/{id}/force-delete', [TodoController::class, 'forceDelete'])->name('todos.force-delete');

    // 1行で7つのCRUDルートを定義
    Route::resource('todos', TodoController::class);
});
```

```bash
# 定義されたルートを確認
./php-ext.sh artisan route:list

git add routes/web.php
git commit -m "feat: ルーティング設定（認証ミドルウェア・リソースルート）"

git flow feature finish routing
```

**学習ポイント:**
- `middleware('guest')` → ログイン済みユーザーを弾く
- `middleware('auth')` → 未認証ユーザーをログインページへ転送
- `Route::resource()` → CRUD 7メソッドを1行で定義
- ゴミ箱ルートは `resource` より**前**に書く（URLの解釈順序のため）

---

### Feature 7: マスターレイアウト

```bash
git flow feature start blade-layout
```

**作業内容:**

`resources/views/layouts/app.blade.php` を作成。

```blade
{{-- レイアウトの継承構造 --}}
{{-- 子ビューは @extends('layouts.app') で継承 --}}
{{-- @yield('content') の箇所に子ビューの内容が挿入される --}}

<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Todo App')</title>
    {{-- Bootstrap 5 CDN（npm不要） --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav>
        @auth
            {{ auth()->user()->name }}さん
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button>ログアウト</button>
            </form>
        @endauth
        @guest
            <a href="{{ route('login') }}">ログイン</a>
        @endguest
    </nav>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')   {{-- 子ビューのコンテンツがここに入る --}}
</body>
</html>
```

```bash
git add resources/views/layouts/
git commit -m "feat: マスターレイアウト（ナビバー・フラッシュメッセージ）"

git flow feature finish blade-layout
```

**学習ポイント:**
- `@extends` / `@section` / `@yield` → Bladeのレイアウト継承の仕組み
- `@auth` / `@guest` → ログイン状態によって表示を切り替え
- `session('success')` → コントローラーの `with('success', ...)` で渡したメッセージを表示

---

### Feature 8: 認証ビュー

```bash
git flow feature start auth-views
```

**作業内容:**

`resources/views/auth/login.blade.php` と `register.blade.php` を作成。

```blade
{{-- 重要なBladeのポイント --}}

@extends('layouts.app')
@section('content')

<form action="{{ route('register') }}" method="POST">
    @csrf  {{-- CSRF攻撃対策トークン（必須） --}}

    <input name="name" value="{{ old('name') }}">  {{-- バリデーション失敗時に入力値を保持 --}}

    @error('name')
        <span class="text-danger">{{ $message }}</span>  {{-- バリデーションエラーを表示 --}}
    @enderror

    <input type="password" name="password_confirmation">
    {{-- 'confirmed'ルールは {フィールド名}_confirmation と一致するか検証する --}}
</form>

@endsection
```

```bash
git add resources/views/auth/
git commit -m "feat: ログイン・会員登録ビュー（バリデーションエラー表示）"

git flow feature finish auth-views
```

**学習ポイント:**
- `@csrf` → Bladeが `<input type="hidden" name="_token" value="...">` を自動生成
- `old('field')` → バリデーション失敗後のリダイレクト時に入力値を復元
- `@error('field') ... @enderror` → 特定フィールドのエラーメッセージを表示

---

### Feature 9: Todo一覧ビュー（検索・ページネーション）

```bash
git flow feature start todo-index-view
```

**作業内容:**

`resources/views/todos/index.blade.php` を作成。

```blade
{{-- GETメソッドで検索（URLにパラメータが付く → ブックマーク可能） --}}
<form action="{{ route('todos.index') }}" method="GET">
    <input name="keyword" value="{{ request('keyword') }}">  {{-- URLから値を復元 --}}
    <select name="status">
        <option value="incomplete" {{ request('status') === 'incomplete' ? 'selected' : '' }}>未完了</option>
    </select>
</form>

{{-- ページネーション情報 --}}
全 {{ $todos->total() }} 件中 {{ $todos->firstItem() }}〜{{ $todos->lastItem() }} 件

@foreach($todos as $todo)
    {{-- DELETEリクエストはHTMLフォームで直接送れないため @method('DELETE') を使う --}}
    <form method="POST" action="{{ route('todos.destroy', $todo) }}">
        @csrf
        @method('DELETE')
        <button>削除</button>
    </form>
@endforeach

{{-- ページリンク（Bootstrap5スタイル） --}}
{{ $todos->links() }}
```

```bash
git add resources/views/todos/index.blade.php
git commit -m "feat: Todo一覧ビュー（キーワード検索・フィルタ・ページネーション）"

git flow feature finish todo-index-view
```

**学習ポイント:**
- 検索フォームは `method="GET"` → URLにパラメータが付くのでブックマークや共有が可能
- `@method('DELETE')` → HTMLは GET/POST しか対応しないため `_method` パラメータで擬似DELETE
- `$todos->links()` → ページネーションリンクを自動生成

---

### Feature 10: Todo詳細・作成・編集ビュー

```bash
git flow feature start todo-crud-views
```

**作業内容:**

`show.blade.php` / `create.blade.php` / `edit.blade.php` を作成。

```blade
{{-- edit.blade.php のポイント --}}
<form action="{{ route('todos.update', $todo) }}" method="POST">
    @csrf
    @method('PUT')  {{-- PUTメソッドの擬似送信 --}}

    {{-- old('title', $todo->title) --}}
    {{-- → 初回表示時はDBの値、バリデーション失敗時は入力値を表示 --}}
    <input name="title" value="{{ old('title', $todo->title) }}">

    {{-- 本文のXSS対策：nl2br + e() で安全に改行を表示 --}}
    <p>{!! nl2br(e($todo->body)) !!}</p>
</form>
```

```bash
git add resources/views/todos/show.blade.php \
        resources/views/todos/create.blade.php \
        resources/views/todos/edit.blade.php
git commit -m "feat: Todo詳細・作成・編集ビュー"

git flow feature finish todo-crud-views
```

---

### Feature 11: ゴミ箱ビュー（ソフトデリート）

```bash
git flow feature start todo-trash-view
```

**作業内容:**

`resources/views/todos/trash.blade.php` を作成。

```blade
{{-- ソフトデリートされたTodo（deleted_at が NULL でないもの） --}}
@foreach($todos as $todo)
    削除日時: {{ $todo->deleted_at->format('Y/m/d H:i') }}

    {{-- 復元ボタン --}}
    <form action="{{ route('todos.restore', $todo->id) }}" method="POST">
        @csrf
        <button>復元</button>
    </form>

    {{-- 完全削除（物理削除）--}}
    <form action="{{ route('todos.force-delete', $todo->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button>完全に削除</button>
    </form>
@endforeach
```

```bash
git add resources/views/todos/trash.blade.php
git commit -m "feat: ゴミ箱ビュー（ソフトデリート・復元・完全削除）"

git flow feature finish todo-trash-view
```

**学習ポイント:**
- `SoftDeletes` トレイトを使うと `delete()` は `deleted_at` に日時をセットするだけ
- `onlyTrashed()` → 削除済みレコードのみ取得
- `restore()` → `deleted_at` を NULL に戻して復元
- `forceDelete()` → DBから完全に削除

---

### Feature 12: ページネーション・シーダー設定

```bash
git flow feature start pagination-and-seed
```

**作業内容:**

`app/Providers/AppServiceProvider.php` にBootstrap5のページネーション設定を追加。
`database/seeders/TodoSeeder.php` でテストデータを作成。

```php
// AppServiceProvider.php
public function boot(): void
{
    // デフォルトはTailwind CSSなのでBootstrap 5に変更
    Paginator::useBootstrapFive();
}
```

```bash
./php-ext.sh artisan db:seed

git add app/Providers/AppServiceProvider.php database/seeders/
git commit -m "feat: BootstrapページネーションUI設定・テストデータシーダー追加"

git flow feature finish pagination-and-seed
```

---

### リリース

```bash
# developブランチの内容をリリース準備ブランチに切り出す
git flow release start 1.0.0

# バージョン番号やREADMEの最終調整を行う
git add README.md
git commit -m "docs: v1.0.0 リリースノートとREADMEを整備"

# mainにマージ（タグも自動作成される）
git flow release finish 1.0.0
```

---

## よく使うartisanコマンド

```bash
# ルート一覧を確認
./php-ext.sh artisan route:list

# マイグレーションの状態確認
./php-ext.sh artisan migrate:status

# DBをリセットしてテストデータを再投入
./php-ext.sh artisan migrate:fresh --seed

# キャッシュをクリア
./php-ext.sh artisan cache:clear
./php-ext.sh artisan config:clear
./php-ext.sh artisan view:clear
```

---

## 参考資料との対応表

| 参考資料のセクション | 実装ファイル | 学ぶこと |
|---|---|---|
| ルーティング | `routes/web.php` | `Route::resource()` / ミドルウェア |
| ビュー | `resources/views/` | `@extends` / `@yield` / `@foreach` |
| コントローラー | `TodoController.php` | リソースコントローラーの7メソッド |
| マイグレーション | `create_todos_table.php` | `softDeletes()` / `foreignId()` |
| モデル | `Todo.php` | `$fillable` / `$casts` / リレーション |
| 検索・フィルタ | `TodoController@index` | `where()` / `LIKE` / `orWhere()` |
| ソフトデリート | `SoftDeletes` トレイト | `onlyTrashed()` / `restore()` |
| ページネーション | `paginate(10)` | `links()` / `appends()` |
| 認証 | `AuthController.php` | `Auth::attempt()` / セッション管理 |
| ミドルウェア | `routes/web.php` | `middleware('auth')` / `middleware('guest')` |
