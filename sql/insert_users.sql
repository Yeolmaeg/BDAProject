-- author: Sumin Son

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
  -- 비번은 pass1234, qwer1234, admin123, pw0000, test1234
  SELECT 'Kim Min-ji'    AS user_name, '2007-03-15' AS user_bdate, '010-1234-5678' AS user_phone,
         'gildong@example.com' AS user_email,
         '$2y$10$FzFAKyasmt69Eoifg6gG/OjrMhfuWEvMUOc0IvEISpGhrlL.ofWi.' AS user_pass,
         'KIA Tigers'    AS fav_team_name, 'Na Sung-beom'   AS fav_player_name
  UNION ALL
  SELECT 'Lee Yeon-jung', '2000-11-02', '010-2345-6789',
         'younghee@example.com',
         '$2y$10$R9d5.fBsbT3wF7q0ysqix.yWNtnAfn8xx6WYh88GAoB7LX9Yuj9Ma',  
         'Samsung Lions', 'Yang Hyeon-jong'
  UNION ALL
  SELECT 'Jin Sun-in', '1997-07-21', '010-3456-7890',
         'chulsoo@example.com',
         '$2y$10$1oG6hUJnSPbAimupKx/3AulUfblEY0KIixwjkfIDfZUhAGjpHFMJm',  
         'LG Twins', 'Choi Hyung-woo'
  UNION ALL
  SELECT 'Ha Sam', '1999-01-05', '010-4567-8901',
         'minsu@example.com',
         '$2y$10$vFl/fvmoGdEVhw57F7.fTuiK1hdZg6h6k76kvSzhc/WjaR6WpUXv2',  
         'Doosan Bears', 'Kim Do-young'
  UNION ALL
  SELECT 'Park Jae-yong', '2001-05-30', '010-5678-9012',
         'soomin@example.com',
         '$2y$10$rjHNYdBwbwBVN/AbnNyUVuOmeieEgOmv.tWHAZeKO06ndFSHhGxuG',  
         'kt wiz', 'Yang Hyeon-jong'
) AS s
LEFT JOIN teams t
  ON t.team_name = s.fav_team_name
LEFT JOIN players p
  ON p.player_name = s.fav_player_name;
