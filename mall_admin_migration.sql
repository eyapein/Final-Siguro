-- Mall Admin Migration
-- Run this file to set up Mall Admin role, Cinema Numbers, and Assignments

USE TICKETIX;

-- 1. CINEMA_NUMBER table - Each branch has multiple cinema screens
CREATE TABLE IF NOT EXISTS CINEMA_NUMBER (
    cinema_number_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    cinema_name VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 100,
    FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. CINEMA_MOVIE_ASSIGNMENT table - Links movies to cinema numbers
CREATE TABLE IF NOT EXISTS CINEMA_MOVIE_ASSIGNMENT (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    cinema_number_id INT NOT NULL,
    movie_show_id INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NOT NULL,
    FOREIGN KEY (cinema_number_id) REFERENCES CINEMA_NUMBER(cinema_number_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_show_id) REFERENCES MOVIE(movie_show_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES USER_ACCOUNT(acc_id)
) ENGINE=InnoDB;

-- 3. MALL_ADMIN_BRANCH table - Links mall admin to their branch
CREATE TABLE IF NOT EXISTS MALL_ADMIN_BRANCH (
    id INT PRIMARY KEY AUTO_INCREMENT,
    acc_id INT NOT NULL,
    branch_id INT NOT NULL,
    FOREIGN KEY (acc_id) REFERENCES USER_ACCOUNT(acc_id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id) ON DELETE CASCADE,
    UNIQUE KEY unique_admin_branch (acc_id, branch_id)
) ENGINE=InnoDB;

-- 4. Set eyabananaaa0828@gmail.com as mall_admin role
UPDATE USER_ACCOUNT SET role = 'mall_admin' WHERE email = 'eyabananaaa0828@gmail.com';

-- 5. Assign mall admin to Light Residences (branch_id = 1)
-- (Only runs if the user exists)
INSERT IGNORE INTO MALL_ADMIN_BRANCH (acc_id, branch_id)
SELECT acc_id, 1 FROM USER_ACCOUNT WHERE email = 'eyabananaaa0828@gmail.com';

-- 6. Seed cinema numbers for ALL branches (3 cinemas each)
INSERT INTO CINEMA_NUMBER (branch_id, cinema_name, capacity) VALUES
-- Light Residences (branch_id depends on actual data)
(1, 'Cinema 1', 100), (1, 'Cinema 2', 80), (1, 'Cinema 3', 120),
-- SM City Baguio
(2, 'Cinema 1', 100), (2, 'Cinema 2', 80), (2, 'Cinema 3', 120),
-- SM City Marikina
(3, 'Cinema 1', 100), (3, 'Cinema 2', 80), (3, 'Cinema 3', 120),
-- SM Aura Premier
(4, 'Cinema 1', 100), (4, 'Cinema 2', 80), (4, 'Cinema 3', 120),
-- SM Center Angono
(5, 'Cinema 1', 100), (5, 'Cinema 2', 80), (5, 'Cinema 3', 120),
-- SM City Sta. Mesa
(6, 'Cinema 1', 100), (6, 'Cinema 2', 80), (6, 'Cinema 3', 120),
-- SM City Sto. Tomas
(7, 'Cinema 1', 100), (7, 'Cinema 2', 80), (7, 'Cinema 3', 120),
-- SM Mall of Asia
(8, 'Cinema 1', 100), (8, 'Cinema 2', 80), (8, 'Cinema 3', 120),
-- SM Megacenter Cabanatuan
(9, 'Cinema 1', 100), (9, 'Cinema 2', 80), (9, 'Cinema 3', 120);
