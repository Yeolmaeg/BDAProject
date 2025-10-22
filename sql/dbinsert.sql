USE team04;

START TRANSACTION;


-- 아래는 테스트용으로 임시값입니다!
-- 1) TEAMS
INSERT INTO teams (team_name, city, country, founded_year, winnings) VALUES
  ('Doosan Bears', 'Seoul', 'Korea', 1982, 6),
  ('LG Twins', 'Seoul', 'Korea', 1982, 3),
  ('New York Yankees', 'New York', 'USA', 1901, 27),
  ('Los Angeles Dodgers', 'Los Angeles', 'USA', 1883, 7);

-- 2) STADIUMS
INSERT INTO stadiums (stadium_name, location, built_year, roof_type) VALUES
  ('Jamsil Baseball Stadium', 'Seoul, Korea', 1982, 'OPEN'),
  ('Yankee Stadium', 'New York, USA', 2009, 'OPEN'),
  ('Dodger Stadium', 'Los Angeles, USA', 1962, 'OPEN');

-- 3) PLAYERS
INSERT INTO players (player_name, position, age, nationality, team_id, salary, debuted_year) VALUES
  ('Kim Minsoo', 'P', 28, 'Korea',
    (SELECT team_id FROM teams WHERE team_name='Doosan Bears'), 550000.00, 2019),
  ('Lee Jaeho', '1B', 30, 'Korea',
    (SELECT team_id FROM teams WHERE team_name='LG Twins'), 620000.00, 2017),
  ('Aaron Judge', 'RF', 33, 'USA',
    (SELECT team_id FROM teams WHERE team_name='New York Yankees'), 40000000.00, 2016),
  ('Mookie Betts', 'RF', 33, 'USA',
    (SELECT team_id FROM teams WHERE team_name='Los Angeles Dodgers'), 30000000.00, 2014);

-- 4) MATCHES
INSERT INTO matches
  (match_date, stadium_id, home_team_id, away_team_id, score_home, score_away,
   temp, humidity, wind_speed, rainfall)
VALUES
  ('2025-04-10 18:30:00',
   (SELECT stadium_id FROM stadiums WHERE stadium_name='Jamsil Baseball Stadium'),
   (SELECT team_id FROM teams WHERE team_name='LG Twins'),
   (SELECT team_id FROM teams WHERE team_name='Doosan Bears'),
   5, 3,  18.5, 45.0, 2.1, 0.0),
  ('2025-04-15 19:05:00',
   (SELECT stadium_id FROM stadiums WHERE stadium_name='Yankee Stadium'),
   (SELECT team_id FROM teams WHERE team_name='New York Yankees'),
   (SELECT team_id FROM teams WHERE team_name='Los Angeles Dodgers'),
   4, 6,  12.3, 55.0, 4.5, 0.0);

-- 5) TEAM_MATCH_PERFORMANCE (한 경기당 팀 2행: HOME/AWAY)
--   LG(홈) vs Doosan(원정)
INSERT INTO team_match_performance
(match_id, team_id, score, team_rbi, team_homeruns, team_errors, team_injury_rate, home_or_away)
VALUES
  (
    (SELECT match_id FROM matches
     WHERE match_date='2025-04-10 18:30:00'
       AND home_team_id=(SELECT team_id FROM teams WHERE team_name='LG Twins')),
    (SELECT team_id FROM teams WHERE team_name='LG Twins'),
    5, 4, 1, 0, 0.00, 'HOME'
  ),
  (
    (SELECT match_id FROM matches
     WHERE match_date='2025-04-10 18:30:00'
       AND away_team_id=(SELECT team_id FROM teams WHERE team_name='Doosan Bears')),
    (SELECT team_id FROM teams WHERE team_name='Doosan Bears'),
    3, 3, 0, 1, 0.00, 'AWAY'
  );

--   Yankees(홈) vs Dodgers(원정)
INSERT INTO team_match_performance
(match_id, team_id, score, team_rbi, team_homeruns, team_errors, team_injury_rate, home_or_away)
VALUES
  (
    (SELECT match_id FROM matches
     WHERE match_date='2025-04-15 19:05:00'
       AND home_team_id=(SELECT team_id FROM teams WHERE team_name='New York Yankees')),
    (SELECT team_id FROM teams WHERE team_name='New York Yankees'),
    4, 4, 1, 1, 0.00, 'HOME'
  ),
  (
    (SELECT match_id FROM matches
     WHERE match_date='2025-04-15 19:05:00'
       AND away_team_id=(SELECT team_id FROM teams WHERE team_name='Los Angeles Dodgers')),
    (SELECT team_id FROM teams WHERE team_name='Los Angeles Dodgers'),
    6, 5, 2, 0, 0.00, 'AWAY'
  );

-- 6) PITCHING_STATS
INSERT INTO pitching_stats
(match_id, player_id, innings_pitched, era, strikeouts, pitch_count, win_lost)
VALUES
  (
    (SELECT match_id FROM matches WHERE match_date='2025-04-10 18:30:00'),
    (SELECT player_id FROM players WHERE player_name='Kim Minsoo'),
    6.2, 3.15, 7, 98, 'ND'
  ),
  (
    (SELECT match_id FROM matches WHERE match_date='2025-04-15 19:05:00'),
    (SELECT player_id FROM players WHERE player_name='Mookie Betts'),
    1.0, 0.00, 1, 14, 'W'
  );

-- 7) BATTING_STATS
INSERT INTO batting_stats
(match_id, player_id, batting_number, hits, homeruns, rbi, batting_avg, on_base_percentage)
VALUES
  (
    (SELECT match_id FROM matches WHERE match_date='2025-04-10 18:30:00'),
    (SELECT player_id FROM players WHERE player_name='Lee Jaeho'),
    4, 2, 1, 2, 0.500, 0.600
  ),
  (
    (SELECT match_id FROM matches WHERE match_date='2025-04-15 19:05:00'),
    (SELECT player_id FROM players WHERE player_name='Aaron Judge'),
    5, 3, 2, 4, 0.600, 0.667
  );

COMMIT;
