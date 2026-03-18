USE TICKETIX;

ALTER TABLE PAYMENT 
MODIFY COLUMN payment_status ENUM('paid', 'pending', 'not-yet', 'refunded') DEFAULT 'pending';

UPDATE PAYMENT SET payment_status = 'refunded' 
WHERE payment_status = 'refunded';

SELECT 'Updated payment_status enum to include refunded.' AS result;

SET SQL_SAFE_UPDATES = 0
