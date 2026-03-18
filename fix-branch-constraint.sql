-- Fix branch_id foreign key constraint
-- Run these commands one by one in phpMyAdmin

USE TICKETIX;

-- Step 1: First, make branch_id nullable temporarily (if it's NOT NULL)
ALTER TABLE MOVIE_SCHEDULE MODIFY COLUMN branch_id INT NULL;

-- Step 2: Find the first valid branch_id
-- Run this query first to see what the first branch_id is:
-- SELECT MIN(branch_id) as first_branch_id FROM BRANCH;

-- Step 3: Set all NULL or invalid branch_id values to a valid branch_id (replace 1 with your first branch_id)
-- If your branches start from 1, use 1. If they start from a different number, use that number.
UPDATE MOVIE_SCHEDULE 
SET branch_id = 1
WHERE branch_id IS NULL 
   OR branch_id NOT IN (SELECT branch_id FROM BRANCH);

-- Alternative: If the above UPDATE doesn't work, try this simpler version:
-- UPDATE MOVIE_SCHEDULE SET branch_id = 1 WHERE branch_id IS NULL;

-- Step 4: Make branch_id NOT NULL again
ALTER TABLE MOVIE_SCHEDULE MODIFY COLUMN branch_id INT NOT NULL;

-- Step 5: Now add the foreign key constraint (this should work now)
ALTER TABLE MOVIE_SCHEDULE
ADD CONSTRAINT fk_branch FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id);

-- Done! The constraint should now be added successfully.

