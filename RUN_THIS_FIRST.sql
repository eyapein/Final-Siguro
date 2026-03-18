-- COPY AND PASTE THIS ENTIRE SCRIPT IN phpMyAdmin SQL TAB
-- This will add firstName and lastName columns to your USER_ACCOUNT table
-- It will also make fullName nullable so it doesn't cause errors

USE TICKETIX;

-- Add firstName column
ALTER TABLE USER_ACCOUNT ADD COLUMN firstName VARCHAR(50) NOT NULL DEFAULT '' AFTER acc_id;

-- Add lastName column  
ALTER TABLE USER_ACCOUNT ADD COLUMN lastName VARCHAR(50) NOT NULL DEFAULT '' AFTER firstName;

-- Make fullName nullable (so it doesn't cause errors if not provided)
-- This allows the code to work with both firstName/lastName and fullName
ALTER TABLE USER_ACCOUNT MODIFY COLUMN fullName VARCHAR(50) NULL;

-- Done! Now try signing up again.
-- The signup will save to firstName and lastName, and also populate fullName automatically.
