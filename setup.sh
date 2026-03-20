#!/bin/bash
# =============================================================
# Todo App セットアップスクリプト
#
# 使い方:
#   ./setup.sh          # 初回セットアップ（マイグレーション + テストデータ投入）
#   ./setup.sh --fresh  # DBをリセットして再セットアップ
#   ./setup.sh --ext    # PHP拡張だけ再インストール（PCの再起動後など）
# =============================================================

set -e  # エラーが起きたら即座に停止

# ---- 色付き出力ヘルパー ----
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'  # No Color

info()    { echo -e "${GREEN}[INFO]${NC} $1"; }
warning() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
step()    { echo -e "\n${GREEN}>>> $1${NC}"; }

# ---- オプション解析 ----
FRESH=false
EXT_ONLY=false
for arg in "$@"; do
  case $arg in
    --fresh)   FRESH=true ;;
    --ext)     EXT_ONLY=true ;;
    --help|-h)
      echo "使い方: $0 [--fresh] [--ext]"
      echo "  --fresh  DBをリセットしてテストデータを再投入"
      echo "  --ext    PHP拡張の再インストールのみ実行（PC再起動後に使用）"
      exit 0 ;;
  esac
done

# ============================================================
# Step 1: PHP拡張のセットアップ
# ============================================================
step "Step 1: PHP拡張のセットアップ"

PHP_EXT_DIR="/tmp/php-ext"
PHP_INI_DIR="/home/ogawa/.php"

setup_php_extensions() {
  # 拡張ファイルが既に存在していればスキップ
  if [ -f "${PHP_EXT_DIR}/usr/lib/php/20230831/pdo_sqlite.so" ]; then
    info "PHP拡張ファイルは既に存在します（スキップ）"
    return
  fi

  info "PHP拡張パッケージをダウンロードしています..."
  mkdir -p "$PHP_EXT_DIR"
  cd /tmp

  # apt-get download でsudo不要でパッケージをダウンロード
  apt-get download php8.3-xml php8.3-sqlite3 2>/dev/null || \
    error "パッケージのダウンロードに失敗しました。ネットワーク接続を確認してください。"

  info "拡張ファイルを展開しています..."
  dpkg --extract /tmp/php8.3-xml_*.deb "$PHP_EXT_DIR"
  dpkg --extract /tmp/php8.3-sqlite3_*.deb "$PHP_EXT_DIR"

  cd - > /dev/null
  info "PHP拡張ファイルの展開が完了しました"
}

generate_php_ini() {
  info "カスタム php.ini を生成しています..."
  mkdir -p "$PHP_INI_DIR"

  # システムiniをベースにコピー
  cp /etc/php/8.3/cli/php.ini "${PHP_INI_DIR}/php.ini"

  # conf.d の内容を取り込む（pdo.so などが含まれる）
  for f in /etc/php/8.3/cli/conf.d/*.ini; do
    echo "; --- from $f ---" >> "${PHP_INI_DIR}/php.ini"
    cat "$f" >> "${PHP_INI_DIR}/php.ini"
  done

  # 追加拡張を末尾に追記（pdo.so より後に読み込まれるため正常動作する）
  cat >> "${PHP_INI_DIR}/php.ini" << 'EOF'

; --- 追加拡張（php8.3-xml / php8.3-sqlite3 より展開） ---
extension=/tmp/php-ext/usr/lib/php/20230831/xml.so
extension=/tmp/php-ext/usr/lib/php/20230831/dom.so
extension=/tmp/php-ext/usr/lib/php/20230831/simplexml.so
extension=/tmp/php-ext/usr/lib/php/20230831/xmlreader.so
extension=/tmp/php-ext/usr/lib/php/20230831/xmlwriter.so
extension=/tmp/php-ext/usr/lib/php/20230831/sqlite3.so
extension=/tmp/php-ext/usr/lib/php/20230831/pdo_sqlite.so
EOF

  info "php.ini の生成が完了しました → ${PHP_INI_DIR}/php.ini"
}

setup_php_extensions
generate_php_ini

# --ext オプション時はここで終了
if $EXT_ONLY; then
  info "PHP拡張のセットアップが完了しました。"
  echo ""
  echo "サーバーを起動するには:"
  echo "  ./php-ext.sh artisan serve --host=0.0.0.0 --port=8000"
  exit 0
fi

# php-ext.sh 経由でPHPを実行する関数
php_ext() {
  PHPRC="$PHP_INI_DIR" PHP_INI_SCAN_DIR="" php "$@"
}

# 動作確認
php_ext -r "echo extension_loaded('pdo_sqlite') ? '' : '';" 2>/dev/null || \
  error "pdo_sqlite 拡張が読み込めません。setup.sh --ext を再実行してください。"

# ============================================================
# Step 2: Composerパッケージのインストール
# ============================================================
step "Step 2: Composerパッケージのインストール"

if [ ! -d "vendor" ]; then
  info "vendor ディレクトリが存在しません。composer install を実行します..."
  PHPRC="$PHP_INI_DIR" PHP_INI_SCAN_DIR="" composer install --no-interaction
else
  info "vendor ディレクトリが既に存在します（スキップ）"
fi

# ============================================================
# Step 3: .envの設定
# ============================================================
step "Step 3: .env の設定"

if [ ! -f ".env" ]; then
  info ".env ファイルを作成します..."
  cp .env.example .env
  info ".env.example をコピーしました"
else
  info ".env ファイルは既に存在します（スキップ）"
fi

# APP_KEY が空なら生成
if grep -q "APP_KEY=$" .env; then
  info "アプリケーションキーを生成します..."
  php_ext artisan key:generate
else
  info "APP_KEY は既に設定済みです（スキップ）"
fi

# ============================================================
# Step 4: データベースのセットアップ
# ============================================================
step "Step 4: データベースのセットアップ"

DB_FILE="database/database.sqlite"

if $FRESH; then
  warning "--fresh オプション: データベースをリセットします"
  rm -f "$DB_FILE"
fi

if [ ! -f "$DB_FILE" ]; then
  info "SQLite データベースファイルを作成します..."
  touch "$DB_FILE"
fi

if $FRESH; then
  info "マイグレーションをリセットして再実行します..."
  php_ext artisan migrate:fresh --force
else
  info "マイグレーションを実行します..."
  php_ext artisan migrate --force
fi

# ============================================================
# Step 5: テストデータの投入
# ============================================================
step "Step 5: テストデータの投入"

if $FRESH; then
  info "テストデータを投入します..."
  php_ext artisan db:seed --force
else
  # 初回セットアップ時のみ投入（usersテーブルが空なら実行）
  USER_COUNT=$(php_ext artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
  if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    info "テストデータを投入します..."
    php_ext artisan db:seed --force
  else
    info "データが既に存在します（スキップ）"
  fi
fi

# ============================================================
# 完了メッセージ
# ============================================================
echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  セットアップが完了しました！${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo "テストアカウント:"
echo "  Email   : test@example.com"
echo "  Password: password123"
echo ""
echo "サーバーを起動するには:"
echo "  ./php-ext.sh artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "ブラウザでアクセス:"
echo "  http://localhost:8000"
echo ""
