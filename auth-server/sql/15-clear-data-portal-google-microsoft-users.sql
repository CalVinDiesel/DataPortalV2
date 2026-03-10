-- Clear all data in DataPortalUsers, GoogleUsers and MicrosoftUsers only.
-- Table structures, constraints and setup are unchanged; only rows are removed.
-- Run in pgAdmin: Query Tool, execute on your Temadigital_Data_Portal database.

TRUNCATE TABLE public."DataPortalUsers" RESTART IDENTITY;

TRUNCATE TABLE public."MicrosoftUsers" RESTART IDENTITY;

-- Better Auth user table: may be named "GoogleUsers" (after rename) or "user"
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'GoogleUsers') THEN
    TRUNCATE TABLE public."GoogleUsers" RESTART IDENTITY CASCADE;
    RAISE NOTICE 'Cleared GoogleUsers.';
  ELSIF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'user') THEN
    TRUNCATE TABLE public."user" RESTART IDENTITY CASCADE;
    RAISE NOTICE 'Cleared user (Better Auth).';
  END IF;
END $$;
