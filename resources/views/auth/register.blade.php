{{--
    @extends → 継承するレイアウトを指定
    このビューは layouts/app.blade.php をベースとして使う
--}}
@extends('layouts.app')

{{--
    @section('title') → layouts/app.blade.php の @yield('title') に入る内容を定義
--}}
@section('title', '会員登録')

{{--
    @section('content') ... @endsection → メインコンテンツを定義
--}}
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> 会員登録</h4>
            </div>
            <div class="card-body">

                {{--
                    フォームの action → 送信先URL（route() ヘルパーで名前付きルートを使用）
                    method="POST" → POSTリクエスト
                --}}
                <form action="{{ route('register') }}" method="POST">
                    {{--
                        @csrf → CSRFトークンを生成する Blade ディレクティブ
                        フォーム送信時にCSRF攻撃を防ぐための隠しフィールドを追加する
                        これがないとLaravelが 419 エラーを返す
                    --}}
                    @csrf

                    {{-- 名前フィールド --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">名前 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               {{--
                                   old('name') → バリデーション失敗時に前回入力値を再表示
                                   入力ミスでフォームが戻ったとき、もう一度入力し直すのを防ぐ
                               --}}
                               value="{{ old('name') }}"
                               placeholder="山田 太郎"
                               required>
                        {{--
                            @error('name') → 'name' フィールドのバリデーションエラーがある場合に表示
                            $message → エラーメッセージの内容
                        --}}
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- メールアドレスフィールド --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">メールアドレス <span class="text-danger">*</span></label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="example@email.com"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- パスワードフィールド --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">パスワード <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="8文字以上"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- パスワード確認フィールド --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">パスワード確認 <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               {{--
                                   name="password_confirmation" が重要！
                                   バリデーションの 'confirmed' ルールは
                                   {フィールド名}_confirmation というフィールドと比較する
                               --}}
                               name="password_confirmation"
                               placeholder="同じパスワードを入力"
                               required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> 登録する
                        </button>
                    </div>
                </form>

                <hr>
                <p class="text-center text-muted">
                    既にアカウントをお持ちの方は
                    <a href="{{ route('login') }}">こちらからログイン</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
