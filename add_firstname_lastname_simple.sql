-- Simple script to add firstName and lastName columns to USER_ACCOUNT table
-- Run this in phpMyAdmin SQL tab

USE TICKETIX;

-- Add firstName column
ALTER TABLE USER_ACCOUNT 
ADD COLUMN firstName VARCHAR(50) NOT NULL DEFAULT '' AFTER acc_id;

-- Add lastName column
ALTER TABLE USER_ACCOUNT 
ADD COLUMN lastName VARCHAR(50) NOT NULL DEFAULT '' AFTER firstName;

-- Migrate existing fullName data to firstName and lastName
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

SELECT 'Success! firstName and lastName columns have been added.' AS result;