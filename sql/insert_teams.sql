-- author: Sumin Son


USE team04;

INSERT INTO teams (team_name, city, stadium_id, founded_year, winnings)
WITH src AS (
  SELECT 'KIA Tigers'     AS team_name, 'Gwangju'  AS city, 'Gwangju-KIA Champions Field'      AS stadium_name, 1982 AS founded_year, 12 AS winnings
  UNION ALL SELECT 'Samsung Lions',  'Daegu',   'Daegu Samsung Lions Park',                      1982, 8
  UNION ALL SELECT 'LG Twins',       'Seoul',   'Seoul Sports Complex Baseball Stadium',         1982, 3
  UNION ALL SELECT 'Doosan Bears',   'Seoul',   'Seoul Sports Complex Baseball Stadium',         1982, 6
  UNION ALL SELECT 'kt wiz',         'Suwon',   'Suwon kt wiz Park',                             2013, 1
  UNION ALL SELECT 'SSG Landers',    'Incheon', 'Incheon SSG Landers Field',                     2000, 5
  UNION ALL SELECT 'Lotte Giants',   'Busan',   'Sajik Baseball Stadium',                        1982, 2
  UNION ALL SELECT 'Hanwha Eagles',  'Daejeon', 'Daejeon Hanwha Life Ballpark',                  1986, 1
  UNION ALL SELECT 'NC Dinos',       'Changwon','Changwon NC Park',                              2011, 1
  UNION ALL SELECT 'Kiwoom Heroes',  'Seoul',   'Gocheok Sky Dome',                              2008, 0
)
SELECT s.team_name, s.city, st.stadium_id, s.founded_year, s.winnings
FROM src s
JOIN stadiums st ON st.stadium_name = s.stadium_name


