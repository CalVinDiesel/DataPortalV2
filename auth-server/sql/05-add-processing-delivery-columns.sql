-- Add delivery-to-client tracking to ProcessingRequests
-- Run while connected to Temadigital_Data_Portal (e.g. in pgAdmin).
-- Example: psql -U postgres -d Temadigital_Data_Portal -f sql/05-add-processing-delivery-columns.sql

ALTER TABLE public."ProcessingRequests"
  ADD COLUMN IF NOT EXISTS delivered_at TIMESTAMP WITH TIME ZONE,
  ADD COLUMN IF NOT EXISTS delivery_notes TEXT;

COMMENT ON COLUMN public."ProcessingRequests".delivered_at IS 'When the processed 3D model was sent/delivered back to the client';
COMMENT ON COLUMN public."ProcessingRequests".delivery_notes IS 'Optional notes about delivery (e.g. download link, method)';
