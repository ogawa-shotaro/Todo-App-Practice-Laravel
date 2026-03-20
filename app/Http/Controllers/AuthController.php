<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 認証コントローラー
 *
 * ユーザー登録・ログイン・ログアウトを管理します。
 *
 * 処理の流れ:
 * 1. ユーザー登録: フォーム表示 → バリデーション → DB保存 → 自動ログイン
 * 2. ログイン:     フォーム表示 → バリデーション → 認証 → セッション生成
 * 3. ログアウト:   セッション破棄 → トップページへ
 */
class AuthController extends Controller
{
    // =============================================
    // 会員登録
    // =============================================

    /**
     * 登録フォームを表示する
     * GET /register
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * ユーザー登録処理
     * POST /register
     */
    public function register(Request $request)
    {
        // バリデーション（入力値の検証）
        // 'required'  → 必須入力
        // 'email'     → メールアドレス形式
        // 'unique'    → DBに同じ値がないこと
        // 'confirmed' → password_confirmation フィールドと一致すること
        // 'min:8'     → 最低8文字
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ], [
            // バリデーションエラーメッセージを日本語に
            'name.required'      => '名前を入力してください',
            'name.max'           => '名前は255文字以内で入力してください',
            'email.required'     => 'メールアドレスを入力してください',
            'email.email'        => '正しいメールアドレス形式で入力してください',
            'email.unique'       => 'このメールアドレスは既に使用されています',
            'password.required'  => 'パスワードを入力してください',
            'password.min'       => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ]);

        // Userモデルを使ってDBにユーザーを保存
        // $fillable に 'password' があり、$castsで 'hashed' に設定されているので
        // パスワードは自動的にBcryptハッシュ化される
        $user = User::create($validated);

        // 登録後に自動でログイン状態にする
        // Auth::login() → セッションにユーザー情報を保存
        Auth::login($user);

        // フラッシュメッセージをセッションに保存してリダイレクト
        // with('success', ...) → 1回だけ表示されるメッセージ（次のリクエストで消える）
        return redirect()->route('todos.index')
            ->with('success', 'ユーザー登録が完了しました！');
    }

    // =============================================
    // ログイン
    // =============================================

    /**
     * ログインフォームを表示する
     * GET /login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     * POST /login
     */
    public function login(Request $request)
    {
        // バリデーション
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => '正しいメールアドレス形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // Auth::attempt() → メールとパスワードを検証
        // 成功すると true を返し、セッションにログイン情報を保存
        // 'remember' → 「ログイン状態を保持する」チェックボックスの値
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // セッション固定攻撃（Session Fixation）を防ぐために
            // ログイン後はセッションIDを新しく生成する
            $request->session()->regenerate();

            // intended() → ログイン前にアクセスしようとしたURLにリダイレクト
            // 指定したURLがなければ '/todos' にリダイレクト
            return redirect()->intended(route('todos.index'))
                ->with('success', 'ログインしました！');
        }

        // 認証失敗: エラーメッセージと入力値を返す
        // back() → 前のページ（ログインフォーム）に戻る
        // withErrors() → バリデーションエラーとして表示
        // onlyInput('email') → パスワード以外の入力値を保持
        return back()
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません'])
            ->onlyInput('email');
    }

    // =============================================
    // ログアウト
    // =============================================

    /**
     * ログアウト処理
     * POST /logout
     */
    public function logout(Request $request)
    {
        // 3ステップのログアウト処理（セキュリティのため）

        // 1. セッションの認証情報を削除
        Auth::logout();

        // 2. セッション自体を無効化（セッションIDを削除）
        $request->session()->invalidate();

        // 3. CSRFトークンを再生成（クロスサイトリクエストフォージェリ対策）
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'ログアウトしました');
    }
}
