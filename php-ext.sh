#!/bin/bash
# PHP拡張機能を有効化してartisanコマンドを実行するラッパースクリプト
#
# 使用方法:
#   ./php-ext.sh artisan serve    # 開発サーバー起動
#   ./php-ext.sh artisan migrate  # マイグレーション実行
#
# なぜこのスクリプトが必要か:
#   このシステムには php8.3-xml・php8.3-sqlite3 パッケージがインストールされていないため、
#   dom・pdo_sqlite 拡張がデフォルトでは使えない。
#   /home/ogawa/.php/php.ini にシステムのiniを取り込んだ上でこれらの拡張を追加している。
#
# 環境変数の役割:
#   PHPRC=/home/ogawa/.php
#     → PHP が /home/ogawa/.php/php.ini を設定ファイルとして使用する
#     → export することで「artisan serve が起動する php8.3 -S」サブプロセスにも引き継がれる
#
#   PHP_INI_SCAN_DIR=""
#     → /etc/php/8.3/cli/conf.d/*.ini のスキャンを無効化
#     → php.ini 側でシステムのconf.dを取り込み済みなので、二重ロードを防ぐため無効化

export PHPRC=/home/ogawa/.php
export PHP_INI_SCAN_DIR=""

exec php "$@"
