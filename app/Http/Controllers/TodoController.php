<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Todoリソースコントローラー
 *
 * リソースコントローラーはCRUDの7つのアクションを持つ標準的なコントローラーです。
 * routes/web.php で Route::resource('todos', TodoController::class) と定義すると
 * 以下の7つのルートが自動生成されます:
 *
 * GET    /todos          → index()   一覧表示
 * GET    /todos/create   → create()  作成フォーム表示
 * POST   /todos          → store()   作成処理
 * GET    /todos/{id}     → show()    詳細表示
 * GET    /todos/{id}/edit → edit()   編集フォーム表示
 * PUT    /todos/{id}     → update()  更新処理
 * DELETE /todos/{id}     → destroy() 削除処理
 */
class TodoController extends Controller
{
    /**
     * Todo一覧表示
     * GET /todos
     *
     * 機能:
     * - ログインユーザーのTodoのみ表示（認可）
     * - キーワード検索
     * - 状態フィルタ（全て/未完了/完了済み）
     * - 優先度フィルタ
     * - ページネーション（1ページ10件）
     */
    public function index(Request $request)
    {
        // auth()->id() → 現在ログイン中のユーザーID
        // where('user_id', ...) → そのユーザーのTodoのみ取得
        $query = Todo::where('user_id', Auth::id());

        // =============================================
        // 検索・フィルタ処理
        // =============================================

        // キーワード検索（タイトルまたは本文に含まれるもの）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            // LIKE検索: % はワイルドカード（任意の文字列）
            // where → AND条件、orWhere → OR条件
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('body', 'LIKE', "%{$keyword}%");
            });
        }

        // 状態フィルタ
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                // 完了済みのみ
                $query->where('is_completed', true);
            } elseif ($request->status === 'incomplete') {
                // 未完了のみ
                $query->where('is_completed', false);
            }
        }

        // 優先度フィルタ
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // 並び順: デフォルトは「作成日の新しい順」
        // → 優先度が高いものを先に、同じ優先度なら期限が近いものを先に表示するオプションも考えられる
        $query->orderByDesc('priority')->orderBy('due_date');

        // =============================================
        // ページネーション
        // =============================================
        // paginate(10) → 10件ずつ分割
        // appends() → ページ切り替え時も検索条件を維持
        $todos = $query->paginate(10)->appends($request->query());

        // コンパクト記法: compact('todos') は ['todos' => $todos] と同じ
        return view('todos.index', compact('todos'));
    }

    /**
     * Todo作成フォーム表示
     * GET /todos/create
     */
    public function create()
    {
        // 優先度の選択肢をビューに渡す
        $priorityLabels = Todo::$priorityLabels;
        return view('todos.create', compact('priorityLabels'));
    }

    /**
     * Todo作成処理
     * POST /todos
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string|max:2000',
            'priority'     => 'required|integer|in:1,2,3',
            'due_date'     => 'nullable|date|after_or_equal:today',
        ], [
            'title.required'        => 'タイトルを入力してください',
            'title.max'             => 'タイトルは255文字以内で入力してください',
            'body.max'              => '詳細は2000文字以内で入力してください',
            'priority.required'     => '優先度を選択してください',
            'priority.in'           => '優先度は1・2・3のいずれかを選択してください',
            'due_date.date'         => '正しい日付形式で入力してください',
            'due_date.after_or_equal' => '期限は今日以降の日付を指定してください',
        ]);

        // ログインユーザーIDを追加して保存
        // array_merge → 配列を結合
        $validated['user_id'] = Auth::id();

        // Todo::create() → $fillable で許可されたカラムだけ一括保存
        Todo::create($validated);

        // redirect()->route('名前') → 名前付きルートにリダイレクト
        return redirect()->route('todos.index')
            ->with('success', 'Todoを作成しました！');
    }

    /**
     * Todo詳細表示
     * GET /todos/{todo}
     *
     * {todo} → ルートモデルバインディング
     * LaravelがTodoモデルのIDを自動的に $todo に変換してくれる
     */
    public function show(Todo $todo)
    {
        // 認可: ログインユーザー以外のTodoへのアクセスを禁止
        $this->authorizeAccess($todo);

        return view('todos.show', compact('todo'));
    }

    /**
     * Todo編集フォーム表示
     * GET /todos/{todo}/edit
     */
    public function edit(Todo $todo)
    {
        $this->authorizeAccess($todo);

        $priorityLabels = Todo::$priorityLabels;
        return view('todos.edit', compact('todo', 'priorityLabels'));
    }

    /**
     * Todo更新処理
     * PUT /todos/{todo}
     */
    public function update(Request $request, Todo $todo)
    {
        $this->authorizeAccess($todo);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string|max:2000',
            'priority'     => 'required|integer|in:1,2,3',
            'due_date'     => 'nullable|date',
            'is_completed' => 'boolean',
        ], [
            'title.required'    => 'タイトルを入力してください',
            'title.max'         => 'タイトルは255文字以内で入力してください',
            'body.max'          => '詳細は2000文字以内で入力してください',
            'priority.required' => '優先度を選択してください',
        ]);

        // チェックボックスは未チェックの場合リクエストに含まれないため
        // 明示的に false をセットする
        $validated['is_completed'] = $request->boolean('is_completed');

        // $todo->update() → 変更があったカラムのみ UPDATE クエリを発行
        $todo->update($validated);

        return redirect()->route('todos.show', $todo)
            ->with('success', 'Todoを更新しました！');
    }

    /**
     * Todo削除処理（ソフトデリート）
     * DELETE /todos/{todo}
     *
     * SoftDeletesトレイトを使用しているため、
     * delete() を呼ぶと物理削除ではなく
     * deleted_at カラムに現在時刻が入る（論理削除）
     */
    public function destroy(Todo $todo)
    {
        $this->authorizeAccess($todo);

        // ソフトデリート実行
        // deleted_at = 現在時刻 → 通常のクエリでは取得されなくなる
        $todo->delete();

        return redirect()->route('todos.index')
            ->with('success', 'Todoをゴミ箱に移動しました');
    }

    /**
     * ゴミ箱（ソフトデリートしたTodo一覧）
     * GET /todos/trash
     */
    public function trash()
    {
        // onlyTrashed() → deleted_at が NULL でないもの（削除済み）を取得
        $todos = Todo::onlyTrashed()
            ->where('user_id', Auth::id())
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('todos.trash', compact('todos'));
    }

    /**
     * Todoを復元（ソフトデリートを取り消す）
     * POST /todos/{id}/restore
     */
    public function restore($id)
    {
        // withTrashed() → 削除済みも含めてモデルを取得
        $todo = Todo::withTrashed()->findOrFail($id);
        $this->authorizeAccess($todo);

        // restore() → deleted_at を NULL に戻す
        $todo->restore();

        return redirect()->route('todos.trash')
            ->with('success', 'Todoを復元しました！');
    }

    /**
     * Todoを完全削除（物理削除）
     * DELETE /todos/{id}/force-delete
     */
    public function forceDelete($id)
    {
        // withTrashed() → 削除済みも含めて取得
        $todo = Todo::withTrashed()->findOrFail($id);
        $this->authorizeAccess($todo);

        // forceDelete() → DBから完全に削除
        $todo->forceDelete();

        return redirect()->route('todos.trash')
            ->with('success', 'Todoを完全に削除しました');
    }

    /**
     * 完了状態をトグル（完了 ↔ 未完了の切り替え）
     * POST /todos/{todo}/toggle
     */
    public function toggle(Todo $todo)
    {
        $this->authorizeAccess($todo);

        // ! 演算子で true/false を反転
        $todo->update(['is_completed' => !$todo->is_completed]);

        $message = $todo->is_completed ? 'Todoを完了しました！' : 'Todoを未完了に戻しました';

        return back()->with('success', $message);
    }

    /**
     * アクセス権限チェック
     * ログインユーザーのTodoかどうかを確認するプライベートメソッド
     *
     * @param  Todo  $todo
     * @return void
     */
    private function authorizeAccess(Todo $todo): void
    {
        // ログインユーザーのIDとTodoのuser_idが一致しない場合は403エラー
        if ($todo->user_id !== Auth::id()) {
            abort(403, '不正なアクセスです');
        }
    }
}
