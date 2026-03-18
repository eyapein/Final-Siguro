-- Fix branch_id values in MOVIE_SCHEDULE to match actual BRANCH table IDs
-- The INSERT statements used 31-39, but BRANCH table has 1-9

USE TICKETIX;

-- First, let's see what branch_id values exist in BRANCH
SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_id;

-- Now update MOVIE_SCHEDULE to use correct branch_id values
-- Mapping: 31 -> 1 (Light Residences), 32 -> 2 (SM City Baguio), etc.

-- Light Residences (31 -> 1)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 1 
WHERE branch_id = 31;

-- SM City Baguio (32 -> 2)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 2 
WHERE branch_id = 32;

-- SM City Marikina (33 -> 3)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 3 
WHERE branch_id = 33;

-- SM Aura Premier (34 -> 4)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 4 
WHERE branch_id = 34;

-- SM Center Angono (35 -> 5)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 5 
WHERE branch_id = 35;

-- SM City Sta. Mesa (36 -> 6)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 6 
WHERE branch_id = 36;

-- SM City Sto. Tomas (37 -> 7)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 7 
WHERE branch_id = 37;

-- SM Mall of Asia (38 -> 8)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 8 
WHERE branch_id = 38;

-- SM Megacenter Cabanatuan (39 -> 9)
UPDATE MOVIE_SCHEDULE 
SET branch_id = 9 
WHERE branch_id = 39;

-- Verify the update
SELECT ms.branch_id, b.branch_name, COUNT(*) as schedule_count
FROM MOVIE_SCHEDULE ms
LEFT JOIN BRANCH b ON ms.branch_id = b.branch_id
GROUP BY ms.branch_id, b.branch_name
ORDER BY ms.branch_id;

-- Test query: Check if Light Residences (branch_id = 1) now has movies
SELECT DISTINCT m.title, m.now_showing
FROM MOVIE m
JOIN MOVIE_SCHEDULE ms ON m.movie_show_id = ms.movie_show_id
WHERE ms.branch_id = 1 AND m.now_showing = 1;

