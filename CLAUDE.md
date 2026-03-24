# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TemaDataPortal is a 3D geospatial data portal combining:
- **Backend**: Node.js Express server (`auth-server/`) handling authentication, file uploads, SFTP relay, and database operations
- **Frontend**: React + TypeScript Cesium 3D viewer (`react-viewer-app/`) for visualizing geospatial data
- **Landing Pages**: Static HTML/CSS pages in `html/` directory

The application enables users to view 3D models, upload drone imagery for processing, purchase map data with tokens, and manage geospatial datasets.

## Development Commands

### Starting the Application
```bash
# Start the main portal (runs auth-server on port 3000)
npm start

# Alternative: start from auth-server directory
cd auth-server
npm start
```

### Building the 3D Viewer
```bash
# Build React viewer and copy to html/cesium-viewer
npm run build-viewer

# Or from react-viewer-app directory
cd react-viewer-app
npm run build
```

### Database Operations
```bash
# Seed map data from locations
npm run seed-mapdata

# From auth-server directory
cd auth-server
npm run init-mapdata      # Initialize map data database
npm run create-db         # Create SQLite map data table
npm run seed-mapdata      # Seed map data
npm run auth:migrate      # Run Better Auth migrations
```

## Architecture

### Backend Structure (`auth-server/`)
- **server.js**: Main Express server with all route handlers inline (no separate routes/ directory)
- **auth.config.js**: Better Auth configuration for OAuth (Google, Microsoft)
- **microsoftAuth.js**: Microsoft OAuth helper functions
- **db/pg.cjs**: PostgreSQL query helper (CommonJS)
- **data/**: JSON files for users and map data (fallback storage)
- **sql/**: PostgreSQL migration scripts (run in numbered order)
- **scripts/**: Database seeding and initialization utilities

### Frontend Structure (`react-viewer-app/`)
- **src/App.tsx**: Main Cesium viewer application with measurement and annotation tools
- **src/pages/**: LandingPage.tsx, DiscoveryPage.tsx
- **src/components/**: Reusable UI components (AnnotationToolbar, MeasurementToolbar, EntityPopup, etc.)
- **vite.config.ts**: Vite build configuration with cesium plugin
- Built output goes to `dist/`, then copied to `html/cesium-viewer/`

### Key Technologies
- **Cesium**: 3D globe and terrain visualization
- **Better Auth**: OAuth authentication library
- **PostgreSQL**: Primary database (with SQLite fallback for map data)
- **Stripe**: Token payment processing
- **SFTP**: Remote file transfer to processing server
- **Cloudinary**: Image hosting for map thumbnails

## Database Setup

1. Create PostgreSQL database: `Temadigital_Data_Portal`
2. Run SQL scripts in `auth-server/sql/` in this order:
   - `Temadigital_Data_Portal_PostgreSQL.sql` (core tables)
   - `03-admin-tables-postgres.sql` (admin & upload tables)
   - `09-data-portal-users-table.sql` (user management)
   - `18-token-payments-tables.sql` (token system)
   - `19-mapdata-prices.sql` (pricing)
   - Other numbered scripts as needed

## Environment Configuration

Create `auth-server/.env` with:
- **PostgreSQL**: PG_HOST, PG_USER, PG_PASSWORD, PG_DATABASE, PG_PORT
- **OAuth**: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, MICROSOFT_CLIENT_ID, MICROSOFT_CLIENT_SECRET
- **Stripe**: STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY
- **SFTP**: REMOTE_SFTP_HOST, REMOTE_SFTP_PORT, REMOTE_SFTP_USER, REMOTE_SFTP_PASS
- **Cloudinary**: CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET
- **App**: BASE_URL, FRONTEND_URL, SESSION_SECRET, TOKEN_MYR_RATE

See `auth-server/.env.example` for full template.

## Key Features

### Authentication System
- Email/password registration (stored in PostgreSQL `data_portal_users` table)
- Google OAuth via Better Auth
- Microsoft OAuth via MSAL
- Session management with express-session
- User roles: guest, registered, trusted, subscriber, admin

### File Upload & Processing
- Clients upload drone imagery via chunked multipart upload
- Files temporarily stored in `uploads/` directory
- Admin reviews uploads and can relay to remote SFTP server for 3D processing
- Processing requests tracked in `ProcessingRequests` table

### Token System
- Users purchase tokens via Stripe
- Tokens used to buy map data downloads
- Token balance tracked per user
- 1 token = 2 MYR (configurable via TOKEN_MYR_RATE)

### Map Data Management
- Admin creates map entries with thumbnails, pricing, and 3D model metadata
- Map data stored in PostgreSQL `MapData` table
- Thumbnails uploaded to Cloudinary or local storage
- Users browse and purchase maps with tokens

## Important Notes

- The server uses ES modules (`"type": "module"` in package.json)
- Database helper `db/pg.cjs` is CommonJS, imported via `createRequire`
- React viewer must be rebuilt after source changes (`npm run build-viewer`)
- All routes are defined inline in `server.js` (no separate routes directory)
- SFTP connection is optional; server runs without it if not configured
- Stripe is optional; token system disabled if STRIPE_SECRET_KEY not set
