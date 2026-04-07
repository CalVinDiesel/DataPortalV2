# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this unified repository.

## Project Overview

TemaDataPortal is a unified 3D geospatial data portal combining the Data Portal and 3D Viewer into a single Laravel-based project.

- **Framework**: Laravel 11 (PHP)
- **Frontend**: Vite-managed assets (React/TypeScript for the Viewer, Blade/JavaScript for the Portal)
- **Primary Database**: PostgreSQL

## Development Commands

### Starting the Application
```bash
# Start the Laravel development server
php artisan serve

# Start the Vite development server (for JS/CSS/React)
npm run dev
```

### Database Operations
```bash
# Run migrations to set up the database schema
php artisan migrate

# Seed the database
php artisan db:seed
```

### Building for Production
```bash
# Build production assets
npm run build
```

## Architecture

### Backend Structure (`app/`)
- **Http/Controllers/**: Controllers for Auth, MapData, and Token management.
- **Models/**: Eloquent models for User, MapData, TokenWallet, etc.
- **Services/**: Ported logic for external integrations (SFTP, Stripe, Cloudinary).

### Frontend Structure (`resources/js/`)
- **app.js**: Main entry point for the portal's JavaScript.
- **viewer/**: React source code for the Cesium-based 3D Viewer.
  - `main.tsx`: Entry point for the 3D Viewer.

### Routing (`routes/`)
- **web.php**: Main routes for the portal and viewer.
- **api.php**: API endpoints for the viewer and data operations.

### Views (`resources/views/`)
- **Blade Templates**: Replacing the former static HTML landing pages.

## Database Setup

1. Configure PostgreSQL connection in the root `.env` file.
2. Run `php artisan migrate` to unify the schema.

## Key Technologies
- **Laravel**: PHP backend framework.
- **Vite**: Modern build tool for JavaScript/TypeScript assets.
- **React**: Library used for the 3D Viewer interactive components.
- **Cesium**: 3D globe visualization.
- **PostgreSQL**: Primary data storage.
- **Stripe**: Token payment processing.
- **SFTPGo**: Remote file management for 3D processing.

## Important Notes
- This is a UNIFIED project. **Do not create separate auth-server or react-viewer-app directories.**
- Use `php artisan` for generating new models, controllers, and migrations.
- The 3D Viewer's main entry point is `resources/js/viewer/main.tsx`.
- All environment variables are consolidated in the root `.env` file.
