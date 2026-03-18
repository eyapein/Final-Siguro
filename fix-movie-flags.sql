-- Fix existing movies that have both now_showing and coming_soon set to TRUE
-- If coming_soon is TRUE, set now_showing to FALSE
-- Run this in phpMyAdmin SQL tab

USE ticketix;

-- Update movies where both flags are TRUE - prioritize coming_soon
UPDATE MOVIE 
SET now_showing = FALSE 
WHERE coming_soon = TRUE AND now_showing = TRUE;

-- Verify the fix
SELECT movie_show_id, title, now_showing, coming_soon 
FROM MOVIE 
WHERE (now_showing = TRUE AND coming_soon = TRUE);

-- Should return 0 rows if fixed correctly