USE team04;

INSERT INTO users (
  user_name,
  user_bdate,
  user_phone,
  user_email,
  user_pass,
  favorite_team_id,
  favorite_player_id
)
SELECT
  s.user_name,
  s.user_bdate,
  s.user_phone,
  s.user_email,
  s.user_pass,
  t.team_id,
  p.player_id
FROM (
  -- user_name, bdate, phone, email, pass, fav_team_name, fav_player_name
  SELECT 'Kim Min-ji'    AS user_name, '2007-03-15' AS user_bdate, '010-1234-5678' AS user_phone,
         'gildong@example.com' AS user_email, 'pass1234' AS user_pass,
         'KIA Tigers'    AS fav_team_name, 'Na Sung-beom'   AS fav_player_name
  UNION ALL
  SELECT 'Lee Yeon-jung', '2000-11-02', '010-2345-6789',
         'younghee@example.com', 'qwer1234',
         'Samsung Lions', 'Yang Hyeon-jong'
  UNION ALL
  SELECT 'Jin Sun-in', '1997-07-21', '010-3456-7890',
         'chulsoo@example.com', 'admin123',
         'LG Twins', 'Choi Hyung-woo'
  UNION ALL
  SELECT 'Ha Sam', '1999-01-05', '010-4567-8901',
         'minsu@example.com', 'pw0000',
         'Doosan Bears', 'Kim Do-young'
  UNION ALL
  SELECT 'Park Jae-yong', '2001-05-30', '010-5678-9012',
         'soomin@example.com', 'test1234',
         'kt wiz', 'Yang Hyeon-jong'
) AS s
LEFT JOIN teams t
  ON t.team_name = s.fav_team_name
LEFT JOIN players p
  ON p.player_name = s.fav_player_name
