-- Add columns to support admin removal of Data Portal users (store reason + timestamp)
-- Run: psql -U postgres -d Temadigital_Data_Portal -f sql/21-add-data-portal-user-removal-columns.sql

-- removed_at: when set, the account is considered removed/disabled
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema='public' AND table_name='DataPortalUsers' AND column_name='removed_at'
  ) THEN
    ALTER TABLE public."DataPortalUsers"
      ADD COLUMN removed_at TIMESTAMP WITH TIME ZONE;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema='public' AND table_name='DataPortalUsers' AND column_name='removal_reason'
  ) THEN
    ALTER TABLE public."DataPortalUsers"
      ADD COLUMN removal_reason TEXT;
  END IF;
END $$;

