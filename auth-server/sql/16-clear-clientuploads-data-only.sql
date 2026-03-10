-- Clear all data in ClientUploads only. Table structure (columns, constraints, indexes) is kept.
-- Run in pgAdmin: Query Tool, execute on your Temadigital_Data_Portal database.

TRUNCATE TABLE public."ClientUploads" RESTART IDENTITY CASCADE;

-- After running: ClientUploads is empty; IDs will restart from 1 on next insert.
-- CASCADE clears any rows in other tables that reference ClientUploads (e.g. ProcessingRequests).
