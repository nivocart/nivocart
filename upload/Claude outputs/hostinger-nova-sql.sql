-- NivoCart Nova admin theme / Reports Dashboard: run in Hostinger phpMyAdmin (database prefix nc_)
-- Run BEFORE uploading the files (or straight after: either order is fine, but the menu needs the permission).

-- 1) Add the Nova admin theme row (safe to re-run: does nothing if it already exists)
INSERT INTO `nc_administration` (`name`, `contrast`, `date_added`, `date_modified`)
SELECT 'nova', 'dark', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `nc_administration` WHERE `name` = 'nova');

-- 2) Give existing user groups access to the new Reports Dashboard (access + modify lists).
--    Handles both JSON styles ("report/x" and "report\/x"). Safe to re-run: groups that already have it are skipped.
UPDATE `nc_user_group`
SET `permission` = REPLACE(REPLACE(`permission`,
    '"report/customer_wishlist"', '"report/customer_wishlist","report/dashboard"'),
    '"report\\/customer_wishlist"', '"report\\/customer_wishlist","report\\/dashboard"')
WHERE LOCATE('report/dashboard', `permission`) = 0
  AND LOCATE('report\\/dashboard', `permission`) = 0;

-- 3) Check: has_dashboard should be 1 for every group that had report access (add it by hand in User Groups for any group showing 0)
SELECT `user_group_id`, `name`,
       (LOCATE('report/dashboard', `permission`) > 0 OR LOCATE('report\\/dashboard', `permission`) > 0) AS has_dashboard
FROM `nc_user_group`;

-- 4) Check the theme row
SELECT * FROM `nc_administration`;

-- Afterwards: Design > Administration (or Settings > Server) and choose "nova" if you want it as the admin theme.
