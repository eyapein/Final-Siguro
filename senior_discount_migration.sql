-- Migration: Add Senior Citizen discount support
-- Creates SENIOR_APPLICATIONS table and adds senior_approved column to USER_ACCOUNT

CREATE TABLE IF NOT EXISTS SENIOR_APPLICATIONS (
    senior_app_id INT AUTO_INCREMENT PRIMARY KEY,
    acc_id INT NOT NULL,
    senior_id_number VARCHAR(50) NOT NULL,
    senior_id_image VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (acc_id) REFERENCES USER_ACCOUNT(acc_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add senior_approved flag to USER_ACCOUNT (same pattern as pwd_approved)
ALTER TABLE USER_ACCOUNT ADD COLUMN IF NOT EXISTS senior_approved TINYINT(1) DEFAULT 0;
