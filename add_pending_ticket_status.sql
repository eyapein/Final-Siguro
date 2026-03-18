-- Add 'pending' status to TICKET table ticket_status ENUM
-- Run this script in phpMyAdmin or MySQL command line

USE TICKETIX;

-- Modify ticket_status to include 'pending'
ALTER TABLE TICKET 
MODIFY COLUMN ticket_status ENUM('pending', 'valid', 'cancelled', 'refunded') DEFAULT 'pending';

-- Update existing tickets to 'pending' if they don't have an approved booking_status
-- (Only if booking_status column exists)
SET @has_booking_status = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'TICKETIX' 
    AND TABLE_NAME = 'RESERVE' 
    AND COLUMN_NAME = 'booking_status'
);

SET @sql = IF(@has_booking_status > 0,
    'UPDATE TICKET t
     JOIN RESERVE r ON t.reserve_id = r.reservation_id
     SET t.ticket_status = CASE 
         WHEN r.booking_status = ''approved'' THEN ''valid''
         WHEN r.booking_status = ''declined'' THEN ''cancelled''
         ELSE ''pending''
     END',
    'SELECT ''booking_status column does not exist yet. Run add_booking_status_column.sql first.'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration completed successfully! pending status added to TICKET table.' AS result;

