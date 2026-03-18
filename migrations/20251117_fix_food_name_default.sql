-- Migration: Ensure FOOD.food_name has a safe default to avoid strict-mode insert errors
-- Run this in your MySQL client (e.g., via phpMyAdmin or the mysql CLI) for the TICKETIX database:

USE TICKETIX;

-- If you prefer allowing NULLs instead, change to DROP NOT NULL; this sets an empty string default instead.
ALTER TABLE FOOD
    MODIFY COLUMN food_name VARCHAR(50) NOT NULL DEFAULT '' ;

-- Optional: verify column definition
-- DESCRIBE FOOD;
