-- Add sample movies to the database for search functionality
USE TICKETIX;

-- Insert sample movies
INSERT INTO MOVIE (title, genre, duration, rating, movie_descrp, image_poster) VALUES
('Tron: Ares', 'Sci-Fi/Action', 135, 'PG-13', 'A thrilling sci-fi adventure set in the digital world of Tron, where a new hero must navigate the dangerous landscape of cyberspace to save both the digital and real worlds.', 'images/TRON.png'),
('Chainsaw Man', 'Action/Horror', 105, 'R', 'A dark and intense action-horror film following a young man who becomes a devil hunter after merging with his pet devil Pochita.', 'images/CHAINSAWMAN.jpg'),
('Black Phone', 'Horror/Thriller', 103, 'R', 'A psychological horror thriller about a kidnapped boy who receives mysterious phone calls from the killer''s previous victims.', 'images/BLACKPHONE.png'),
('Good Boy', 'Comedy/Family', 90, 'PG', 'A heartwarming family comedy about a mischievous dog who learns valuable life lessons while getting into hilarious situations.', 'images/GOODBOY.png'),
('Quezon', 'Drama/Historical', 150, 'PG-13', 'A powerful historical drama chronicling the life and political career of Manuel L. Quezon, the first President of the Commonwealth of the Philippines.', 'images/QUEZON.jpg'),
('One in a Million', 'Romance/Drama', 125, 'PG-13', 'A touching romantic drama about two people from different worlds who find love against all odds in this modern fairy tale.', 'images/ONEINAMILLION.png'),
('Shelby', 'Action/Thriller', 115, 'R', 'An intense action thriller following a skilled assassin who must complete one final mission while being hunted by his own organization.', 'images/SHELBY.png'),
('Now You See Me 3', 'Thriller/Mystery', 130, 'PG-13', 'The latest installment in the mind-bending thriller series featuring master illusionists who use their skills for elaborate heists.', 'images/NOWYOUSEEME.jpg'),
('Predator: The Hunt', 'Sci-Fi/Horror', 110, 'R', 'A terrifying sci-fi horror film where a group of elite soldiers must survive against an advanced alien predator in the jungle.', 'images/PREDATOR.jpg'),
('Meet Greet Bye', 'Comedy/Romance', 100, 'PG', 'A delightful romantic comedy about two people who meet at an airport and embark on an unexpected journey of love and self-discovery.', 'images/MEETGREETBYE.jpg');

-- Add some movie schedules for now showing movies
INSERT INTO MOVIE_SCHEDULE (movie_show_id, show_date, show_hour) VALUES
(1, CURDATE(), '10:00:00'),
(1, CURDATE(), '13:00:00'),
(1, CURDATE(), '16:00:00'),
(1, CURDATE(), '19:00:00'),
(1, CURDATE(), '22:00:00'),
(2, CURDATE(), '11:00:00'),
(2, CURDATE(), '14:00:00'),
(2, CURDATE(), '17:00:00'),
(2, CURDATE(), '20:00:00'),
(3, CURDATE(), '12:00:00'),
(3, CURDATE(), '15:00:00'),
(3, CURDATE(), '18:00:00'),
(3, CURDATE(), '21:00:00'),
(4, CURDATE(), '10:30:00'),
(4, CURDATE(), '13:30:00'),
(4, CURDATE(), '16:30:00'),
(4, CURDATE(), '19:30:00'),
(5, CURDATE(), '11:30:00'),
(5, CURDATE(), '14:30:00'),
(5, CURDATE(), '17:30:00'),
(5, CURDATE(), '20:30:00');

-- Add some future schedules for coming soon movies
INSERT INTO MOVIE_SCHEDULE (movie_show_id, show_date, show_hour) VALUES
(6, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '10:00:00'),
(6, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '13:00:00'),
(6, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '16:00:00'),
(6, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '19:00:00'),
(7, DATE_ADD(CURDATE(), INTERVAL 22 DAY), '11:00:00'),
(7, DATE_ADD(CURDATE(), INTERVAL 22 DAY), '14:00:00'),
(7, DATE_ADD(CURDATE(), INTERVAL 22 DAY), '17:00:00'),
(7, DATE_ADD(CURDATE(), INTERVAL 22 DAY), '20:00:00'),
(8, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00'),
(8, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '15:00:00'),
(8, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '18:00:00'),
(8, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '21:00:00'),
(9, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '10:30:00'),
(9, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '13:30:00'),
(9, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '16:30:00'),
(9, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '19:30:00'),
(10, DATE_ADD(CURDATE(), INTERVAL 20 DAY), '11:30:00'),
(10, DATE_ADD(CURDATE(), INTERVAL 20 DAY), '14:30:00'),
(10, DATE_ADD(CURDATE(), INTERVAL 20 DAY), '17:30:00'),
(10, DATE_ADD(CURDATE(), INTERVAL 20 DAY), '20:30:00');