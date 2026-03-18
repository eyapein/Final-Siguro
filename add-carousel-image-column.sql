-- Add carousel_image column to MOVIE table for existing databases
-- Run this script if your database already exists and you need to add the carousel_image column

USE TICKETIX;

-- Check if column exists before adding (optional - will error if column already exists)
ALTER TABLE MOVIE ADD COLUMN carousel_image VARCHAR(100);

-- Note: If you get an error that the column already exists, that's fine - it means it's already been added.

