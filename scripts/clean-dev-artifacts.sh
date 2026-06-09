#!/usr/bin/env bash
# Remove local dev artifacts that should not ship to customers.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Removing obsolete local bundles..."
rm -rf \
    codecanyon-fleetcart-laravel-ecommerce-system-v4.7.11 \
    php-pear \
    php-extensions

echo "==> Removing node_modules (run npm install when developing)..."
rm -rf node_modules

echo "==> Clearing dev caches (media uploads are kept)..."
find storage/logs -type f -name '*.log' -delete 2>/dev/null || true
find storage/framework/cache/data -mindepth 1 -delete 2>/dev/null || true
find storage/framework/sessions -mindepth 1 -type f -delete 2>/dev/null || true
find storage/framework/views -mindepth 1 -type f -delete 2>/dev/null || true

echo "==> Current project size:"
du -sh .

echo "==> Done. For customer hosting zip, run: bash scripts/build-hosting-package.sh"
