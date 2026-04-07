# 3D Hub Data Portal

A high-performance geospatial data management portal built with Laravel and React. This portal allows users to upload, manage, and visualize 3D mapping data using the Cesium globe.

## 🚀 Quick Start (Local Development)

To get things running on your machine:

### 1. Requirements
Ensure you have the following installed:
- **PHP 8.2+** (recommend 8.4)
- **Node.js 18+** & **NPM**
- **Composer**
- **PostgreSQL** (local or Cloud instance like Neon)

### 2. Basic Installation
```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git
cd YOUR_REPO

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 3. Database & App Configuration
Open your `.env` file and configure your credentials:
- **DB Connection**: Your PostgreSQL host and password.
- **Social Login**: Set your `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.
- **APP_URL**: Typically `http://127.0.0.1:8000` for local dev.

### 4. ⚠️ CRITICAL: Large File Upload Support
This portal handles large 3D datasets. To prevent "413 Content Too Large" errors, you must increase your PHP upload limits.

#### If you use Laravel Herd (Recommended):
1. Open the **Herd Dashboard** -> PHP Tab.
2. Click **Edit php.ini**.
3. Update these lines:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 512M
```
4. Restart Herd.

#### Manual Terminal Run:
The project's `npm run dev:all` script is already pre-configured to attempt to force these limits during development.

### 5. Running the Application
```bash
# Start both the PHP server and Vite dev server at once
npm run dev:all
```
Your app will now be live at **http://127.0.0.1:8000**.

## 🏗️ Technical Architecture
- **Backend**: Laravel 11.
- **Frontend**: React (TypeScript) + Vite.
- **Visualizer**: CesiumJS for 3D globe rendering.
- **Authentication**: Google/Microsoft OAuth (Socialite) + Sanctum.
- **File System**: Local storage with chunked browser uploads for high-volume data.

## 🛡️ License
Private repository. All rights reserved. GeoVidia.
