@extends('layouts.app')

@section('title', 'Todo作成')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> 新しいTodoを作成</h4>
            </div>
            <div class="card-body">

                {{--
                    action="{{ route('todos.store') }}" → POST /todos に送信
                    TodoController@store() が処理する
                --}}
                <form action="{{ route('todos.store') }}" method="POST">
                    @csrf

                    {{-- タイトル（必須） --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">
                            タイトル <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               placeholder="例: 買い物に行く、報告書を作成する"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 詳細（任意） --}}
                    <div class="mb-3">
                        <label for="body" class="form-label fw-bold">詳細</label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body"
                                  name="body"
                                  rows="4"
                                  placeholder="詳細な内容を記入してください（任意）">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- 優先度 --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label fw-bold">
                                    優先度 <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority"
                                        name="priority">
                                    {{--
                                        $priorityLabels はコントローラーから渡された配列
                                        [1 => '低', 2 => '中', 3 => '高']
                                    --}}
                                    @foreach($priorityLabels as $value => $label)
                                        <option value="{{ $value }}"
                                                {{-- old('priority', 2) → 初期値は2（中）--}}
                                                {{ old('priority', 2) == $value ? 'selected' : '' }}>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label fw-bold">期限</label>
                                <input type="date"
                                       class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date"
                                       name="due_date"
                                       value="{{ old('due_date') }}"
                                       {{-- min属性でHTML側でも過去日付を制限 --}}
                                       min="{{ date('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ボタン --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 作成する
                        </button>
                        <a href="{{ route('todos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
