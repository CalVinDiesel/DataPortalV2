-- Migration script to update user roles from client/subscriber to registered/trusted
-- Run this script to migrate existing data

-- Update existing clients to registered users
UPDATE "DataPortalUsers"
SET role = 'registered'
WHERE role = 'client';

-- Update existing subscribers to trusted users
UPDATE "DataPortalUsers"
SET role = 'trusted'
WHERE role = 'subscriber';

-- Verify the migration
SELECT role, COUNT(*) as count
FROM "DataPortalUsers"
GROUP BY role;
