-- Fix branch_id foreign key constraint issue
-- This script will fix invalid branch_id values before adding the constraint

USE TICKETIX;

-- Step 1: Check what branch_ids exist in BRANCH table
SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_id;

-- Step 2: Check what branch_id values are in MOVIE_SCHEDULE (if any)
SELECT DISTINCT branch_id, COUNT(*) as count 
FROM MOVIE_SCHEDULE 
WHERE branch_id IS NOT NULL
GROUP BY branch_id;

-- Step 3: Set all invalid branch_id values to NULL (or first valid branch_id)
-- Option A: Set to NULL if column allows it (we'll make it nullable temporarily)
ALTER TABLE MOVIE_SCHEDULE MODIFY COLUMN branch_id INT NULL;

-- Step 4: Set all existing branch_id values to the first valid branch_id (branch_id = 1)
-- This ensures all schedules are assigned to at least one branch
UPDATE MOVIE_SCHEDULE 
SET branch_id = (SELECT MIN(branch_id) FROM BRANCH)
WHERE branch_id IS NULL OR branch_id NOT IN (SELECT branch_id FROM BRANCH);

-- Step 5: Make branch_id NOT NULL again
ALTER TABLE MOVIE_SCHEDULE MODIFY COLUMN branch_id INT NOT NULL;

-- Step 6: Now add the foreign key constraint
ALTER TABLE MOVIE_SCHEDULE
ADD CONSTRAINT fk_branch FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id);

-- Verify the constraint was added
SHOW CREATE TABLE MOVIE_SCHEDULE;

