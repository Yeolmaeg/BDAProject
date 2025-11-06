
SET NAMES utf8mb4;
USE team04;

START TRANSACTION;

INSERT INTO users (user_name, user_bdate, user_phone, user_email, user_pass) VALUES
('Kim Min-ji',  '2007-03-15', '010-1234-5678', 'gildong@example.com',  'pass1234'),
('Lee Yeon-jung',  '2000-11-02', '010-2345-6789', 'younghee@example.com', 'qwer1234'),
('Jin Sun-in',  '1997-07-21', '010-3456-7890', 'chulsoo@example.com',  'admin123'),
('Ha Sam',  '1999-01-05', '010-4567-8901', 'minsu@example.com',    'pw0000'),
('Park Jae-yong',  '2001-05-30', '010-5678-9012', 'soomin@example.com',   'test1234');

COMMIT;
