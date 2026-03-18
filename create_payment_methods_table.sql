-- Create USER_PAYMENT_METHODS table
USE TICKETIX;

CREATE TABLE IF NOT EXISTS USER_PAYMENT_METHODS (
    payment_method_id INT PRIMARY KEY AUTO_INCREMENT,
    acc_id INT NOT NULL,
    payment_type ENUM('credit-card', 'gcash', 'paypal') NOT NULL,
    card_number VARCHAR(20) NULL COMMENT 'Last 4 digits or full number (encrypted in production)',
    card_name VARCHAR(100) NULL COMMENT 'Cardholder name',
    card_expiry VARCHAR(7) NULL COMMENT 'MM/YYYY format',
    card_cvv VARCHAR(4) NULL COMMENT 'CVV (encrypted in production)',
    gcash_number VARCHAR(15) NULL COMMENT 'Philippine phone number for GCash',
    paypal_email VARCHAR(100) NULL COMMENT 'PayPal email address',
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (acc_id) REFERENCES USER_ACCOUNT(acc_id) ON DELETE CASCADE,
    INDEX idx_acc_id (acc_id),
    INDEX idx_default (acc_id, is_default)
) ENGINE=InnoDB;

ALTER TABLE USER_PAYMENT_METHODS 
MODIFY payment_type ENUM('credit-card', 'gcash', 'paypal', 'grabpay', 'paymaya') NOT NULL;

ALTER TABLE USER_PAYMENT_METHODS 
MODIFY payment_type VARCHAR(20) NOT NULL;

SELECT * FROM USER_PAYMENT_METHODS;


