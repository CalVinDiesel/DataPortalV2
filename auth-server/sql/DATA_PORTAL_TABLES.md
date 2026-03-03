# Data Portal – How the 3 Tables Link to the System

The **Temadigital_Data_Portal** database uses three tables. Here is how each one is used and where data comes from.

---

## 1. **MapData** – Overview map pins and showcases

**Purpose:** Stores every location pin shown on the overview map and in the 3D showcases.

**Data flow:**
- **Source:** Admin adds entries via **Add 3D Model** in the admin portal, or you seed from existing `data/locations.json`.
- **API:** `GET /api/map-data` and `GET /api/map-data/:id` read from this table when PostgreSQL is configured.
- **Front-end:** The overview map and 3D viewer load locations from the MapData API (and, if needed, from `data/locations.json` as fallback).

**Linking existing data:** Run from **auth-server**:
```bash
npm run seed-mapdata
```
This reads `data/locations.json` and upserts each location into **MapData** (mapDataID, title, description, xAxis/yAxis, 3dTiles, thumbNailUrl). After that, the overview map and showcases use the database for these locations.

---

## 2. **ClientUploads** – Client upload requests (waiting for admin approval)

**Purpose:** Stores uploads from the **Upload Data** page: client images/files/folders and metadata, with admin decision (accept/reject).

**Data flow:**
- **Source:** Clients submit via the data portal **Upload Data** page; `POST /api/upload-geospatial-data` saves files to disk and inserts a row into **ClientUploads** (project_id, project_title, upload_type, file_count, file_paths, camera_models, capture_date, organization_name, created_by_email, request_status = `pending`).
- **Admin:** Admin sees the list at **Client Uploads** in the admin portal; they can **Accept** or **Reject** (with reason). The backend updates `request_status`, `rejected_reason`, `decided_at`, `decided_by` via `POST /api/admin/client-uploads/:id/decision`.

No separate “migration” of existing upload data is needed: any new upload from the upload page is already written to **ClientUploads** when PostgreSQL is configured.

---

## 3. **ProcessingRequests** – Processed jobs and delivery to client

**Purpose:** One row per “process this client upload” job. Tracks status (pending → processing → completed/failed), result tileset URL, and **whether the result has been sent back to the client**.

**Data flow:**
- **Source:** Admin clicks **Process & deliver to client** for an accepted upload; `POST /api/admin/processing-request` creates a **ProcessingRequests** row (upload_id, status = `pending`, requested_by).
- **Processing:** You run your image-to-3D pipeline externally; then update the row (e.g. status = `completed`, result_tileset_url, completed_at) — e.g. via SQL or a future admin API.
- **Delivery:** When the 3D model has been sent to the client, admin clicks **Mark as delivered** in the admin portal; `POST /api/admin/processing-requests/:id/delivery` sets `delivered_at` (and optionally `delivery_notes`).

**Schema:** If **ProcessingRequests** was created before delivery tracking existed, run:
`sql/05-add-processing-delivery-columns.sql`
to add `delivered_at` and `delivery_notes`.

---

## Summary

| Table              | Focus                                      | Where data comes from / how it’s linked |
|--------------------|--------------------------------------------|-----------------------------------------|
| **MapData**        | Pins on overview map and showcases         | Admin “Add 3D Model” + seed from `data/locations.json` (`npm run seed-mapdata`) |
| **ClientUploads**  | Client uploads from upload page, pending approval | Upload page → `POST /api/upload-geospatial-data` → insert into **ClientUploads** |
| **ProcessingRequests** | Processed jobs and “sent back to client”   | Admin “Process & deliver” → insert; admin “Mark as delivered” → set `delivered_at` |
