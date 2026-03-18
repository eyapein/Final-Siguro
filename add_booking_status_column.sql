-- Add booking_status column to RESERVE table
-- Run this script in phpMyAdmin or MySQL command line

USE TICKETIX;

-- Add booking_status column with default value 'pending'
ALTER TABLE RESERVE 
ADD COLUMN booking_status ENUM('pending', 'approved', 'declined') DEFAULT 'pending' AFTER reserve_date;

-- Update existing records to 'approved' if payment is already paid, otherwise 'pending'
UPDATE RESERVE r
LEFT JOIN PAYMENT p ON r.reservation_id = p.reserve_id
SET r.booking_status = CASE 
    WHEN p.payment_status = 'paid' THEN 'approved'
    ELSE 'pending'
END
WHERE r.booking_status IS NULL OR r.booking_status = '';

SELECT 'Migration completed successfully! booking_status column added to RESERVE table.' AS result;

SET SQL_SAFE_UPDATES = 0;

UPDATE RESERVE r
LEFT JOIN PAYMENT p ON r.reservation_id = p.reserve_id
SET r.booking_status = CASE
    WHEN p.payment_status = 'paid' THEN 'approved'
    ELSE 'pending'
END
WHERE r.booking_status IS NULL OR r.booking_status = '';

SET SQL_SAFE_UPDATES = 1;  -- optional: turn it back on

