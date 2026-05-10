# 🚀 3DHub Data Portal — Complete Deployment Guide

This guide provides step-by-step instructions for deploying the Data Portal to a production server. Follow these steps sequentially to ensure a successful deployment.

---

## 📋 Prerequisites (Manager Side)

Before deploying, ensure the following software is installed on the server:

1.  **OS**: Linux (Ubuntu 22.04+ recommended) or Windows Server.
2.  **Web Server**: Nginx (Recommended) or Apache.
3.  **PHP 8.3**: With the following extensions:
    *   `php8.3-fpm`, `php8.3-cli`, `php8.3-pgsql`, `php8.3-mbstring`, `php8.3-xml`, `php8.3-curl`, `php8.3-zip`, `php8.3-bcmath`, `php8.3-intl`.
4.  **Database**: PostgreSQL 15+.
5.  **Composer**: PHP Dependency Manager.
6.  **Node.js & NPM**: For frontend asset compilation (Vite).
7.  **SFTP Server**: SFTPGo or OpenSSH (for data storage and delivery).

---

## 🛠️ Phase 1: Code Acquisition & Environment Setup

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/your-organization/DataPortalV2.git /var/www/dataportal
    cd /var/www/dataportal
    ```

2.  **Install PHP Dependencies**:
    ```bash
    composer install --no-dev --optimize-autoloader
    ```

3.  **Install Node Dependencies & Build Assets**:
    ```bash
    npm install
    npm run build
    ```

4.  **Configure Environment (`.env`)**:
    Copy the example file and update the variables:
    ```bash
    cp .env.example .env
    nano .env
    ```

    **Update the following CRITICAL variables:**
    *   `APP_ENV=production` (Enables production security and performance optimizations)
    *   `APP_DEBUG=false`
    *   `APP_URL=https://your-portal-domain.com`
    *   `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (PostgreSQL credentials)
    *   `SFTP_DELIVERY_HOST`, `SFTP_DELIVERY_PORT` (Admin/Management port, e.g., 2222)
    *   `SFTP_USER_PORT` (Client access port, e.g., 2223)
    *   `SFTP_DELIVERY_USERNAME`, `SFTP_DELIVERY_PASSWORD` (Master SFTP credentials)
    *   `SFTP_DELIVERY_ROOT=/home/tiquan/` (The absolute root path of your SFTP storage)
    *   `NITRO_STORAGE_ROOT=/var/www/dataportal_nitro` (A high-speed directory for temporary upload chunks)

5.  **Generate App Key & Initialize Database**:
    ```bash
    php artisan key:generate
    php artisan migrate --force
    php artisan storage:link
    ```

---

## ⚡ Phase 2: Nitro Engine & Permissions

The **Nitro Parallel Upload Engine** handles large file transfers. It needs a dedicated temporary storage area.

1.  **Create Nitro Directory**:
    ```bash
    sudo mkdir -p /var/www/dataportal_nitro
    sudo chown -R www-data:www-data /var/www/dataportal_nitro
    sudo chmod -R 775 /var/www/dataportal_nitro
    ```

2.  **App Permissions**:
    ```bash
    sudo chown -R www-data:www-data /var/www/dataportal
    sudo chmod -R 775 /var/www/dataportal/storage
    sudo chmod -R 775 /var/www/dataportal/bootstrap/cache
    ```

---

## 🛰️ Phase 3: External Services Integration

To ensure all features work correctly, provide the following credentials in `.env`:

### 1. Microsoft/OneDrive (Project Creation)
*   `MICROSOFT_CLIENT_ID`
*   `MICROSOFT_CLIENT_SECRET`
*   `MICROSOFT_TENANT_ID`
*   *Note: If automated scanning fails due to Microsoft restrictions, the system will automatically prompt the user to enter File Size and Photo Count manually.*

### 2. Google Drive (Delivery)
*   `GOOGLE_CLIENT_ID`
*   `GOOGLE_CLIENT_SECRET`
*   `GOOGLE_REFRESH_TOKEN`
*   `GOOGLE_DRIVE_DELIVERY_FOLDER_ID`

### 3. Stripe (Payments)
*   `STRIPE_PUBLISHABLE_KEY`
*   `STRIPE_SECRET_KEY`

---

## 🚀 Phase 4: Running the App

### For Linux (Nginx/Production):
Nginx handles the high-concurrency requirements of Nitro automatically.
1.  **Configure Nginx**: Use the template provided in `deployment/nginx.conf`.
2.  **Configure PHP-FPM**: Use the pool config in `deployment/php-fpm.conf`.
3.  **Optimize Laravel**:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

### For Background Processing:
If you use a queue (e.g., for email notifications), start the worker:
```bash
php artisan queue:work --tries=3 --timeout=900
```

---

## 🏁 Phase 5: Final Verification Checklist

After deployment, log in as **Admin** and verify:
1.  **Mark as Delivered**: Ensure you can click "Mark as Delivered" on a project and successfully upload a file or provide a manual link.
2.  **Edit Notes**: Verify you can click the "Edit Notes" button on a completed project to update the delivery summary.
3.  **OneDrive Sync**: Create a test project with a OneDrive link and verify that you can manually enter the photo count and file size if prompted.
4.  **Nitro Check**: Ensure that during large file deliveries, the progress bar moves and the "Nitro Integrity Check" passes upon completion.

---

**Support Contact**:
If errors occur during deployment, check the logs at: `/var/www/dataportal/storage/logs/laravel.log`
