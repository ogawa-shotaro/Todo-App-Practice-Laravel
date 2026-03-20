@extends('layouts.app')

@section('title', 'Todo編集')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('todos.index') }}">Todo一覧</a></li>
                <li class="breadcrumb-item"><a href="{{ route('todos.show', $todo) }}">詳細</a></li>
                <li class="breadcrumb-item active">編集</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Todoを編集</h4>
            </div>
            <div class="card-body">

                {{--
                    action="{{ route('todos.update', $todo) }}" → PUT /todos/{id}
                    HTMLフォームはPUTをサポートしないので @method('PUT') で擬似的に指定
                --}}
                <form action="{{ route('todos.update', $todo) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- タイトル --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">
                            タイトル <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               {{--
                                   old('title', $todo->title) →
                                   バリデーションエラー時は入力値を維持
                                   初回表示時はDBの値を表示
                               --}}
                               value="{{ old('title', $todo->title) }}"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 詳細 --}}
                    <div class="mb-3">
                        <label for="body" class="form-label fw-bold">詳細</label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body"
                                  name="body"
                                  rows="4">{{ old('body', $todo->body) }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- 優先度 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="priority" class="form-label fw-bold">優先度</label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority"
                                        name="priority">
                                    @foreach($priorityLabels as $value => $label)
                                        <option value="{{ $value }}"
                                                {{ old('priority', $todo->priority) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- 期限 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="due_date" class="form-label fw-bold">期限</label>
                                <input type="date"
                                       class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date"
                                       name="due_date"
                                       value="{{ old('due_date', $todo->due_date?->format('Y-m-d')) }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- 完了状態 --}}
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">状態</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="is_completed"
                                           name="is_completed"
                                           value="1"
                                           {{ old('is_completed', $todo->is_completed) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_completed">
                                        完了済みにする
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> 更新する
                        </button>
                        <a href="{{ route('todos.show', $todo) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
