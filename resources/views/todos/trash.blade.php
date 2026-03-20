@extends('layouts.app')

@section('title', 'ゴミ箱')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-trash"></i> ゴミ箱</h2>
    <a href="{{ route('todos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Todo一覧に戻る
    </a>
</div>

{{-- ソフトデリートの説明 --}}
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>ソフトデリートとは？</strong><br>
    削除したデータは実際にはデータベースから消えず、<code>deleted_at</code> カラムに削除日時が記録されます。
    ゴミ箱から復元することも可能です。「完全に削除」を選ぶと物理的にDBから削除されます。
</div>

@if($todos->isEmpty())
    <div class="alert alert-secondary">
        <i class="bi bi-trash"></i> ゴミ箱は空です。
    </div>
@else
    <p class="text-muted small">{{ $todos->total() }} 件のTodoがゴミ箱にあります</p>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>タイトル</th>
                    <th>優先度</th>
                    <th>削除日時</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todos as $todo)
                    <tr>
                        <td class="text-muted">
                            <s>{{ $todo->title }}</s>
                        </td>
                        <td>
                            <span class="{{ $todo->getPriorityBadgeClass() }}">
                                {{ $todo->getPriorityLabel() }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{-- deleted_at → ソフトデリートした日時 --}}
                            {{ $todo->deleted_at->format('Y/m/d H:i') }}
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                {{-- 復元ボタン --}}
                                <form action="{{ route('todos.restore', $todo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i> 復元
                                    </button>
                                </form>

                                {{-- 完全削除ボタン --}}
                                <form action="{{ route('todos.force-delete', $todo->id) }}" method="POST"
                                      onsubmit="return confirm('完全に削除します。この操作は取り消せません。よろしいですか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash-fill"></i> 完全に削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div class="d-flex justify-content-center">
        {{ $todos->links() }}
    </div>
@endif
@endsection
