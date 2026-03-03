-- Showcase table: which locations appear in the landing-page showcase section (independent from overview map pins).
-- Admin can manage map pins (MapData) and showcase items (Showcase) separately; delete from map only, showcase only, or both.
-- Run while connected to Temadigital_Data_Portal (e.g. in pgAdmin).

CREATE TABLE IF NOT EXISTS public."Showcase" (
  id             SERIAL PRIMARY KEY,
  map_data_id    VARCHAR(64) NOT NULL,   -- references MapData.mapDataID (location id for 3D viewer link)
  display_order  INTEGER NOT NULL DEFAULT 0,
  created_at     TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

COMMENT ON TABLE public."Showcase" IS 'Landing page showcase tiles; each row links to a location (map_data_id). Independent from MapData so admin can remove from map only, showcase only, or both.';
COMMENT ON COLUMN public."Showcase".map_data_id IS 'Location id (e.g. MapData.mapDataID) used for 3D viewer link and tile label.';
COMMENT ON COLUMN public."Showcase".display_order IS 'Order of the tile in the showcase (lower = first).';
CREATE INDEX IF NOT EXISTS idx_showcase_map_data_id ON public."Showcase"(map_data_id);
CREATE INDEX IF NOT EXISTS idx_showcase_display_order ON public."Showcase"(display_order);
