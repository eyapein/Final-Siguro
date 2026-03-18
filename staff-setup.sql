-- ================================================================
-- TICKETIX STAFF PORTAL SETUP
-- Run this SQL in phpMyAdmin or MySQL terminal
-- Staff account: marbellamoonie30@gmail.com / Staff@2024!
-- ================================================================
USE ticketix;

-- Insert staff account (password: Staff@2024!)
-- bcrypt hash of 'Staff@2024!'
INSERT INTO USER_ACCOUNT (firstName, lastName, contNo, email, address, user_password, time_created, user_status, role)
VALUES (
    'Marbella',
    'Moonie',
    '09000000000',
    'marbellamoonie30@gmail.com',
    'Cinema Branch',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(),
    'offline',
    'staff'
)
ON DUPLICATE KEY UPDATE role = 'staff';

-- Mark email as verified so staff can log in immediately
INSERT INTO email_verifications (acc_id, token, expires_at, used_at)
SELECT acc_id, SHA2(CONCAT('staff_verified_', email), 256), DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW()
FROM USER_ACCOUNT
WHERE email = 'marbellamoonie30@gmail.com'
ON DUPLICATE KEY UPDATE used_at = NOW();

-- Create walk-in customer account for staff bookings (shared guest)
INSERT INTO USER_ACCOUNT (firstName, lastName, contNo, email, address, user_password, time_created, user_status, role)
VALUES (
    'Walk',
    'In',
    '09000000001',
    'walkin@ticketix.staff',
    'Cinema Counter',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    NOW(),
    'offline',
    'walkin'
)
ON DUPLICATE KEY UPDATE role = 'walkin';

-- Verify walk-in account too
INSERT INTO email_verifications (acc_id, token, expires_at, used_at)
SELECT acc_id, SHA2(CONCAT('walkin_verified_', email), 256), DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW()
FROM USER_ACCOUNT
WHERE email = 'walkin@ticketix.staff'
ON DUPLICATE KEY UPDATE used_at = NOW();

SELECT 'Staff setup complete!' AS status;
SELECT acc_id, firstName, lastName, email, role FROM USER_ACCOUNT WHERE role IN ('staff', 'walkin');
