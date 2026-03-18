-- Create Database
CREATE DATABASE IF NOT EXISTS TICKETIX;
USE TICKETIX;

-- Drop tables if they already exist (in correct order to handle foreign key constraints)
DROP TABLE IF EXISTS BRANCH;
DROP TABLE IF EXISTS TICKET;
DROP TABLE IF EXISTS PAYMENT;
DROP TABLE IF EXISTS RESERVE_SEAT;
DROP TABLE IF EXISTS RESERVE;
DROP TABLE IF EXISTS SEAT;
DROP TABLE IF EXISTS MOVIE_SCHEDULE;
DROP TABLE IF EXISTS MOVIE;
DROP TABLE IF EXISTS USER_ACCOUNT;
DROP TABLE IF EXISTS TICKET_FOOD;

-- 1️⃣ USER_ACCOUNT Table
CREATE TABLE USER_ACCOUNT(
    acc_id INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    contNo VARCHAR(12),
    email VARCHAR(50) UNIQUE NOT NULL,
    address VARCHAR(50),
    birthdate DATE,
    user_password VARCHAR(70),
    time_created DATETIME,
    user_status ENUM('online', 'offline') DEFAULT 'offline'
) ENGINE=InnoDB;

ALTER TABLE USER_ACCOUNT
ADD COLUMN reset_token_hash VARCHAR(64) NULL,
ADD COLUMN reset_token_expires_at DATETIME NULL;

ALTER TABLE USER_ACCOUNT ADD COLUMN role VARCHAR(50) DEFAULT 'user';
UPDATE USER_ACCOUNT 
SET role = 'admin' 
WHERE email = 'marklaguador09@gmail.com';

CREATE TABLE IF NOT EXISTS BRANCH(
branch_id INT PRIMARY KEY auto_increment,
branch_name VARCHAR(100) NOT NULL,
branch_location VARCHAR(150),
contact_number VARCHAR (15)
) ENGINE=InnoDB;

INSERT INTO BRANCH (branch_name, branch_location, contact_number)
VALUES
('Light Residences', 'EDSA Cor Madison St., Brgy Barangka Ilaya, Mandaluyong City', '09171234567'),
('SM City Baguio', 'Luneta Hill, Upper Session Road, Baguio City', '09179876543'),
('SM City Marikina', 'Marcos Highway, Kalumpang, Marikina City NCR Second', '09171239876'),
('SM Aura Premier', 'McKinley Parkway, Bonifacio Global City, Taguig City', '09171239876'),
('SM Center Angono', 'E. Rodriguez Jr. Avenue, Angono, Rizal', '09171239876'),
('SM City Sta. Mesa', 'G. Araneta Ave., Sta. Mesa, Manila', '09171239876'),
('SM City Sto. Tomas', 'Poblacion, Sto. Tomas, Batangas', '09171239876'),
('SM Mall of Asia', 'Seaside Blvd., Pasay City', '09171239876'),
('SM Megacenter Cabanatuan', 'Brgy. Balintawak, Cabanatuan City, Nueva Ecija', '09171239876');

-- 2️⃣ MOVIE Table
CREATE TABLE MOVIE(
    movie_show_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(50),
    genre VARCHAR(100),
    duration INT,
    rating VARCHAR(20),
    movie_descrp TEXT,
    image_poster VARCHAR(100),
    carousel_image VARCHAR(100),
    now_showing BOOLEAN DEFAULT FALSE,
    coming_soon BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB;

ALTER TABLE MOVIE ADD COLUMN delete_at DATETIME DEFAULT NULL;
ALTER TABLE MOVIE MODIFY delete_at DATETIME NULL DEFAULT NULL;

ALTER TABLE MOVIE ADD COLUMN is_deleted TINYINT(1) DEFAULT 0;
ALTER TABLE MOVIE ADD COLUMN deleted_at DATETIME NULL;
CREATE INDEX idx_is_deleted ON MOVIE(is_deleted);

ALTER TABLE MOVIE ADD COLUMN carousel_image VARCHAR(100);
ALTER TABLE MOVIE MODIFY COLUMN genre VARCHAR(100);
ALTER TABLE MOVIE ADD COLUMN now_showing BOOLEAN DEFAULT FALSE;
ALTER TABLE MOVIE ADD COLUMN coming_soon BOOLEAN DEFAULT FALSE;

-- 3️⃣ MOVIE_SCHEDULE Table
CREATE TABLE MOVIE_SCHEDULE(
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    movie_show_id INT NOT NULL,
    show_date DATE,
    show_hour TIME,
    FOREIGN KEY (movie_show_id) REFERENCES MOVIE(movie_show_id)
) ENGINE=InnoDB;

ALTER TABLE MOVIE_SCHEDULE
ADD COLUMN branch_id INT NOT NULL;

ALTER TABLE MOVIE_SCHEDULE
ADD CONSTRAINT fk_branch FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id);

INSERT INTO MOVIE_SCHEDULE (movie_show_id, show_date, show_hour, branch_id)
VALUES
-- Light Residences (branch_id = 1)
(1, CURDATE(), '12:00:00', 31),
(1, CURDATE(), '16:00:00', 31),
(2, CURDATE(), '14:00:00', 31),
(2, CURDATE(), '18:00:00', 31),

-- SM City Baguio (branch_id = 2)
(1, CURDATE(), '11:00:00', 32),
(1, CURDATE(), '15:00:00', 32),
(2, CURDATE(), '13:00:00', 32),
(2, CURDATE(), '17:00:00', 32),

-- SM City Marikina (branch_id = 3)
(1, CURDATE(), '12:30:00', 33),
(1, CURDATE(), '16:30:00', 33),
(2, CURDATE(), '14:30:00', 33),
(2, CURDATE(), '18:30:00', 33),

-- SM Aura Premier (branch_id = 4)
(1, CURDATE(), '11:15:00', 34), 
(1, CURDATE(), '15:15:00', 34),
(2, CURDATE(), '13:15:00', 34),
(2, CURDATE(), '17:15:00', 34),

-- SM Center Angono (branch_id = 5)
(1, CURDATE(), '12:00:00', 35),
(1, CURDATE(), '16:00:00', 35),
(2, CURDATE(), '14:00:00', 35),
(2, CURDATE(), '18:00:00', 35),

-- SM City Sta. Mesa (branch_id = 6)
(1, CURDATE(), '11:45:00', 36),
(1, CURDATE(), '15:45:00', 36),
(2, CURDATE(), '13:45:00', 36),
(2, CURDATE(), '17:45:00', 36),


-- SM City Sto. Tomas (branch_id = 7)
(1, CURDATE(), '12:30:00', 37),
(1, CURDATE(), '16:30:00', 37),
(2, CURDATE(), '14:30:00', 37),
(2, CURDATE(), '18:30:00', 37),

-- SM Mall of Asia (branch_id = 8)
(1, CURDATE(), '11:00:00', 38), 
(1, CURDATE(), '15:00:00', 38),
(2, CURDATE(), '13:00:00', 38),
(2, CURDATE(), '17:00:00', 38),

-- SM Megacenter Cabanatuan (branch_id = 9)
(1, CURDATE(), '12:15:00', 39), 
(1, CURDATE(), '16:15:00', 39),
(2, CURDATE(), '14:15:00', 39),
(2, CURDATE(), '18:15:00', 39);

-- 4️⃣ SEAT Table
CREATE TABLE SEAT(
    seat_id INT PRIMARY KEY AUTO_INCREMENT,
    seat_number VARCHAR(10),
    seat_type ENUM('Regular','VIP') DEFAULT 'Regular',
    seat_price DECIMAL(10,2)
) ENGINE=InnoDB;

-- 5️⃣ RESERVE Table
CREATE TABLE RESERVE(
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    acc_id INT,
    schedule_id INT,
    reserve_date DATETIME,
    ticket_amount INT,
    sum_price DECIMAL(10,2),
    FOREIGN KEY (acc_id) REFERENCES USER_ACCOUNT(acc_id),
    FOREIGN KEY (schedule_id) REFERENCES MOVIE_SCHEDULE(schedule_id)
) ENGINE=InnoDB;
ALTER TABLE RESERVE MODIFY COLUMN booking_status ENUM('pending','approved','rejected','declined') DEFAULT 'pending';
ALTER TABLE RESERVE ADD COLUMN food_total DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE RESERVE ADD COLUMN booking_status ENUM('pending','approved','rejected') DEFAULT 'pending';



-- 6️⃣ RESERVE_SEAT Table
CREATE TABLE RESERVE_SEAT(
    reserve_seat_id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT,
    seat_id INT,
    FOREIGN KEY (reservation_id) REFERENCES RESERVE(reservation_id),
    FOREIGN KEY (seat_id) REFERENCES SEAT(seat_id)
) ENGINE=InnoDB;

-- 7️⃣ PAYMENT Table
CREATE TABLE PAYMENT(
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    reserve_id INT,
    payment_type ENUM('cash','credit','e-wallet'),
    amount_paid DECIMAL(10,2),
    payment_status ENUM('paid','pending','not-yet'),
    payment_date DATETIME,
    reference_number VARCHAR(100),
    FOREIGN KEY (reserve_id) REFERENCES RESERVE(reservation_id)
) ENGINE=InnoDB;

CREATE TABLE TICKET(
ticket_id INT PRIMARY KEY auto_increment,
reserve_id INT,
payment_id INT,
ticket_number VARCHAR(50),
date_issued DATETIME,
ticket_status ENUM('valid','cancelled','refunded'),
FOREIGN KEY (payment_id) REFERENCES PAYMENT(payment_id),
FOREIGN KEY (reserve_id) REFERENCES RESERVE(reservation_id)
);

ALTER TABLE TICKET
ADD COLUMN e_ticket_code VARCHAR(100) UNIQUE,
ADD COLUMN e_ticket_file VARCHAR(255);
ALTER TABLE TICKET MODIFY ticket_status ENUM('valid','cancelled','refunded');

CREATE TABLE FOOD (
food_id INT PRIMARY KEY AUTO_INCREMENT,
food_name VARCHAR(50) NOT NULL,
food_price DECIMAL(10,2) DEFAULT 0.00
) ENGINE=InnoDB;
ALTER TABLE FOOD ADD COLUMN image_path VARCHAR(255);

INSERT INTO FOOD(food_name, food_price, image_path) VALUES
('All-In-Combo', 199.00, 'images/all-in.png'),
('HotCoke', 165.00, 'images/hotdog-coke.png'),
('Froke', 120.00, 'images/fries-coke.png'),
('Fries', 50.00, 'images/fries-solo.png'),
('Hotdog', 60.00, 'images/hotdog-solo.png'),
('Coke', 40.00, 'images/coke-solo.png'),
('Popcorn', 40.00, 'images/popcorn-solo.png');

CREATE TABLE TICKET_FOOD (
    ticket_food_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (ticket_id) REFERENCES TICKET(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES FOOD(food_id) ON DELETE CASCADE,
    UNIQUE KEY unique_ticket_food (ticket_id, food_id)
) ENGINE=InnoDB;
ALTER TABLE TICKET_FOOD 
ADD UNIQUE KEY unique_ticket_food (ticket_id, food_id);
ALTER TABLE TICKET_FOOD
ADD COLUMN IF NOT EXISTS ticket_food_id INT AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE TICKET_FOOD
ADD COLUMN quantity INT NOT NULL DEFAULT 1;

CREATE TABLE USER_PAYMENT_METHODS (
    payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
    acc_id INT NOT NULL,
    method_type VARCHAR(50) NOT NULL,        -- e.g. 'credit_card', 'gcash', 'maya'
    card_number VARCHAR(20),                  -- last 4 digits only for display
    card_holder VARCHAR(100),
    expiry_date VARCHAR(10),
    is_default TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acc_id) REFERENCES USER_ACCOUNT(acc_id) ON DELETE CASCADE
);
ALTER TABLE USER_PAYMENT_METHODS 
ADD COLUMN payment_type VARCHAR(50);
ALTER TABLE USER_PAYMENT_METHODS 
ADD COLUMN card_name VARCHAR(100),
ADD COLUMN card_expiry VARCHAR(10),
ADD COLUMN card_cvv VARCHAR(5),
ADD COLUMN paypal_email VARCHAR(100);
ALTER TABLE USER_PAYMENT_METHODS MODIFY COLUMN method_type VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE USER_PAYMENT_METHODS ADD COLUMN phone_number VARCHAR(20);

-- Add booking_status to RESERVE

-- Add 'pending' to TICKET status ENUM
ALTER TABLE TICKET MODIFY ticket_status ENUM('valid','cancelled','refunded','pending') DEFAULT 'pending';


SELECT * FROM BRANCH;
SELECT * FROM USER_ACCOUNT;
SELECT * FROM MOVIE;
SELECT * FROM MOVIE_SCHEDULE;
SELECT * FROM SEAT;
SELECT * FROM RESERVE;
SELECT * FROM RESERVE_SEAT;
SELECT * FROM PAYMENT;
SELECT * FROM TICKET;
SELECT * FROM FOOD;
SELECT * FROM TICKET_FOOD;
SHOW COLUMNS FROM RESERVE;