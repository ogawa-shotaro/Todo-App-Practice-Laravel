<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- @yield('title') → 子ビューで @section('title') で上書きできる --}}
    <title>@yield('title', 'Todo App') - Todo管理</title>

    {{-- Bootstrap 5 CSS（CDN経由）→ npm不要でスタイルを適用できる --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* 期限切れのTodoを赤く表示 */
        .overdue {
            background-color: #fff5f5;
        }
        /* 完了済みTodoのタイトルに取り消し線 */
        .completed-title {
            text-decoration: line-through;
            color: #6c757d;
        }
        /* ナビバーのスタイル */
        .navbar-brand {
            font-weight: bold;
        }
    </style>

    {{-- 子ビューが追加のCSSを定義できる場所 --}}
    @stack('styles')
</head>
<body class="bg-light">

    {{-- ナビゲーションバー --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            {{-- ブランドロゴ/名称 --}}
            <a class="navbar-brand" href="{{ route('todos.index') }}">
                <i class="bi bi-check2-square"></i> Todo App
            </a>

            {{-- ハンバーガーメニュー（モバイル用） --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    {{--
                        @auth ディレクティブ → ログイン済みの場合に表示
                        @guest ディレクティブ → 未ログインの場合に表示
                    --}}
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('todos.index') }}">
                                <i class="bi bi-list-check"></i> Todo一覧
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('todos.create') }}">
                                <i class="bi bi-plus-circle"></i> 新規作成
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('todos.trash') }}">
                                <i class="bi bi-trash"></i> ゴミ箱
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown">
                                {{-- auth()->user() → 現在ログイン中のUserモデル --}}
                                <i class="bi bi-person-circle"></i>
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    {{-- ログアウトはPOSTリクエスト（CSRFトークンが必要） --}}
                                    <form action="{{ route('logout') }}" method="POST">
                                        {{-- @csrf → CSRFトークンを埋め込む（CSRF攻撃対策） --}}
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> ログアウト
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">ログイン</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">会員登録</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- メインコンテンツ --}}
    <main class="container py-4">

        {{-- フラッシュメッセージの表示 --}}
        {{-- session('success') → with('success', ...) で設定した1回限りのメッセージ --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- @yield('content') → 子ビューの @section('content') の内容がここに入る --}}
        @yield('content')
    </main>

    {{-- Bootstrap 5 JavaScript（CDN経由） --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- 子ビューが追加のJavaScriptを定義できる場所 --}}
    @stack('scripts')
</body>
</html>
