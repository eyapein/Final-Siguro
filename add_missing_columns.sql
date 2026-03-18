-- Add missing columns to user_account table
USE ticketix;

-- Add email column
ALTER TABLE user_account ADD COLUMN email VARCHAR(50) UNIQUE;

-- Add username column (we can use fullName as username for now, or add a separate username field)
-- For now, let's add a separate username field
ALTER TABLE user_account ADD COLUMN username VARCHAR(50) UNIQUE;

-- Update the table to make email NOT NULL if needed
-- ALTER TABLE user_account MODIFY COLUMN email VARCHAR(50) UNIQUE NOT NULL;