-- author: Sumin Son

USE team04;

/* ===== KIA Tigers ===== */

SET @team_name := 'KIA Tigers';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kang I-jun' AS player_name, 'pitcher' AS position, 25 AS age, 'Republic of Korea' AS nationality, 30000000 AS salary
  UNION ALL SELECT 'Kwak Do-gyu','pitcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Geon-guk','pitcher',19,'Republic of Korea',40000000
  UNION ALL SELECT 'Kim Ki-hoon','pitcher',24,'Republic of Korea',40000000
  UNION ALL SELECT 'Kim Dae-yu','pitcher',30,'Republic of Korea',110000000
  UNION ALL SELECT 'Kim Min-joo','pitcher',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Sa-yoon','pitcher',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Kim Seung-hyun','pitcher',20,'Republic of Korea',41000000
  UNION ALL SELECT 'Kim Yu-shin','pitcher',21,'Republic of Korea',42000000
  UNION ALL SELECT 'Kim Chan-min','pitcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Hyun-soo (00.07.10)','pitcher',24,'Republic of Korea',45000000
  UNION ALL SELECT 'Park Jun-pyo','pitcher',32,'Republic of Korea',80000000
  UNION ALL SELECT 'Yang Hyeon-jong','pitcher',36,'Republic of Korea',500000000
  UNION ALL SELECT 'Yoo Seung-cheol','pitcher',21,'Republic of Korea',36000000
  UNION ALL SELECT 'Yoo Ji-seong','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Yoon Young-cheol','pitcher',20,'Republic of Korea',90000000
  UNION ALL SELECT 'Lee Eui-ri','pitcher',22,'Republic of Korea',170000000
  UNION ALL SELECT 'Lee Jun-young','pitcher',25,'Republic of Korea',140000000
  UNION ALL SELECT 'Lee Hyeong-beom','pitcher',30,'Republic of Korea',70000000
  UNION ALL SELECT 'Lim Gi-young','pitcher',31,'Republic of Korea',250000000
  UNION ALL SELECT 'Jang Min-gi','pitcher',25,'Republic of Korea',35000000
  UNION ALL SELECT 'Jang Hyun-sik','pitcher',30,'Republic of Korea',160000000
  UNION ALL SELECT 'Jeon Sang-hyun','pitcher',28,'Republic of Korea',170000000
  UNION ALL SELECT 'Jung Hae-young','pitcher',24,'Republic of Korea',200000000
  UNION ALL SELECT 'Jo Dae-hyun','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Ji-min','pitcher',21,'Republic of Korea',100000000
  UNION ALL SELECT 'Hwang Dong-ha','pitcher',20,'Republic of Korea',35000000
  UNION ALL SELECT 'James Naile','pitcher',31,'United States of America',785000000
  UNION ALL SELECT 'William Crowe','pitcher',30,'United States of America',1140000000
  UNION ALL SELECT 'Kim Tae-gun','catcher',35,'Republic of Korea',700000000
  UNION ALL SELECT 'Lee Sang-jun','catcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Joo Hyo-sang','catcher',28,'Republic of Korea',44000000
  UNION ALL SELECT 'Han Seung-taek','catcher',30,'Republic of Korea',65000000
  UNION ALL SELECT 'Han Joon-soo','catcher',26,'Republic of Korea',50000000
  UNION ALL SELECT 'Ko Myeong-seong','infielder',27,'Republic of Korea',31000000
  UNION ALL SELECT 'Kim Gyu-seong','infielder',26,'Republic of Korea',55000000
  UNION ALL SELECT 'Kim Do-young','infielder',27,'Republic of Korea',100000000
  UNION ALL SELECT 'Kim Sun-bin','infielder',35,'Republic of Korea',600000000
  UNION ALL SELECT 'Park Min','infielder',22,'Republic of Korea',35000000
  UNION ALL SELECT 'Park Chan-ho','infielder',28,'Republic of Korea',300000000
  UNION ALL SELECT 'Byun Woo-hyuk','infielder',24,'Republic of Korea',60000000
  UNION ALL SELECT 'Seo Geon-chang','infielder',38,'Republic of Korea',50000000
  UNION ALL SELECT 'Oh Seon-woo','infielder',24,'Republic of Korea',33000000
  UNION ALL SELECT 'Yoon Do-hyun','infielder',24,'Republic of Korea',30000000
  UNION ALL SELECT 'Jung Hae-won','infielder',22,'Republic of Korea',31000000
  UNION ALL SELECT 'Choi Jeong-yong','infielder',22,'Republic of Korea',43000000
  UNION ALL SELECT 'Hong Jong-pyo','infielder',22,'Republic of Korea',35000000
  UNION ALL SELECT 'Hwang Dae-in','infielder',23,'Republic of Korea',80000000
  UNION ALL SELECT 'Ko Jong-wook','outfielder',28,'Republic of Korea',150000000
  UNION ALL SELECT 'Kim Seok-hwan','outfielder',31,'Republic of Korea',40000000
  UNION ALL SELECT 'Kim Ho-ryeong','outfielder',26,'Republic of Korea',90000000
  UNION ALL SELECT 'Na Sung-beom','outfielder',32,'Republic of Korea',800000000
  UNION ALL SELECT 'Park Jung-woo','outfielder',24,'Republic of Korea',38000000
  UNION ALL SELECT 'Lee Woo-seong','outfielder',30,'Republic of Korea',130000000
  UNION ALL SELECT 'Lee Chang-jin','outfielder',29,'Republic of Korea',120000000
  UNION ALL SELECT 'Choi Won-jun (97.03.23)','outfielder',27,'Republic of Korea',220000000
  UNION ALL SELECT 'Choi Hyung-woo','outfielder',41,'Republic of Korea',1000000000
  UNION ALL SELECT 'Socrates Brito','outfielder',32,'Dominican Republic',1140000000
  UNION ALL SELECT 'Kim Doo-hyun','infielder',21, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Kim Do-hyun', 'pitcher', 24, 'Republic of Korea', 90000000
  UNION ALL SELECT 'Jang Jae-hyuk', 'pitcher',23, 'Republic of Korea', 31000000
  UNION ALL SELECT 'Kim Min-jae', 'pitcher',21, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Cam Alldred', 'pitcher',29, 'United States of America', 437000000  
  UNION ALL SELECT 'Park Jeong-woo', 'outfielder',26, 'Republic of Korea', 65000000
  UNION ALL SELECT 'Eric Lauer', 'pitcher', 29, 'United States of America', 437000000  
  UNION ALL SELECT 'Eric Stout', 'pitcher', 31, 'United States of America', 65500000  
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== Samsung Lions ===== */
SET @team_name := 'Samsung Lions';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kim Dae-woo' AS player_name, 'pitcher' AS position, 35 AS age, 'Republic of Korea' AS nationality, 100000000 AS salary
  UNION ALL SELECT 'Kim Seo-jun','pitcher',21,'Republic of Korea',34000000
  UNION ALL SELECT 'Kim Si-hyun','pitcher',22,'Republic of Korea',37000000
  UNION ALL SELECT 'Kim Jae-yoon','pitcher',24,'Republic of Korea',400000000
  UNION ALL SELECT 'Kim Tae-hoon (92.03.02)','pitcher',32,'Republic of Korea',170000000
  UNION ALL SELECT 'Park Kwon-hoo','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Park Jun-yong','pitcher',28,'Republic of Korea',30000000
  UNION ALL SELECT 'Baek Jung-hyun','pitcher',37,'Republic of Korea',400000000
  UNION ALL SELECT 'Seo Hyun-won','pitcher',22,'Republic of Korea',30000000
  UNION ALL SELECT 'Yang Hyun','pitcher',32,'Republic of Korea',90000000
  UNION ALL SELECT 'Oh Seung-hwan','pitcher',42,'Republic of Korea',400000000
  UNION ALL SELECT 'Oh Jae-il (Before Trade)','infielder',38,'Republic of Korea',500000000  
  UNION ALL SELECT 'Won Tae-in','pitcher',24,'Republic of Korea',430000000
  UNION ALL SELECT 'Yook Sun-yeop','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Sang-min','pitcher',34,'Republic of Korea',65000000
  UNION ALL SELECT 'Lee Seung-min (00.08.26)','pitcher',24,'Republic of Korea',41000000
  UNION ALL SELECT 'Lee Seung-hyun (02.05.19)','pitcher',22,'Republic of Korea',70000000
  UNION ALL SELECT 'Lee Seung-hyun (91.11.20)','pitcher',33,'Republic of Korea',170000000
  UNION ALL SELECT 'Lee Jae-ik','pitcher',30,'Republic of Korea',82000000
  UNION ALL SELECT 'Lee Ho-sung','pitcher',21,'Republic of Korea',32000000
  UNION ALL SELECT 'Lim Chang-min','pitcher',39,'Republic of Korea',200000000
  UNION ALL SELECT 'Jang Pil-jun','pitcher',34,'Republic of Korea',70000000
  UNION ALL SELECT 'Jung Min-sung','pitcher',28,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Sung-hoon','pitcher',35,'Republic of Korea',100000000
  UNION ALL SELECT 'Choi Ji-gwang','pitcher',25,'Republic of Korea',140000000
  UNION ALL SELECT 'Choi Chae-heung','pitcher',27,'Republic of Korea',150000000
  UNION ALL SELECT 'Choi Choong-yeon','pitcher',27,'Republic of Korea',47000000
  UNION ALL SELECT 'Choi Ha-neul','pitcher',22,'Republic of Korea',41000000
  UNION ALL SELECT 'Hong Moo-won','pitcher',21,'Republic of Korea',35000000
  UNION ALL SELECT 'Hong Won-pyo','pitcher',21,'Republic of Korea',33000000
  UNION ALL SELECT 'Hong Jung-woo','pitcher',21,'Republic of Korea',60000000
  UNION ALL SELECT 'Hwang Dong-jae','pitcher',23,'Republic of Korea',41000000
  UNION ALL SELECT 'Denyi Reyes','pitcher',30,'Dominican Republic',850000000
  UNION ALL SELECT 'Connor Seabold','pitcher',28,'United States of America',1200000000
  UNION ALL SELECT 'Kang Min-ho','catcher',39,'Republic of Korea',400000000
  UNION ALL SELECT 'Kim Do-hwan','catcher',24,'Republic of Korea',50000000
  UNION ALL SELECT 'Kim Min-soo (91.03.02)','catcher',33,'Republic of Korea',46000000
  UNION ALL SELECT 'Kim Jae-sung','catcher',29,'Republic of Korea',70000000
  UNION ALL SELECT 'Lee Byung-heon (99.10.26)','catcher',25,'Republic of Korea',40000000
  UNION ALL SELECT 'Kang Han-ul','infielder',34,'Republic of Korea',100000000
  UNION ALL SELECT 'Gong Min-gyu','infielder',26,'Republic of Korea',41000000
  UNION ALL SELECT 'Kim Dong-jin','infielder',25,'Republic of Korea',45000000
  UNION ALL SELECT 'Kim Young-woong','infielder',24,'Republic of Korea',38000000
  UNION ALL SELECT 'Kim Jae-sang','infielder',22,'Republic of Korea',32000000
  UNION ALL SELECT 'Kim Ji-chan','infielder',24,'Republic of Korea',160000000
  UNION ALL SELECT 'Kim Ho-jin','infielder',25,'Republic of Korea',30000000
  UNION ALL SELECT 'Park Byung-ho (After Trade)','infielder',38,'Republic of Korea',700000000
  UNION ALL SELECT 'Ryu Ji-hyeok','infielder',26,'Republic of Korea',200000000
  UNION ALL SELECT 'Ahn Joo-hyung','infielder',38,'Republic of Korea',52000000
  UNION ALL SELECT 'Lee Jae-hyun','infielder',29,'Republic of Korea',140000000
  UNION ALL SELECT 'Jeon Byung-woo','infielder',29,'Republic of Korea',60000000
  UNION ALL SELECT 'David MacKinnon','infielder',31,'United States of America',1430000000
  UNION ALL SELECT 'Koo Ja-wook','outfielder',31,'Republic of Korea',2000000000
  UNION ALL SELECT 'Kim Dong-yeop','outfielder',30,'Republic of Korea',80000000
  UNION ALL SELECT 'Kim Sung-yoon','outfielder',25,'Republic of Korea',100000000
  UNION ALL SELECT 'Kim Jae-hyuk','outfielder',24,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Tae-hoon (96.03.31)','outfielder',28,'Republic of Korea',41000000
  UNION ALL SELECT 'Kim Heon-gon','outfielder',22,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Hyun-joon','outfielder',22,'Republic of Korea',140000000
  UNION ALL SELECT 'Ryu Seung-min','outfielder',33,'Republic of Korea',35000000
  UNION ALL SELECT 'Yoon Jung-bin','outfielder',22,'Republic of Korea',37000000
  UNION ALL SELECT 'Lee Sung-kyu','outfielder',28,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Hun-gon','outfielder', 37, 'Republic of Korea', 100000000
  UNION ALL SELECT 'Lewin Díaz', 'infielder', 29, 'Dominican Republic', 723500000
  UNION ALL SELECT 'Yang Do-geun', 'infielder', 21, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Yang Woo-hyun', 'infielder', 24, 'Dominican Republic', 40000000
  UNION ALL SELECT 'Lee Chang-yong', 'infielder', 25, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Ruben Cardenas', 'outfielder', 27, 'United States of America', 618000000
  UNION ALL SELECT 'Kim Mu-shin', 'pitcher', 25, 'Republic of Korea', 70000000
  UNION ALL SELECT 'Song Eun-beom', 'pitcher', 40, 'Republic of Korea', 60000000 
  UNION ALL SELECT 'Kim Dae-ho', 'pitcher', 23, 'Republic of Korea', 30000000
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;



/* ===== LG Twins ===== */
SET @team_name := 'LG Twins';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kang Hyo-jong' AS player_name, 'pitcher' AS position, 22 AS age, 'Republic of Korea' AS nationality, 38000000 AS salary
  UNION ALL SELECT 'Kim Dae-hyun','pitcher',27,'Republic of Korea',57000000
  UNION ALL SELECT 'Kim Young-jun','pitcher',25,'Republic of Korea',36000000
  UNION ALL SELECT 'Kim Yu-young','pitcher',30,'Republic of Korea',67000000
  UNION ALL SELECT 'Kim Yun-sik','pitcher',24,'Republic of Korea',120000000
  UNION ALL SELECT 'Kim Jin-sung','pitcher',39,'Republic of Korea',200000000
  UNION ALL SELECT 'Kim Jin-soo','pitcher',27,'Republic of Korea',32000000
  UNION ALL SELECT 'Park Myung-geun','pitcher',21,'Republic of Korea',65000000
  UNION ALL SELECT 'Bae Jae-joon','pitcher',30,'Republic of Korea',60000000
  UNION ALL SELECT 'Baek Seung-hyun','pitcher',29,'Republic of Korea',92000000
  UNION ALL SELECT 'Seong Dong-hyun','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Son Ho-young (Before Trade)','infielder',30,'Republic of Korea',45000000  
  UNION ALL SELECT 'Son Ju-young','pitcher',25,'Republic of Korea',43000000
  UNION ALL SELECT 'Yoo Young-chan','pitcher',20,'Republic of Korea',85000000
  UNION ALL SELECT 'Yoon Ho-sol','pitcher',29,'Republic of Korea',70000000
  UNION ALL SELECT 'Lee Mid-eum','pitcher',31,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Sang-young','pitcher',25,'Republic of Korea',50000000
  UNION ALL SELECT 'Lee Woo-chan','pitcher',29,'Republic of Korea',125000000
  UNION ALL SELECT 'Lee Jong-jun','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Ji-gang','pitcher',21,'Republic of Korea',68000000
  UNION ALL SELECT 'Lim Chan-kyu','pitcher',32,'Republic of Korea',200000000
  UNION ALL SELECT 'Woo Gang-hoon','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Jung Woo-young','pitcher',24,'Republic of Korea',320000000
  UNION ALL SELECT 'Jung Ji-heon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jin Woo-young','pitcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Dong-hwan','pitcher',30,'Republic of Korea',130000000
  UNION ALL SELECT 'Choi Won-tae','pitcher',27,'Republic of Korea',400000000
  UNION ALL SELECT 'Ham Deok-joo','pitcher',30,'Republic of Korea',200000000
  UNION ALL SELECT 'Dietrich Enns','pitcher',33,'United States of America',1715000000
  UNION ALL SELECT 'Casey Kelly','pitcher',35,'United States of America',1286000000
  UNION ALL SELECT 'Kim Beom-seok','catcher',35,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Sung-woo','catcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Park Dong-won','catcher',24,'Republic of Korea',2500000000
  UNION ALL SELECT 'Jeon Jun-ho','catcher',34,'Republic of Korea',33000000
  UNION ALL SELECT 'Heo Do-hwan','catcher',40,'Republic of Korea',100000000
  UNION ALL SELECT 'Koo Bon-hyuk','infielder',26,'Republic of Korea',70000000
  UNION ALL SELECT 'Kim Dae-won','infielder',22,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Min-soo (98.03.18)','infielder',26,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Sung-jin','infielder',22,'Republic of Korea',31000000
  UNION ALL SELECT 'Kim Ju-seong','infielder',20,'Republic of Korea',35000000
  UNION ALL SELECT 'Kim Tae-woo','infielder',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Moon Bo-gyeong','infielder',25,'Republic of Korea',300000000
  UNION ALL SELECT 'Son Yong-jun','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Song Chan-ui','infielder',22,'Republic of Korea',36000000
  UNION ALL SELECT 'Shin Min-jae','infielder',24,'Republic of Korea',115000000
  UNION ALL SELECT 'Oh Ji-hwan','infielder',29,'Republic of Korea',300000000
  UNION ALL SELECT 'Austin Dean','infielder',34,'United States of America',1570000000
  UNION ALL SELECT 'Kim Hyun-soo (88.01.12)','outfielder',36,'Republic of Korea',1000000000
  UNION ALL SELECT 'Kim Hyun-jong','outfielder',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Moon Sung-joo','outfielder',32,'Republic of Korea',200000000
  UNION ALL SELECT 'Park Hae-min','outfielder',34,'Republic of Korea',600000000
  UNION ALL SELECT 'Ahn Ik-hoon','outfielder',34,'Republic of Korea',55000000
  UNION ALL SELECT 'Lee Jae-won (99.07.17)','outfielder',25,'Republic of Korea',70000000
  UNION ALL SELECT 'Choi Seung-min','outfielder',29,'Republic of Korea',40000000
  UNION ALL SELECT 'Hong Chang-ki','outfielder',29,'Republic of Korea',510000000
  UNION ALL SELECT 'Kim Su-in', 'infielder',27,'Republic of Korea', 30000000
  UNION ALL SELECT 'Lee Young-bin','infielder', 22, 'Republic of Korea', 60000000 
  UNION ALL SELECT 'Lee Ju-heon', 'catcher', 21, 'Republic of Korea', 33000000
  UNION ALL SELECT 'Lim Jun-hyung', 'pitcher', 24, 'Republic of Korea', 55000000 
  UNION ALL SELECT 'Choi Myeong-kyung', 'infielder', 23, 'Republic of Korea', 30000000 
  UNION ALL SELECT 'Choi Won-young', 'outfielder', 21, 'Republic of Korea', 30000000 
  UNION ALL SELECT 'Ham Chang-geun', 'outfielder', 23, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Elieser Hernández', 'pitcher', 29, 'United States of America', 1164000000  
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== Doosan Bears ===== */
SET @team_name := 'Doosan Bears';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kwak Bin' AS player_name, 'pitcher' AS position, 25 AS age, 'Republic of Korea' AS nationality, 210000000 AS salary
  UNION ALL SELECT 'Kim Kang-ryul','pitcher',35,'Republic of Korea',150000000
  UNION ALL SELECT 'Kim Dong-joo','pitcher',20,'Republic of Korea',55000000
  UNION ALL SELECT 'Kim Myung-shin','pitcher',28,'Republic of Korea',225000000
  UNION ALL SELECT 'Kim Min-kyu','pitcher',25,'Republic of Korea',50000000
  UNION ALL SELECT 'Kim Yoo-sung','pitcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Jung-woo','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Kim Taek-yeon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Ho-jun','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Park So-jun','pitcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Park Shin-ji','pitcher',25,'Republic of Korea',35000000
  UNION ALL SELECT 'Park Jung-soo','pitcher',26,'Republic of Korea',55000000
  UNION ALL SELECT 'Park Chi-guk','pitcher',28,'Republic of Korea',130000000
  UNION ALL SELECT 'Baek Seung-woo','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Gyo-hoon','pitcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Lee Byung-heon (03.06.04)','pitcher',21,'Republic of Korea',36000000
  UNION ALL SELECT 'Lee Seung-jin','pitcher',26,'Republic of Korea',55000000
  UNION ALL SELECT 'Lee Young-ha','pitcher',27,'Republic of Korea',100000000
  UNION ALL SELECT 'Lee Won-jae','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Cheol-won','pitcher',25,'Republic of Korea',165000000
  UNION ALL SELECT 'Choi Seung-yong','pitcher',22,'Republic of Korea',102000000
  UNION ALL SELECT 'Choi Won-jun (94.12.21)','pitcher',30,'Republic of Korea',250000000
  UNION ALL SELECT 'Choi Jong-in','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Jun-ho','pitcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Ji-gang','pitcher',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Hong Geon-hee','pitcher',31,'Republic of Korea',300000000
  UNION ALL SELECT 'Brandon Waddell','pitcher',30,'United States of America',1430000000
  UNION ALL SELECT 'Raul Alcantara','pitcher',32,'Dominican Republic',1860000000
  UNION ALL SELECT 'Kim Ki-yeon','catcher',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Ahn Seung-han','catcher',26,'Republic of Korea',55000000
  UNION ALL SELECT 'Yang Eui-ji','catcher',37,'Republic of Korea',500000000
  UNION ALL SELECT 'Yoon Jun-ho','catcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jang Seung-hyun','catcher',33,'Republic of Korea',60000000
  UNION ALL SELECT 'Kang Seung-ho','infielder',30,'Republic of Korea',255000000
  UNION ALL SELECT 'Kwon Min-seok','infielder',21,'Republic of Korea',31000000
  UNION ALL SELECT 'Kim Min-hyeok (96.05.03)','infielder',28,'Republic of Korea',38000000
  UNION ALL SELECT 'Kim Jae-ho','infielder',38,'Republic of Korea',300000000
  UNION ALL SELECT 'Park Gye-beom','infielder',28,'Republic of Korea',85000000
  UNION ALL SELECT 'Park Jun-young','infielder',27,'Republic of Korea',70000000
  UNION ALL SELECT 'Park Ji-hoon','infielder',20,'Republic of Korea',36000000
  UNION ALL SELECT 'Seo Ye-il','infielder',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Yang Seok-hwan','infielder',31,'Republic of Korea',300000000
  UNION ALL SELECT 'Yeo Dong-geon','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Oh Myung-jin','infielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Yoo-chan','infielder',26,'Republic of Korea',85000000
  UNION ALL SELECT 'Lim Jong-seong','infielder',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeon Min-jae','infielder',24,'Republic of Korea',34000000
  UNION ALL SELECT 'Heo Kyung-min','infielder',34,'Republic of Korea',600000000
  UNION ALL SELECT 'Kim Dae-han','outfielder',25,'Republic of Korea',37000000
  UNION ALL SELECT 'Kim Moon-soo','outfielder',24,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim In-tae','outfielder',30,'Republic of Korea',90000000
  UNION ALL SELECT 'Kim Jae-hwan','outfielder',36,'Republic of Korea',1500000000
  UNION ALL SELECT 'Kim Tae-geun','outfielder',22,'Republic of Korea',34000000
  UNION ALL SELECT 'Yang Chan-yeol','outfielder',21,'Republic of Korea',40000000
  UNION ALL SELECT 'Jeon Da-min','outfielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jung Soo-bin','outfielder',34,'Republic of Korea',600000000
  UNION ALL SELECT 'Cho Soo-haeng','outfielder',30,'Republic of Korea',95000000
  UNION ALL SELECT 'Hong Sung-ho','outfielder',27,'Republic of Korea',33000000
  UNION ALL SELECT 'Henry Ramos','outfielder',30,'Commonwealth of Puerto Rico',857000000
  UNION ALL SELECT 'Ryu Hyeon-jun', 'catcher', 19, 'Republic of Korea', 30000000 
  UNION ALL SELECT 'Chang Kyu-bin', 'catcher', 23, 'Republic of Korea', 31000000 
  UNION ALL SELECT 'Jared Young', 'infielder', 29, 'Canada', 434000000
  UNION ALL SELECT 'Park Min-jun', 'catcher', 22, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Kwon Hwi', 'pitcher', 24, 'Republic of Korea', 37000000
  UNION ALL SELECT 'Kim Do-Yun', 'pitcher', 22, 'Republic of Korea', 31000000
  UNION ALL SELECT 'Jordan Balazovic', 'pitcher', 26, 'Canada', 364000000
  UNION ALL SELECT 'Park Ji-ho', 'pitcher', 21, 'Republic of Korea', 30000000  
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;



/* ===== kt wiz ===== */
SET @team_name := 'kt wiz';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kang Geon' AS player_name, 'pitcher' AS position, 20 AS age, 'Republic of Korea' AS nationality, 35000000 AS salary
  UNION ALL SELECT 'Go Young-pyo','pitcher',33,'Republic of Korea',2000000000
  UNION ALL SELECT 'Kim Geon-ung','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Min','pitcher',25,'Republic of Korea',50000000
  UNION ALL SELECT 'Kim Min-seong (05.08.28)','pitcher',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Min-soo (92.07.24)','pitcher',32,'Republic of Korea',160000000
  UNION ALL SELECT 'Kim Young-hyun','pitcher',22,'Republic of Korea',41000000
  UNION ALL SELECT 'Kim Jung-woon','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Moon Yong-ik','pitcher',29,'Republic of Korea',63000000
  UNION ALL SELECT 'Park Se-jin','pitcher',27,'Republic of Korea',35000000
  UNION ALL SELECT 'Park Si-young','pitcher',31,'Republic of Korea',90000000
  UNION ALL SELECT 'Park Byung-ho (Before Trade)','infielder',38,'Republic of Korea',700000000
  UNION ALL SELECT 'Park Young-hyun','pitcher',22,'Republic of Korea',160000000
  UNION ALL SELECT 'Seong Jae-heon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'So Hyeong-jun','pitcher',23,'Republic of Korea',220000000
  UNION ALL SELECT 'Son Dong-hyun','pitcher',21,'Republic of Korea',120000000
  UNION ALL SELECT 'Shin Byeong-ryul','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Eom Sang-baek','pitcher',28,'Republic of Korea',250000000
  UNION ALL SELECT 'Woo Kyu-min','pitcher',39,'Republic of Korea',220000000
  UNION ALL SELECT 'Won Sang-hyeon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Yuk Cheong-myeong','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Yoon Gang-chan','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Sang-dong','pitcher',25,'Republic of Korea',60000000
  UNION ALL SELECT 'Lee Seon-woo (00.09.19)','pitcher',24,'Republic of Korea',40000000
  UNION ALL SELECT 'Lee Jeong-hyeon','pitcher',25,'Republic of Korea',35000000
  UNION ALL SELECT 'Lee Chae-ho','pitcher',20,'Republic of Korea',53000000
  UNION ALL SELECT 'Lee Tae-gyu','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeon Yong-joo','pitcher',24,'Republic of Korea',32000000
  UNION ALL SELECT 'Jo I-hyeon','pitcher',20,'Republic of Korea',60000000
  UNION ALL SELECT 'Ju Kwon','pitcher',20,'Republic of Korea',200000000
  UNION ALL SELECT 'Ha Jun-ho','pitcher',20,'Republic of Korea',45000000
  UNION ALL SELECT 'Wes Benjamin','pitcher',32,'United States of America',1857000000
  UNION ALL SELECT 'William Cuevas','pitcher',34,'Bolivarian Republic of Venezuela',1857000000
  UNION ALL SELECT 'Kang Hyun-woo','catcher',23,'Republic of Korea',50000000
  UNION ALL SELECT 'Kim Min-seok (05.07.22)','catcher',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Jun-tae','catcher',28,'Republic of Korea',100000000
  UNION ALL SELECT 'Jang Seong-woo','catcher',34,'Republic of Korea',500000000
  UNION ALL SELECT 'Jo Dae-hyeon','catcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Kang Min-seong','infielder',20,'Republic of Korea',36000000
  UNION ALL SELECT 'Kang Baek-ho','infielder',26,'Republic of Korea',290000000
  UNION ALL SELECT 'Kim Sang-soo (90.03.23)','infielder',34,'Republic of Korea',300000000
  UNION ALL SELECT 'Park Kyung-soo','infielder',38,'Republic of Korea',200000000
  UNION ALL SELECT 'Park Min-seok','infielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Shin Bon-gi','infielder',33,'Republic of Korea',130000000
  UNION ALL SELECT 'Oh Jae-il (After Trade)','infielder',38,'Republic of Korea',500000000
  UNION ALL SELECT 'Oh Yoon-seok','infielder',33,'Republic of Korea',140000000
  UNION ALL SELECT 'Lee Ho-yeon','infielder',29,'Republic of Korea',85000000
  UNION ALL SELECT 'Jang Jun-won','infielder',26,'Republic of Korea',53000000
  UNION ALL SELECT 'Cheon Seong-ho','infielder',27,'Republic of Korea',45000000
  UNION ALL SELECT 'Hwang Jae-gyun','infielder',37,'Republic of Korea',1000000000
  UNION ALL SELECT 'Kim Geon-hyung','outfielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Min-hyeok (95.11.21)','outfielder',37,'Republic of Korea',240000000
  UNION ALL SELECT 'Kim Byeong-jun','outfielder',24,'Republic of Korea',31000000
  UNION ALL SELECT 'Moon Sang-cheol','outfielder',26,'Republic of Korea',110000000
  UNION ALL SELECT 'Bae Jeong-dae','outfielder',30,'Republic of Korea',320000000
  UNION ALL SELECT 'An Chi-young','outfielder',32,'Republic of Korea',50000000
  UNION ALL SELECT 'Jeong Jun-young','outfielder',20,'Republic of Korea',42000000
  UNION ALL SELECT 'Jo Yong-ho','outfielder',35,'Republic of Korea',150000000
  UNION ALL SELECT 'Hong Hyeon-bin','outfielder',34,'Republic of Korea',45000000
  UNION ALL SELECT 'Mel Rojas Jr.','outfielder',30,'Dominican Republic',857000000
  UNION ALL SELECT 'Kwon Dong-jin', 'infielder', 26, 'Republic of Korea', 46000000
  UNION ALL SELECT 'Song Min-seop', 'outfielder', 33, 'Republic of Korea', 60000000
  UNION ALL SELECT 'Sim Woo-jun', 'infielder', 29, 'Republic of Korea', 500000000
  UNION ALL SELECT 'Ahn Hyun-min', 'outfielder', 21, 'Republic of Korea', 33000000
  UNION ALL SELECT 'Yun Jun-hyeok', 'infielder', 23, 'Republic of Korea', 35000000
  UNION ALL SELECT 'Han Cha-hyeon', 'pitcher', 26, 'Republic of Korea', 35000000   
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== SSG Landers ===== */
SET @team_name := 'SSG Landers';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Go Hyo-jun' AS player_name, 'pitcher' AS position, 41 AS age, 'Republic of Korea' AS nationality, 153000000 AS salary
  UNION ALL SELECT 'Kim Kwang-hyun','pitcher',36,'Republic of Korea',1000000000
  UNION ALL SELECT 'Kim Ju-on','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'No Kyung-eun','pitcher',40,'Republic of Korea',270000000
  UNION ALL SELECT 'Moon Seung-won','pitcher',34,'Republic of Korea',800000000
  UNION ALL SELECT 'Park Ki-ho','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Park Min-ho','pitcher',26,'Republic of Korea',60000000
  UNION ALL SELECT 'Park Si-hoo','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Park Jong-hoon','pitcher',33,'Republic of Korea',1100000000
  UNION ALL SELECT 'Baek Seung-geon','pitcher',20,'Republic of Korea',46000000
  UNION ALL SELECT 'Seo Sang-jun','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Seo Jin-yong','pitcher',32,'Republic of Korea',450000000
  UNION ALL SELECT 'Song Young-jin','pitcher',20,'Republic of Korea',45000000
  UNION ALL SELECT 'Shin Heon-min','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Oh Won-seok','pitcher',23,'Republic of Korea',140000000
  UNION ALL SELECT 'Lee Geon-wook','pitcher',29,'Republic of Korea',61000000
  UNION ALL SELECT 'Lee Ki-soon','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Ro-un','pitcher',20,'Republic of Korea',74000000
  UNION ALL SELECT 'Jeong Dong-yoon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Seong-gon','pitcher',30,'Republic of Korea',31000000
  UNION ALL SELECT 'Jo Byeong-hyeon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Min-jun','pitcher',20,'Republic of Korea',144000000
  UNION ALL SELECT 'Choi Hyun-seok','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Han Du-sol','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Robert Dugger','pitcher',29,'United States of America',1070000000
  UNION ALL SELECT 'Roenis Elias','pitcher',36,'Republic of Cuba',1070000000
  UNION ALL SELECT 'Kim Min-sik','catcher',35,'Republic of Korea',150000000
  UNION ALL SELECT 'Park Dae-on','catcher',24,'Republic of Korea',40000000
  UNION ALL SELECT 'Shin Beom-soo','catcher',25,'Republic of Korea',50000000
  UNION ALL SELECT 'Lee Ji-young','catcher',37,'Republic of Korea',200000000
  UNION ALL SELECT 'Jeon Kyung-won','catcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Jo Hyeong-woo','catcher',24,'Republic of Korea',63000000
  UNION ALL SELECT 'Hyeon Won-hoe','catcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Go Myeong-jun','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Min-jun','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Seong-min','infielder',30,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Seong-hyeon','infielder',37,'Republic of Korea',200000000
  UNION ALL SELECT 'Kim Chan-hyeong','infielder',28,'Republic of Korea',50000000
  UNION ALL SELECT 'Park Seong-han','infielder',26,'Republic of Korea',300000000
  UNION ALL SELECT 'Park Ji-hwan','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'An Sang-hyeon','infielder',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Jeon Ui-san','infielder',20,'Republic of Korea',80000000
  UNION ALL SELECT 'Choi Jeong','infielder',25,'Republic of Korea',1000000000
  UNION ALL SELECT 'Choi Kyung-mo','infielder',37,'Republic of Korea',37000000
  UNION ALL SELECT 'Choi Jun-woo','infielder',25,'Republic of Korea',45000000
  UNION ALL SELECT 'Kang Jin-seong','outfielder',31,'Republic of Korea',85000000
  UNION ALL SELECT 'Kim Jeong-min','outfielder',32,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Chang-pyeong','outfielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Ryu Hyo-seung','outfielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Oh Tae-gon','outfielder',33,'Republic of Korea',250000000
  UNION ALL SELECT 'Lee Seung-min (05.01.06)','outfielder',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Jeong-beom','outfielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Chae Hyeon-woo','outfielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Choi Sang-min','outfielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Choi Ji-hoon','outfielder',27,'Republic of Korea',250000000
  UNION ALL SELECT 'Choo Shin-soo','outfielder',42,'Republic of Korea',30000000
  UNION ALL SELECT 'Ha Jae-hoon','outfielder',34,'Republic of Korea',100000000
  UNION ALL SELECT 'Han Yu-seom','outfielder',35,'Republic of Korea',900000000
  UNION ALL SELECT 'Guillermo Heredia','outfielder',33,'Republic of Cuba', 1857000000
  UNION ALL SELECT 'Jeong Jun-jae', 'infielder', 21, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Jung Hyun-seung', 'outfielder', 23, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Choi Min-chang', 'outfielder', 29, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Drew Anderson', 'pitcher', 30, 'United States of America', 1675000000
  UNION ALL SELECT 'Keisho Shirakawa', 'pitcher', 23, 'Japan', 34300000
  UNION ALL SELECT 'Kim Taek-hyeong', 'pitcher', 28, 'Republic of Korea', 205000000
  UNION ALL SELECT 'Jang Ji-hoon', 'pitcher', 26, 'Republic of Korea', 130000000  
  UNION ALL SELECT 'Park Sung-bin', 'pitcher', 21, 'Republic of Korea', 30000000
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== Lotte Giants ===== */
SET @team_name := 'Lotte Giants';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Koo Seung-min' AS player_name, 'pitcher' AS position, 34 AS age, 'Republic of Korea' AS nationality, 450000000 AS salary
  UNION ALL SELECT 'Kim Kang-hyun','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Kim Do-gyu','pitcher',20,'Republic of Korea',80000000
  UNION ALL SELECT 'Kim Sang-soo (88.01.02)','pitcher',36,'Republic of Korea',160000000
  UNION ALL SELECT 'Kim Won-joong','pitcher',30,'Republic of Korea',500000000
  UNION ALL SELECT 'Kim Jin-wook','pitcher',22,'Republic of Korea',60000000
  UNION ALL SELECT 'Na Gyun-an','pitcher',25,'Republic of Korea',170000000
  UNION ALL SELECT 'Park Se-woong','pitcher',29,'Republic of Korea',1350000000
  UNION ALL SELECT 'Park Jin','pitcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Park Jin-hyung','pitcher',29,'Republic of Korea',86000000
  UNION ALL SELECT 'Song Jae-young','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Shin Jeong-rak','pitcher',41,'Republic of Korea',80000000
  UNION ALL SELECT 'Sim Jae-min','pitcher',30,'Republic of Korea',94000000
  UNION ALL SELECT 'Lee In-bok','pitcher',31,'Republic of Korea',100000000
  UNION ALL SELECT 'Lee Jin-ha','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Lee Tae-yeon','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lim Jun-seop','pitcher',32,'Republic of Korea',40000000
  UNION ALL SELECT 'Jeon Mi-reu','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Seong-jong','pitcher',20,'Republic of Korea',39000000
  UNION ALL SELECT 'Jeong Woo-jun','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Jeong Hyun-soo','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jin Seung-hyun','pitcher',20,'Republic of Korea',43000000
  UNION ALL SELECT 'Jin Hae-soo','pitcher',37,'Republic of Korea',150000000
  UNION ALL SELECT 'Choi Seol-woo','pitcher',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Choi Yi-jun','pitcher',20,'Republic of Korea',38000000
  UNION ALL SELECT 'Choi Jun-yong','pitcher',26,'Republic of Korea',163000000
  UNION ALL SELECT 'Han Hyun-hee','pitcher',32,'Republic of Korea',300000000
  UNION ALL SELECT 'Hong Min-ki','pitcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Charlie Barnes','pitcher',34,'United States of America',1714000000
  UNION ALL SELECT 'Aaron Wilkerson','pitcher',20,'United States of America',1070000000
  UNION ALL SELECT 'Kang Tae-yul','catcher',20,'Republic of Korea',36000000
  UNION ALL SELECT 'Seo Dong-wook','catcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Son Seong-bin','catcher',22,'Republic of Korea',50000000
  UNION ALL SELECT 'Yoo Kang-nam','catcher',24,'Republic of Korea',1000000000
  UNION ALL SELECT 'Jeong Bo-geun','catcher',30,'Republic of Korea',75000000
  UNION ALL SELECT 'Ji Si-wan','catcher',29,'Republic of Korea',58000000
  UNION ALL SELECT 'Kang Seong-woo','infielder',30,'Republic of Korea',30000000
  UNION ALL SELECT 'Go Seung-min','infielder',24,'Republic of Korea',80000000
  UNION ALL SELECT 'Kim Min-seong (88.12.17)','infielder',36,'Republic of Korea',200000000
  UNION ALL SELECT 'Na Seung-yeop','infielder',22,'Republic of Korea',40000000
  UNION ALL SELECT 'Noh Jin-hyuk','infielder',22,'Republic of Korea',600000000
  UNION ALL SELECT 'Park Seung-wook','infielder',34,'Republic of Korea',135000000
  UNION ALL SELECT 'Shin Yoon-hoo','infielder',20,'Republic of Korea',52000000
  UNION ALL SELECT 'Son Ho-young (After Trade)','infielder',30,'Republic of Korea',45000000
  UNION ALL SELECT 'Oh Sun-jin','infielder',36,'Republic of Korea',100000000
  UNION ALL SELECT 'Lee Joo-chan','infielder',33,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Hak-joo','infielder',32,'Republic of Korea',92000000
  UNION ALL SELECT 'Lee Ho-joon','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Dae-seon','infielder',37,'Republic of Korea',32000000
  UNION ALL SELECT 'Jeong Hoon','infielder',30,'Republic of Korea',300000000
  UNION ALL SELECT 'Choi Hang','infielder',29,'Republic of Korea',31000000
  UNION ALL SELECT 'Han Dong-hee','infielder',25,'Republic of Korea',162000000
  UNION ALL SELECT 'Kim Dong-hyeok (00.09.15)','outfielder',24,'Republic of Korea',31000000
  UNION ALL SELECT 'Kim Min-seok (04.05.09)','outfielder',20,'Republic of Korea',85000000
  UNION ALL SELECT 'Yoon Dong-hee','outfielder',21,'Republic of Korea',90000000
  UNION ALL SELECT 'Lee Seon-woo (05.02.22)','outfielder',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee Jeong-hoon','outfielder',20,'Republic of Korea',60000000
  UNION ALL SELECT 'Jang Du-seong','outfielder',37,'Republic of Korea',40000000
  UNION ALL SELECT 'Jeon Jun-woo','outfielder',38,'Republic of Korea',1300000000
  UNION ALL SELECT 'Hwang Seong-bin','outfielder',24,'Republic of Korea',76000000
  UNION ALL SELECT 'Victor Reyes','outfielder',30,'Bolivarian Republic of Venezuela',1000000000
  UNION ALL SELECT 'Kang Seung-gu','catcher',21,'Republic of Korea',30000000
  UNION ALL SELECT 'Baek Du-san','catcher',23,'Republic of Korea',30000000
  UNION ALL SELECT 'Lee In-han','outfielder',26,'Republic of Korea',31000000
  UNION ALL SELECT 'Choo Jae-hyun','outfielder',27,'Republic of Korea', 60000000
  UNION ALL SELECT 'Hyun Do-hun', 'pitcher', 31,'Republic of Korea',33000000
  UNION ALL SELECT 'Lee Min-seok', 'pitcher', 21, 'Republic of Korea', 40000000
  UNION ALL SELECT 'Yoon Seong-bin', 'pitcher', 25, 'Republic of Korea', 31000000
  UNION ALL SELECT 'Park Joon-woo', 'pitcher', 19, 'Republic of Korea', 30000000  
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== Hanwha Eagles ===== */
SET @team_name := 'Hanwha Eagles';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kang Jae-min' AS player_name, 'pitcher' AS position, 28 AS age, 'Republic of Korea' AS nationality, 145000000 AS salary
  UNION ALL SELECT 'Kim Gyu-yeon','pitcher',22,'Republic of Korea',41000000
  UNION ALL SELECT 'Kim Gi-jung','pitcher',23,'Republic of Korea',44000000
  UNION ALL SELECT 'Kim Min-woo','pitcher',29,'Republic of Korea',167000000
  UNION ALL SELECT 'Kim Beom-soo','pitcher',30,'Republic of Korea',193000000
  UNION ALL SELECT 'Kim Seo-hyun','pitcher',21,'Republic of Korea',33000000
  UNION ALL SELECT 'Nam Ji-min','pitcher',24,'Republic of Korea',40000000
  UNION ALL SELECT 'Ryu Hyun-jin','pitcher',37,'Republic of Korea',2500000000
  UNION ALL SELECT 'Moon Dong-ju','pitcher',22,'Republic of Korea',100000000
  UNION ALL SELECT 'Park Sang-won','pitcher',31,'Republic of Korea',195000000
  UNION ALL SELECT 'Bae Min-seo','pitcher',20,'Republic of Korea',51000000
  UNION ALL SELECT 'Seong Ji-hoon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Yoon Dae-gyeong','pitcher',33,'Republic of Korea',110000000
  UNION ALL SELECT 'Lee Min-woo','pitcher',31,'Republic of Korea',56000000
  UNION ALL SELECT 'Lee Sang-gyu','pitcher',20,'Republic of Korea',44000000
  UNION ALL SELECT 'Lee Chung-ho','pitcher',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Lee Tae-yang','pitcher',34,'Republic of Korea',500000000
  UNION ALL SELECT 'Jang Min-jae','pitcher',34,'Republic of Korea',150000000
  UNION ALL SELECT 'Jang Si-hwan','pitcher',37,'Republic of Korea',200000000
  UNION ALL SELECT 'Jang Ji-soo','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Jeong Woo-ram','pitcher',39,'Republic of Korea',100000000
  UNION ALL SELECT 'Jeong I-hwang','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jo Dong-wook','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Ju Hyeon-sang','pitcher',32,'Republic of Korea',110000000
  UNION ALL SELECT 'Han Seung-joo','pitcher',27,'Republic of Korea',45000000
  UNION ALL SELECT 'Han Seung-hyeok','pitcher',31,'Republic of Korea',49000000
  UNION ALL SELECT 'Hwang Jun-seo','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Ricardo Sánchez','pitcher',26,'Bolivarian Republic of Venezuela',857000000
  UNION ALL SELECT 'Felix Peña','pitcher',27,'Dominican Republic',1214000000
  UNION ALL SELECT 'Park Sang-eon','catcher',27,'Republic of Korea',42000000
  UNION ALL SELECT 'Lee Jae-yong','catcher',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Jae-won (88.02.24)','catcher',36,'Republic of Korea',50000000
  UNION ALL SELECT 'Jang Gyu-hyeon','catcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Choi Jae-hoon','catcher',36,'Republic of Korea',600000000
  UNION ALL SELECT 'Heo Gwan-hoe','catcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Geon','infielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Kim In-hwan','infielder',28,'Republic of Korea',69000000
  UNION ALL SELECT 'Kim Tae-yeon','infielder',27,'Republic of Korea',78000000
  UNION ALL SELECT 'Noh Si-hwan','infielder',24,'Republic of Korea',350000000
  UNION ALL SELECT 'Moon Hyeon-bin','infielder',21,'Republic of Korea',80000000
  UNION ALL SELECT 'An Chi-hong','infielder',34,'Republic of Korea',500000000
  UNION ALL SELECT 'Lee Do-yoon','infielder',34,'Republic of Korea',75000000
  UNION ALL SELECT 'Lee Min-jun','infielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Jeong An-seok','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Eun-won','infielder',24,'Republic of Korea',178000000
  UNION ALL SELECT 'Jo Han-min','infielder',24,'Republic of Korea',38000000
  UNION ALL SELECT 'Ha Ju-seok','infielder',26,'Republic of Korea',70000000
  UNION ALL SELECT 'Hwang Yeong-muk','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kwon Gwang-min','outfielder',30,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Kang-min','outfielder',42,'Republic of Korea',110000000
  UNION ALL SELECT 'Yu Ro-gyeol','outfielder',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Lee Myeong-gi','outfielder',39,'Republic of Korea',50000000
  UNION ALL SELECT 'Lee Sang-hyeok','outfielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Lee Won-seok (99.03.31)','outfielder',25,'Republic of Korea',36000000
  UNION ALL SELECT 'Lee Jin-yeong','outfielder',27,'Republic of Korea',70000000
  UNION ALL SELECT 'Im Jong-chan','outfielder',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Jang Jin-hyeok','outfielder',31,'Republic of Korea',58000000
  UNION ALL SELECT 'Choi In-ho','outfielder',33,'Republic of Korea',48000000
  UNION ALL SELECT 'Chae Eun-seong','outfielder',34,'Republic of Korea',1000000000
  UNION ALL SELECT 'Yonathan Perlaza','outfielder',34,'Bolivarian Republic of Venezuela',1143000000
  UNION ALL SELECT 'Han Kyeong-bin','infielder', 26, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Jaime Barría', 'pitcher', 28, 'Republic of Panama', 699000000
  UNION ALL SELECT 'Kim Do-bin', 'pitcher', 23, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Kim Seung-il', 'pitcher', 23, 'Republic of Korea', 30000000  
  UNION ALL SELECT 'Ryan Weiss', 'pitcher', 28, 'United States of America', 524000000  
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;


/* ===== NC Dinos ===== */
SET @team_name := 'NC Dinos';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kim Min-gyun' AS player_name, 'pitcher' AS position, 20 AS age, 'Republic of Korea' AS nationality, 30000000 AS salary
  UNION ALL SELECT 'Kim Si-hoon','pitcher',24,'Republic of Korea',110000000
  UNION ALL SELECT 'Kim Whee-jip (After Trade)','pitcher',22,'Republic of Korea',110000000  
  UNION ALL SELECT 'Kim Young-gyu','pitcher',25,'Republic of Korea',225000000
  UNION ALL SELECT 'Kim Jae-yeol','pitcher',20,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Jin-ho','pitcher',20,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Tae-hyun','pitcher',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Kim Hwi-geon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Ryu Jin-uk','pitcher',20,'Republic of Korea',165000000
  UNION ALL SELECT 'Park Ju-hyeon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Bae Jae-hwan','pitcher',20,'Republic of Korea',63000000
  UNION ALL SELECT 'Seo Ui-tae','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'So Yi-hyun','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Song Myeong-gi','pitcher',24,'Republic of Korea',135000000
  UNION ALL SELECT 'Shin Min-hyeok','pitcher',25,'Republic of Korea',180000000
  UNION ALL SELECT 'Shin Young-woo','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Sim Chang-min','pitcher',33,'Republic of Korea',85000000
  UNION ALL SELECT 'Lee Yong-jun','pitcher',20,'Republic of Korea',67000000
  UNION ALL SELECT 'Lee Yong-chan','pitcher',35,'Republic of Korea',400000000
  UNION ALL SELECT 'Lee Jae-hak','pitcher',34,'Republic of Korea',200000000
  UNION ALL SELECT 'Lee Jun-ho','pitcher',20,'Republic of Korea',45000000
  UNION ALL SELECT 'Im Sang-hyeon','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Im Jeong-ho','pitcher',30,'Republic of Korea',135000000
  UNION ALL SELECT 'Im Hyeong-won','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeon Sa-min','pitcher',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Chae Won-hu','pitcher',20,'Republic of Korea',35000000
  UNION ALL SELECT 'Choi Seong-yeong','pitcher',25,'Republic of Korea',83000000
  UNION ALL SELECT 'Choi Woo-seok','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Han Jae-seung','pitcher',20,'Republic of Korea',34000000
  UNION ALL SELECT 'Hong Yu-won','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Daniel Castano','pitcher',20,'United States of America',928000000
  UNION ALL SELECT 'Kyle Hart','pitcher',20,'United States of America',1000000000
  UNION ALL SELECT 'Kim Hyeong-jun','catcher',29,'Republic of Korea',58000000
  UNION ALL SELECT 'Park Se-hyeok','catcher',31,'Republic of Korea',700000000
  UNION ALL SELECT 'Shin Yong-seok','catcher',24,'Republic of Korea',30000000
  UNION ALL SELECT 'An Jung-yeol','catcher',34,'Republic of Korea',71000000
  UNION ALL SELECT 'Kim Su-yun','infielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Kim Ju-won','infielder',30,'Republic of Korea',160000000
  UNION ALL SELECT 'Kim Han-byeol','infielder',20,'Republic of Korea',38000000
  UNION ALL SELECT 'Kim Hwi-jip','infielder',23,'Republic of Korea',110000000
  UNION ALL SELECT 'Do Tae-hoon','infielder',31,'Republic of Korea',80000000
  UNION ALL SELECT 'Park Min-woo','infielder',31,'Republic of Korea',1000000000
  UNION ALL SELECT 'Park Ju-chan','infielder',26,'Republic of Korea',31000000
  UNION ALL SELECT 'Seo Ho-cheol','infielder',31,'Republic of Korea',120000000
  UNION ALL SELECT 'Oh Yeong-su','infielder',20,'Republic of Korea',72000000
  UNION ALL SELECT 'Yoon Hyeong-jun','infielder',26,'Republic of Korea',65000000
  UNION ALL SELECT 'Jo Hyeon-jin','infielder',22,'Republic of Korea',31000000
  UNION ALL SELECT 'Choi Bo-seong','infielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Choi Jeong-won','infielder',24,'Republic of Korea',66000000
  UNION ALL SELECT 'Matt Davidson','infielder',33,'United States of America',1000000000
  UNION ALL SELECT 'Kwon Hee-dong','outfielder',34,'Republic of Korea',150000000
  UNION ALL SELECT 'Kim Seong-uk','outfielder',33,'Republic of Korea',95000000
  UNION ALL SELECT 'Park Geon-woo','outfielder',34,'Republic of Korea',800000000
  UNION ALL SELECT 'Park Si-won','outfielder',30,'Republic of Korea',30000000
  UNION ALL SELECT 'Park Yeong-bin','outfielder',27,'Republic of Korea',32000000
  UNION ALL SELECT 'Park Han-gyeol','outfielder',20,'Republic of Korea',31000000
  UNION ALL SELECT 'Son Ah-seop','outfielder',36,'Republic of Korea',500000000
  UNION ALL SELECT 'Song Seung-hwan','outfielder',20,'Republic of Korea',35000000
  UNION ALL SELECT 'Cheon Jae-hwan','outfielder',30,'Republic of Korea',50000000
  UNION ALL SELECT 'Han Seok-hyeon','outfielder',30,'Republic of Korea',39000000
  UNION ALL SELECT 'Kim Beom-jun','outfielder', 24, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Kim Se-hun','infielder', 19, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Han Jae-hwan', 'infielder', 23, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Jun Ru-geon', 'pitcher', 24, 'Republic of Korea', 31000000
  UNION ALL SELECT 'Son Ju-hwan', 'pitcher', 22, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Mok Ji-hoon', 'pitcher', 20, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Eric Jokisch', 'pitcher', 35, 'United States of America', 145600000    
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;



/* ===== Kiwoom Heroes ===== */
SET @team_name := 'Kiwoom Heroes';

INSERT INTO players (player_name, position, age, nationality, team_id, team_name, salary)
SELECT
  s.player_name, s.position, s.age, s.nationality,
  t.team_id, @team_name, s.salary
FROM (
  SELECT 'Kim Dong-gyu' AS player_name, 'pitcher' AS position, 20 AS age, 'Republic of Korea' AS nationality, 31000000 AS salary
  UNION ALL SELECT 'Kim Dong-hyeok (01.12.27)','pitcher',23,'Republic of Korea',60000000
  UNION ALL SELECT 'Kim Whee-jip (Before Trade)','pitcher',22,'Republic of Korea',110000000  
  UNION ALL SELECT 'Kim Seon-gi','pitcher',33,'Republic of Korea',70000000
  UNION ALL SELECT 'Kim Yeon-ju','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim In-beom','pitcher',24,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Yun-ha','pitcher',19,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Jae-woong','pitcher',26,'Republic of Korea',190000000
  UNION ALL SELECT 'Moon Seong-hyun','pitcher',32,'Republic of Korea',75000000
  UNION ALL SELECT 'Park Seung-joo','pitcher',30,'Republic of Korea',45000000
  UNION ALL SELECT 'Park Yun-seong','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Son Hyeon-gi','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Oh Sang-won','pitcher',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Oh Seok-joo','pitcher',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Yoon Seok-won','pitcher',20,'Republic of Korea',43000000
  UNION ALL SELECT 'Lee Myeong-jong','pitcher',20,'Republic of Korea',60000000
  UNION ALL SELECT 'Lee Jong-min','pitcher',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Won Jong-hyun','pitcher',37,'Republic of Korea',500000000
  UNION ALL SELECT 'Jang Jae-young','pitcher',22,'Republic of Korea',40000000
  UNION ALL SELECT 'Jeon Jun-pyo','pitcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Jeong Chan-heon','pitcher',34,'Republic of Korea',200000000
  UNION ALL SELECT 'Jo Sang-woo','pitcher',30,'Republic of Korea',340000000
  UNION ALL SELECT 'Jo Yeong-geon','pitcher',24,'Republic of Korea',40000000
  UNION ALL SELECT 'Joo Seung-woo','pitcher',25,'Republic of Korea',32000000
  UNION ALL SELECT 'Yoon Jung-hyun','pitcher',31,'Republic of Korea',65000000  
  UNION ALL SELECT 'Ha Yeong-min','pitcher',25,'Republic of Korea',80000000
  UNION ALL SELECT 'Ariel Jurado','pitcher',28,'Republic of Panama',1715000000
  UNION ALL SELECT 'Emmanuel De Jesus','pitcher',27,'Bolivarian Republic of Venezuela',857000000
  UNION ALL SELECT 'Kim Dong-heon','catcher',23,'Republic of Korea',40000000
  UNION ALL SELECT 'Kim Si-ang','catcher',20,'Republic of Korea',36000000
  UNION ALL SELECT 'Kim Ji-seong','catcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Jae-hyeon','catcher',31,'Republic of Korea',55000000
  UNION ALL SELECT 'Park Seong-bin','catcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Park Jun-hyeong','catcher',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Go Yeong-woo','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Kim Geon-hee','infielder',20,'Republic of Korea',32000000
  UNION ALL SELECT 'Kim Byeong-hwi','infielder',20,'Republic of Korea',33000000
  UNION ALL SELECT 'Kim Su-hwan','infielder',20,'Republic of Korea',47000000
  UNION ALL SELECT 'Kim Ung-bin','infielder',27,'Republic of Korea',50000000
  UNION ALL SELECT 'Kim Joo-hyeong','infielder',20,'Republic of Korea',45000000
  UNION ALL SELECT 'Kim Tae-jin','infielder',28,'Republic of Korea',110000000
  UNION ALL SELECT 'Kim Hye-seong','infielder',25,'Republic of Korea',650000000
  UNION ALL SELECT 'Song Seong-mun','infielder',28,'Republic of Korea',130000000
  UNION ALL SELECT 'Song Ji-hoo','infielder',20,'Republic of Korea',30000000
  UNION ALL SELECT 'Shin Jun-woo','infielder',20,'Republic of Korea',42000000
  UNION ALL SELECT 'Lee Won-seok (86.10.21)','infielder',38,'Republic of Korea',400000000
  UNION ALL SELECT 'Lee Jae-sang','infielder',38,'Republic of Korea',30000000
  UNION ALL SELECT 'Im Ji-yeol','infielder',29,'Republic of Korea',72000000
  UNION ALL SELECT 'Choi Joo-hwan','infielder',25,'Republic of Korea',650000000
  UNION ALL SELECT 'Park Su-jong','outfielder',36,'Republic of Korea',40000000
  UNION ALL SELECT 'Park Chan-hyeok','outfielder',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Byeon Sang-gwon','outfielder',37,'Republic of Korea',48000000
  UNION ALL SELECT 'Ye Jin-won','outfielder',20,'Republic of Korea',40000000
  UNION ALL SELECT 'Lee Yong-gyu','outfielder',39,'Republic of Korea',200000000
  UNION ALL SELECT 'Lee Joo-hyeong','outfielder',39,'Republic of Korea',66000000
  UNION ALL SELECT 'Lee Hyeong-jong','outfielder',28,'Republic of Korea',680000000
  UNION ALL SELECT 'Im Byeong-uk','outfielder',35,'Republic of Korea',70000000
  UNION ALL SELECT 'Joo Seong-won','outfielder',30,'Republic of Korea',35000000
  UNION ALL SELECT 'Ronnie Dawson','outfielder',29,'United States of America',786000000
  UNION ALL SELECT 'Kim Dong-wook','pitcher', 27, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Park Bum-jun','pitcher', 20, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Park Joo-hong', 'outfielder', 23, 'Republic of Korea', 37000000
  UNION ALL SELECT 'Shim Hwi-yun', 'infielder', 19, 'Republic of Korea', 30000000
  UNION ALL SELECT 'Yang Ji-yul', 'pitcher', 26, 'Republic of Korea', 35000000
  UNION ALL SELECT 'Won Seong-jun', 'outfielder', 24, 'Republic of Korea', 40000000
  UNION ALL SELECT 'Lee Seung-won','infielder', 20, 'Republic of Korea', 32000000 
) AS s
CROSS JOIN (
  SELECT team_id FROM teams WHERE team_name = @team_name
) AS t;



