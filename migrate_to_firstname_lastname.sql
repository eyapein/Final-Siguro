-- Migrate USER_ACCOUNT table to match ticketix.sql schema
-- This will add firstName and lastName columns and migrate data from fullName
-- Run this in phpMyAdmin or MySQL command line

USE TICKETIX;

-- Step 1: Check and add firstName column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TICKETIX' 
AND TABLE_NAME = 'USER_ACCOUNT' 
AND COLUMN_NAME = 'firstName';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE USER_ACCOUNT ADD COLUMN firstName VARCHAR(50) NOT NULL DEFAULT "" AFTER acc_id',
    'SELECT "firstName column already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Check and add lastName column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TICKETIX' 
AND TABLE_NAME = 'USER_ACCOUNT' 
AND COLUMN_NAME = 'lastName';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE USER_ACCOUNT ADD COLUMN lastName VARCHAR(50) NOT NULL DEFAULT "" AFTER firstName',
    'SELECT "lastName column already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Migrate existing fullName data to firstName and lastName
-- Split "John Doe" into firstName="John" and lastName="Doe"
SET @fullname_exists = 0;
SELECT COUNT(*) INTO @fullname_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TICKETIX' 
AND TABLE_NAME = 'USER_ACCOUNT' 
AND COLUMN_NAME = 'fullName';

SET @sql = IF(@fullname_exists > 0,
    'UPDATE USER_ACCOUNT 
     SET 
       firstName = CASE 
         WHEN fullName IS NOT NULL AND fullName != "" AND LOCATE(" ", fullName) > 0 
         THEN SUBSTRING_INDEX(fullName, " ", 1)
         WHEN fullName IS NOT NULL AND fullName != ""
         THEN fullName
         ELSE firstName
       END,
       lastName = CASE 
         WHEN fullName IS NOT NULL AND fullName != "" AND LOCATE(" ", fullName) > 0 
         THEN SUBSTRING_INDEX(fullName, " ", -1)
         WHEN fullName IS NOT NULL AND fullName != "" AND LOCATE(" ", fullName) = 0
         THEN ""
         ELSE lastName
       END
     WHERE fullName IS NOT NULL AND fullName != ""',
    'SELECT "No fullName column found, skipping data migration" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration completed! firstName and lastName columns are now available.' AS result;
SELECT 'Your database structure now matches ticketix.sql schema.' AS result;

-- Step 4: (OPTIONAL) Remove fullName column
-- Uncomment the following lines ONLY after verifying that firstName and lastName have correct data
/*
SET @fullname_exists = 0;
SELECT COUNT(*) INTO @fullname_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'TICKETIX' 
AND TABLE_NAME = 'USER_ACCOUNT' 
AND COLUMN_NAME = 'fullName';

SET @sql = IF(@fullname_exists > 0,
    'ALTER TABLE USER_ACCOUNT DROP COLUMN fullName',
    'SELECT "fullName column does not exist" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
*/