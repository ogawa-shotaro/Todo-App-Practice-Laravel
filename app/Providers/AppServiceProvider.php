<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * アプリケーション起動時に実行される処理
     */
    public function boot(): void
    {
        // ページネーションのデザインをBootstrap 5に設定
        // デフォルトはTailwind CSSなので明示的にBootstrapを指定する
        // これにより $todos->links() が Bootstrap のスタイルで表示される
        Paginator::useBootstrapFive();
    }
}
