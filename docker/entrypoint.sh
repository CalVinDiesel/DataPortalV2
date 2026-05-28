#!/bin/bash
##############################################################################
# Docker Container Entrypoint Script for DataPortalV2
#
# This script runs automatically every time the container starts.
# It performs one-time setup tasks (like caching configs) before starting
# the main services (Nginx + PHP-FPM) via Supervisord.
##############################################################################

set -e

echo "──────────────────────────────────────────────────────────"
echo "  DataPortalV2 — Container Starting Up"
echo "──────────────────────────────────────────────────────────"

# ── Step 1: Ensure storage directories have correct permissions ───────────────
echo "[1/5] Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/nitro_storage
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/nitro_storage

# ── Step 2: Create the storage link (public/storage → storage/app/public) ─────
echo "[2/5] Linking public storage..."
php artisan storage:link --force 2>/dev/null || true

# ── Step 3: Cache Laravel config, routes, and views for fast performance ──────
echo "[3/5] Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Step 4: Run any pending database migrations ───────────────────────────────
echo "[4/5] Running database migrations..."
php artisan migrate --force

# ── Step 5: Ensure supervisor log directory exists ────────────────────────────
echo "[5/5] Starting Nginx + PHP-FPM via Supervisord..."
mkdir -p /var/log/supervisor

# ── Start Supervisord (which starts Nginx and PHP-FPM) ───────────────────────
echo "──────────────────────────────────────────────────────────"
echo "  ✅ Setup complete. Container is live on port 80."
echo "──────────────────────────────────────────────────────────"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
