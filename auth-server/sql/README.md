# SQL scripts for Data Portal (PostgreSQL)

**First-time setup:** see **[SETUP-POSTGRESQL-PGADMIN.md](./SETUP-POSTGRESQL-PGADMIN.md)** for step-by-step instructions.

---

## Scripts in this folder

| File | Purpose |
|------|--------|
| **Temadigital_Data_Portal_PostgreSQL.sql** | Creates table **MapData** (3D model entries for the overview map) and inserts one seed row. Run this while connected to database `Temadigital_Data_Portal`. |
| **03-admin-tables-postgres.sql** | Creates tables **ClientUploads** and **ProcessingRequests** for the custom image-to-3D processing service: clients submit their images; admin can accept/reject requests (with reason); admin processes accepted requests and delivers the 3D model back to the client (paid service; not added to overview map). Run after the script above, same database. |
| **04-add-client-upload-decision-columns.sql** | Run only if **ClientUploads** already existed before the accept/reject feature: adds `request_status`, `rejected_reason`, `decided_at`, `decided_by`. New installs get these from 03. |
| **05-add-processing-delivery-columns.sql** | Run if **ProcessingRequests** already existed: adds `delivered_at`, `delivery_notes` to track when the 3D model was sent back to the client. New installs get these from 03. |
| **06-showcase-table.sql** | Creates table **Showcase** (landing page showcase tiles). Independent from MapData so admin can remove from map only, showcase only, or both. Run after 03, same database. |

Create the database **Temadigital_Data_Portal** in pgAdmin first, then run the scripts in order (01 = MapData, 03 = ClientUploads + ProcessingRequests, 06 = Showcase; 04 and 05 only if you already had those tables).

**Link existing data to MapData (overview map + showcases):** From **auth-server**, run:
`node scripts/seed-mapdata-from-locations.js`
This reads `data/locations.json` and upserts every location into **MapData** so the overview map and 3D viewer use the database. Ensure `PG_DATABASE` is set in `.env` before running.

---

## Option: SQLite (no PostgreSQL)

For map data only, the server can use a local SQLite file instead of PostgreSQL:

1. In **auth-server**: `npm run create-db`
2. This creates `data/Temadigital_Data_Portal.sqlite` with table **MapData** (used when `PG_DATABASE` is not set).

When **PG_DATABASE** is set in `.env`, the server uses PostgreSQL (MapData, ClientUploads, ProcessingRequests) instead.
