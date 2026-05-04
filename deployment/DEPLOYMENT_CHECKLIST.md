# DataPortalV2 — Production Deployment Checklist

> This checklist was pre-prepared during development. Follow it top to bottom
> when deploying to a real-world server. No extra Nitro configuration needed —
> the upload engine adapts automatically when `APP_ENV=production`.

---

## Phase 1: Server Setup

### 1.1 Install Required Software
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-pgsql php8.2-mbstring \
     php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl unzip git
```

### 1.2 Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## Phase 2: Deploy the Application

### 2.1 Upload Project Files
```bash
# Option A: Git clone
git clone https://github.com/your-repo/DataPortalV2.git /var/www/dataportal

# Option B: SCP / SFTP upload
# Upload project zip then: unzip dataportal.zip -d /var/www/dataportal
```

### 2.2 Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/dataportal
sudo chmod -R 755 /var/www/dataportal
sudo chmod -R 775 /var/www/dataportal/storage
sudo chmod -R 775 /var/www/dataportal/bootstrap/cache
```

### 2.3 Install PHP Dependencies
```bash
cd /var/www/dataportal
composer install --no-dev --optimize-autoloader
```

### 2.4 Configure Environment
```bash
cp .env.example .env   # or upload your .env directly
nano .env
```

**Critical `.env` values for production:**
```env
APP_ENV=production        # ← Switches Nitro engine to production mode automatically
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=...
DB_DATABASE=...

SFTP_DELIVERY_HOST=...
SFTP_DELIVERY_PORT=2222   # ← Admin port (keep as is)
SFTP_DELIVERY_USERNAME=tiquan
SFTP_DELIVERY_PASSWORD=ubuntu23
SFTP_DELIVERY_ROOT=/home/tiquan/uploads/

NITRO_STORAGE_ROOT=/tmp/dataportal_nitro
```

### 2.5 Generate App Key & Run Migrations
```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Phase 3: Configure Web Server

### 3.1 Nginx
```bash
# Copy the pre-built config from the deployment folder
sudo cp /var/www/dataportal/deployment/nginx.conf /etc/nginx/sites-available/dataportal

# Edit domain name
sudo nano /etc/nginx/sites-available/dataportal
# Replace 'your-domain.com' with your actual domain

# Enable the site
sudo ln -s /etc/nginx/sites-available/dataportal /etc/nginx/sites-enabled/

# Test and reload
sudo nginx -t && sudo systemctl reload nginx
```

### 3.2 PHP-FPM
```bash
# Copy the pre-built pool config
sudo cp /var/www/dataportal/deployment/php-fpm.conf /etc/php/8.2/fpm/pool.d/dataportal.conf
sudo systemctl reload php8.2-fpm
```

---

## Phase 4: SSL Certificate (HTTPS)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

---

## Phase 5: Nitro Temporary Storage

```bash
sudo mkdir -p /tmp/dataportal_nitro
sudo chown www-data:www-data /tmp/dataportal_nitro
sudo chmod 775 /tmp/dataportal_nitro
```

---

## Phase 6: Final Verification

```bash
# Watch logs during a test upload
tail -f /var/www/dataportal/storage/logs/laravel.log

# Look for these success indicators:
# ✅ [v108 SUCCESS] All cargo delivered to WinSCP disk.
# ✅ NITRO FINALIZE SUCCESS
```

---

## What You Do NOT Need in Production

| Dev Only | Why Not Needed |
|---|---|
| `php artisan nitro:start` | Nginx + PHP-FPM replaces this |
| `NITRO_START.bat / STOP.bat` | Windows dev tools only |
| Ports 9001–9016 | Nginx handles concurrency natively |
| `php artisan serve` | Nginx serves the app |

> The Nitro 16-lane engine **automatically switches to production mode**
> when `APP_ENV=production`. No code changes required. ✅

---

## Quick Reference After Deployment

```bash
# Restart everything
sudo systemctl restart nginx php8.2-fpm

# Clear caches after a code update
php artisan config:cache && php artisan route:cache && php artisan view:cache

# View live logs
tail -f /var/www/dataportal/storage/logs/laravel.log

# Run new migrations
php artisan migrate --force
```
