#!/usr/bin/env bash
#
# Idempotent application bootstrap for BeautySpa (AestheticCart) Cloud Agents.
#
# Ensures the local .env, MySQL database/user, and installed application data
# exist. Safe to run repeatedly: it detects an already-installed environment
# and leaves it untouched, and only seeds data when the database is empty.
#
# Invoked from the environment `install` step. Because `install` runs before
# `start` in the Cloud Agent lifecycle, this script also makes sure MariaDB is
# up before touching the database.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${REPO_ROOT}"

DB_NAME="${BEAUTYSPA_DB_NAME:-beautyspa}"
DB_USER="${BEAUTYSPA_DB_USER:-beautyspa}"
DB_PASS="${BEAUTYSPA_DB_PASS:-beautyspa}"
APP_URL="${BEAUTYSPA_APP_URL:-http://localhost:8000}"

echo "[bootstrap] Ensuring MariaDB and Redis are running…"
sudo service mariadb start >/dev/null 2>&1 || true
sudo service redis-server start >/dev/null 2>&1 || true

echo "[bootstrap] Waiting for MariaDB…"
for _ in $(seq 1 30); do
    if sudo mysqladmin ping >/dev/null 2>&1; then break; fi
    sleep 1
done

echo "[bootstrap] Ensuring database '${DB_NAME}' and user '${DB_USER}' exist…"
sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;"

if [ ! -f .env ]; then
    echo "[bootstrap] Creating .env from .env.example…"
    cp .env.example .env
    sed -i \
        -e "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" \
        -e "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" \
        -e "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" \
        -e "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" \
        -e "s#^APP_URL=.*#APP_URL=${APP_URL}#" \
        -e "s/^APP_INSTALLED=.*/APP_INSTALLED=true/" \
        -e "s/^APP_ENV=.*/APP_ENV=local/" \
        -e "s/^APP_DEBUG=.*/APP_DEBUG=true/" \
        .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[bootstrap] Generating application key…"
    php artisan key:generate --force
fi

echo "[bootstrap] Ensuring writable storage directories…"
mkdir -p \
    storage/framework/cache/data \
    storage/framework/cache/local-data/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache
chmod -R 775 storage bootstrap/cache >/dev/null 2>&1 || true

# Decide whether the application data still needs to be installed.
need_install=0
users_table=$(mysql -u"${DB_USER}" -p"${DB_PASS}" -h127.0.0.1 -N -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}' AND table_name='users';" 2>/dev/null || echo 0)
if [ "${users_table}" != "1" ]; then
    need_install=1
else
    admin_count=$(mysql -u"${DB_USER}" -p"${DB_PASS}" -h127.0.0.1 -N -e \
        "SELECT COUNT(*) FROM \`${DB_NAME}\`.users;" 2>/dev/null || echo 0)
    if [ "${admin_count}" = "0" ]; then need_install=1; fi
fi

if [ "${need_install}" = "1" ]; then
    echo "[bootstrap] Database not installed yet — running migrate:fresh + seed…"
    php artisan config:clear >/dev/null 2>&1 || true
    php artisan migrate:fresh --force
    php artisan tinker "${SCRIPT_DIR}/cloud-seed-install.php"
    # The installer flips the app into production mode; keep it in local/dev.
    sed -i \
        -e "s/^APP_ENV=.*/APP_ENV=local/" \
        -e "s/^APP_DEBUG=.*/APP_DEBUG=true/" \
        .env
else
    echo "[bootstrap] Application already installed — skipping migrate/seed."
fi

php artisan config:clear >/dev/null 2>&1 || true
echo "[bootstrap] Done. Storefront + admin available once the app server starts on ${APP_URL}."
