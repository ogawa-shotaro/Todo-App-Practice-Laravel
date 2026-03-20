@extends('layouts.app')

@section('title', 'Todo一覧')

@section('content')

{{-- ページヘッダー --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-check"></i> Todo一覧</h2>
    <a href="{{ route('todos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> 新規作成
    </a>
</div>

{{-- =============================================
    検索・フィルタフォーム
    検索条件はGETパラメータとしてURLに付与される
    例: /todos?keyword=買い物&status=incomplete&priority=3
    ============================================= --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-search"></i> 検索・絞り込み
    </div>
    <div class="card-body">
        {{-- method="GET" → URLに検索条件を付与（ブックマーク可能・ページ遷移しても維持） --}}
        <form action="{{ route('todos.index') }}" method="GET">
            <div class="row g-3">
                {{-- キーワード検索 --}}
                <div class="col-md-4">
                    <label class="form-label">キーワード</label>
                    {{--
                        request('keyword') → URLパラメータの値を取得
                        ページをリロードしても入力値が保持される
                    --}}
                    <input type="text"
                           class="form-control"
                           name="keyword"
                           value="{{ request('keyword') }}"
                           placeholder="タイトル・詳細を検索">
                </div>

                {{-- 状態フィルタ --}}
                <div class="col-md-3">
                    <label class="form-label">状態</label>
                    <select class="form-select" name="status">
                        <option value="">すべて</option>
                        <option value="incomplete" {{ request('status') === 'incomplete' ? 'selected' : '' }}>
                            未完了
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                            完了済み
                        </option>
                    </select>
                </div>

                {{-- 優先度フィルタ --}}
                <div class="col-md-3">
                    <label class="form-label">優先度</label>
                    <select class="form-select" name="priority">
                        <option value="">すべて</option>
                        {{--
                            @foreach → PHPのforeachと同じ
                            $priorityLabels はTodoモデルの静的プロパティ
                            コントローラーから渡さずに直接モデルを参照できる
                        --}}
                        @foreach(\App\Models\Todo::$priorityLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('priority') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 検索ボタン --}}
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> 検索
                    </button>
                </div>
            </div>

            {{-- 検索条件をリセットするリンク --}}
            @if(request('keyword') || request('status') || request('priority'))
                <div class="mt-2">
                    <a href="{{ route('todos.index') }}" class="text-muted small">
                        <i class="bi bi-x-circle"></i> 検索条件をリセット
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- =============================================
    Todo一覧テーブル
    ============================================= --}}
@if($todos->isEmpty())
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        @if(request('keyword') || request('status') || request('priority'))
            検索条件に一致するTodoが見つかりませんでした。
        @else
            まだTodoがありません。<a href="{{ route('todos.create') }}">最初のTodoを作成しましょう！</a>
        @endif
    </div>
@else
    {{-- 件数表示 --}}
    <p class="text-muted small">
        全 {{ $todos->total() }} 件中
        {{ $todos->firstItem() }}〜{{ $todos->lastItem() }} 件を表示
    </p>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>状態</th>
                    <th>タイトル</th>
                    <th>優先度</th>
                    <th>期限</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                {{--
                    @foreach($todos as $todo) → $todos はページネーション済みコレクション
                    通常のコレクションと同様に foreach で扱える
                --}}
                @foreach($todos as $todo)
                    {{--
                        $todo->isOverdue() → 期限切れかチェック（モデルのカスタムメソッド）
                        条件に応じてクラスを切り替えてスタイルを変える
                    --}}
                    <tr class="{{ $todo->is_completed ? 'table-secondary' : ($todo->isOverdue() ? 'overdue' : '') }}">
                        <td>
                            {{-- 完了状態のトグルボタン --}}
                            <form action="{{ route('todos.toggle', $todo) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm {{ $todo->is_completed ? 'btn-success' : 'btn-outline-secondary' }}"
                                        title="{{ $todo->is_completed ? '未完了に戻す' : '完了にする' }}">
                                    <i class="bi {{ $todo->is_completed ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('todos.show', $todo) }}"
                               class="{{ $todo->is_completed ? 'completed-title' : '' }} text-decoration-none fw-bold">
                                {{ $todo->title }}
                            </a>
                            @if($todo->isOverdue())
                                <span class="badge bg-danger ms-1">期限切れ</span>
                            @endif
                        </td>
                        <td>
                            {{-- getPriorityBadgeClass() → 優先度に応じたBootstrapクラスを返す --}}
                            <span class="{{ $todo->getPriorityBadgeClass() }}">
                                {{ $todo->getPriorityLabel() }}
                            </span>
                        </td>
                        <td>
                            @if($todo->due_date)
                                {{--
                                    Carbon の format() メソッドで日付をフォーマット
                                    Y=年, m=月, d=日
                                --}}
                                {{ $todo->due_date->format('Y/m/d') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                {{-- 詳細ボタン --}}
                                <a href="{{ route('todos.show', $todo) }}"
                                   class="btn btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                {{-- 編集ボタン --}}
                                <a href="{{ route('todos.edit', $todo) }}"
                                   class="btn btn-outline-warning" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                {{-- 削除ボタン（確認ダイアログ付き） --}}
                                <form action="{{ route('todos.destroy', $todo) }}" method="POST"
                                      onsubmit="return confirm('このTodoをゴミ箱に移しますか？')">
                                    @csrf
                                    {{--
                                        @method('DELETE') → HTMLフォームはGET/POSTしか使えないため
                                        Laravelは _method パラメータで擬似的にHTTPメソッドを指定できる
                                        これを「メソッドスプーフィング」という
                                    --}}
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="削除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- =============================================
        ページネーション
        $todos->links() → ページ切り替えリンクを自動生成
        appends() はコントローラー側で設定済み（検索条件を維持）
        ============================================= --}}
    <div class="d-flex justify-content-center">
        {{ $todos->links() }}
    </div>
@endif

@endsection
