@extends('layouts.app')

@section('title', $todo->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">

        {{-- パンくずリスト --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('todos.index') }}">Todo一覧</a></li>
                <li class="breadcrumb-item active">詳細</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-card-text"></i> Todo詳細
                </h4>
                {{-- 操作ボタン --}}
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('todos.edit', $todo) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> 編集
                    </a>
                    <form action="{{ route('todos.destroy', $todo) }}" method="POST"
                          onsubmit="return confirm('このTodoをゴミ箱に移しますか？')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash"></i> 削除
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">

                {{-- 完了/未完了の表示 --}}
                <div class="mb-4">
                    @if($todo->is_completed)
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-check-circle-fill"></i> 完了済み
                        </span>
                    @else
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="bi bi-clock"></i> 未完了
                        </span>
                        @if($todo->isOverdue())
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-exclamation-triangle"></i> 期限切れ
                            </span>
                        @endif
                    @endif
                </div>

                {{-- タイトル --}}
                <div class="mb-3">
                    <label class="text-muted small">タイトル</label>
                    <h3 class="{{ $todo->is_completed ? 'completed-title' : '' }}">
                        {{ $todo->title }}
                    </h3>
                </div>

                {{-- 詳細 --}}
                @if($todo->body)
                    <div class="mb-3">
                        <label class="text-muted small">詳細</label>
                        {{--
                            nl2br() → 改行文字を <br> に変換
                            e() → XSS対策でHTMLエスケープ（htmlspecialchars と同等）
                            {!! !!} → HTMLをそのまま出力（エスケープなし）
                            ※ nl2br+e() の組み合わせで安全に改行を表示できる
                        --}}
                        <p class="border rounded p-3 bg-light">{!! nl2br(e($todo->body)) !!}</p>
                    </div>
                @endif

                {{-- 優先度・期限 --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">優先度</label>
                        <div>
                            <span class="{{ $todo->getPriorityBadgeClass() }} fs-6">
                                {{ $todo->getPriorityLabel() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">期限</label>
                        <div>
                            @if($todo->due_date)
                                <span class="{{ $todo->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                    <i class="bi bi-calendar"></i>
                                    {{ $todo->due_date->format('Y年m月d日') }}
                                    @if($todo->isOverdue())
                                        （{{ abs($todo->due_date->diffInDays()) }}日経過）
                                    @elseif(!$todo->is_completed)
                                        （あと {{ $todo->due_date->diffInDays() }} 日）
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">設定なし</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 作成日・更新日 --}}
                <div class="row text-muted small">
                    <div class="col-md-6">
                        <i class="bi bi-calendar-plus"></i>
                        作成日: {{ $todo->created_at->format('Y/m/d H:i') }}
                    </div>
                    <div class="col-md-6">
                        <i class="bi bi-calendar-check"></i>
                        更新日: {{ $todo->updated_at->format('Y/m/d H:i') }}
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex gap-2">
                {{-- 完了状態のトグルボタン --}}
                <form action="{{ route('todos.toggle', $todo) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="btn {{ $todo->is_completed ? 'btn-outline-secondary' : 'btn-success' }}">
                        @if($todo->is_completed)
                            <i class="bi bi-arrow-counterclockwise"></i> 未完了に戻す
                        @else
                            <i class="bi bi-check-circle"></i> 完了にする
                        @endif
                    </button>
                </form>
                <a href="{{ route('todos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> 一覧に戻る
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
