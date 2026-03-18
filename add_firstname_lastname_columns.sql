-- Add firstName and lastName columns to USER_ACCOUNT table
-- Run this script in phpMyAdmin or MySQL command line

USE TICKETIX;

-- Add firstName column
ALTER TABLE USER_ACCOUNT 
ADD COLUMN firstName VARCHAR(50) NOT NULL DEFAULT '' AFTER acc_id;

-- Add lastName column  
ALTER TABLE USER_ACCOUNT 
ADD COLUMN lastName VARCHAR(20) NOT NULL DEFAULT '' AFTER firstName;

-- If fullName column exists, populate firstName and lastName from existing fullName data
-- This will split "John Doe" into firstName="John" and lastName="Doe"
UPDATE USER_ACCOUNT 
SET 
  firstName = CASE 
    WHEN fullName IS NOT NULL AND fullName != '' AND LOCATE(' ', fullName) > 0 
    THEN SUBSTRING_INDEX(fullName, ' ', 1)
    WHEN fullName IS NOT NULL AND fullName != ''
    THEN fullName
    ELSE firstName
  END,
  lastName = CASE 
    WHEN fullName IS NOT NULL AND fullName != '' AND LOCATE(' ', fullName) > 0 
    THEN SUBSTRING_INDEX(fullName, ' ', -1)
    WHEN fullName IS NOT NULL AND fullName != '' AND LOCATE(' ', fullName) = 0
    THEN ''
    ELSE lastName
  END
WHERE fullName IS NOT NULL AND fullName != '';

SELECT 'Migration completed successfully! firstName and lastName columns added.' AS result;