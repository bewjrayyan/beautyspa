#!/usr/bin/env bash
# Build a lean hosting upload package (no Git, no Node, no dev junk).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT_DIR="${1:-$ROOT/../fleetcart-hosting-package}"
STAGING="$OUT_DIR/staging"
ARCHIVE="$OUT_DIR/fleetcart-hosting-$(date +%Y%m%d).tar.gz"

echo "==> Preparing hosting package in: $OUT_DIR"

if [[ ! -d "$ROOT/vendor" ]]; then
    echo "vendor/ missing. Run: composer install --no-dev --optimize-autoloader"
    exit 1
fi

if [[ ! -f "$ROOT/public/build/manifest.json" ]]; then
    echo "public/build/ missing. Run: npm install && npm run build"
    exit 1
fi

rm -rf "$STAGING"
mkdir -p "$OUT_DIR"

rsync -a --delete \
    --exclude='.git/' \
    --exclude='.cursor/' \
    --exclude='.idea/' \
    --exclude='.vscode/' \
    --exclude='node_modules/' \
    --exclude='codecanyon-fleetcart-laravel-ecommerce-system-v4.7.11/' \
    --exclude='php-pear/' \
    --exclude='php-extensions/' \
    --exclude='tests/' \
    --exclude='docs/' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='public/storage/media/*' \
    --exclude='public/hot' \
    --exclude='.env' \
    --exclude='.DS_Store' \
    "$ROOT/" "$STAGING/"

mkdir -p "$STAGING/storage/framework/cache/data"
mkdir -p "$STAGING/storage/framework/sessions"
mkdir -p "$STAGING/storage/framework/views"
mkdir -p "$STAGING/storage/logs"
mkdir -p "$STAGING/bootstrap/cache"
mkdir -p "$STAGING/public/storage/media"
touch "$STAGING/storage/logs/.gitkeep"

tar -czf "$ARCHIVE" -C "$STAGING" .
SIZE="$(du -sh "$ARCHIVE" | awk '{print $1}')"

echo "==> Done: $ARCHIVE ($SIZE)"
echo "    Upload and extract to public_html, then open /install"
