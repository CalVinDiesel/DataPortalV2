-- Add accept/reject decision columns to existing ClientUploads table (run if table was created before this feature).
-- Run while connected to database Temadigital_Data_Portal. Safe to run multiple times (IF NOT EXISTS / defaults).

ALTER TABLE public."ClientUploads"
  ADD COLUMN IF NOT EXISTS request_status VARCHAR(32) NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS rejected_reason TEXT,
  ADD COLUMN IF NOT EXISTS decided_at TIMESTAMP WITH TIME ZONE,
  ADD COLUMN IF NOT EXISTS decided_by VARCHAR(255);

COMMENT ON COLUMN public."ClientUploads"."request_status" IS 'Admin decision: pending, accepted, or rejected';
COMMENT ON COLUMN public."ClientUploads"."rejected_reason" IS 'Reason given to client when request is rejected';

CREATE INDEX IF NOT EXISTS idx_client_uploads_request_status ON public."ClientUploads"(request_status);
