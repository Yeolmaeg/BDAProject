SET NAMES utf8mb4;
SET time_zone = '+09:00';

CREATE DATABASE IF NOT EXISTS team04
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE team04;

-- stadiums
CREATE TABLE IF NOT EXISTS stadiums (
  stadium_id    INT AUTO_INCREMENT PRIMARY KEY,
  stadium_name  VARCHAR(120) NOT NULL,
  location      VARCHAR(200),
  built_year    SMALLINT,
  roof_type     ENUM('OPEN','RETRACTABLE','DOME','UNKNOWN') DEFAULT 'UNKNOWN',
  UNIQUE KEY uk_stadiums_name (stadium_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- teams (stadiums 참조)
CREATE TABLE IF NOT EXISTS teams (
  team_id       INT AUTO_INCREMENT PRIMARY KEY,
  team_name     VARCHAR(100) NOT NULL,
  city          VARCHAR(100),
  stadium_id    INT NOT NULL,
  founded_year  SMALLINT,
  winnings      INT DEFAULT 0,
  CONSTRAINT fk_teams_stadium
    FOREIGN KEY (stadium_id) REFERENCES stadiums(stadium_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_teams_name (team_name),
  KEY idx_teams_stadium (stadium_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- players (teams 참조)
CREATE TABLE IF NOT EXISTS players (
  player_id     INT AUTO_INCREMENT PRIMARY KEY,
  player_name   VARCHAR(120) NOT NULL,
  position      VARCHAR(50),
  age           SMALLINT,
  nationality   VARCHAR(80),
  team_id       INT NOT NULL,
  team_name     VARCHAR(100) NOT NULL,
  salary        BIGINT UNSIGNED,
  CONSTRAINT fk_players_team
    FOREIGN KEY (team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_players_team_name (team_id, player_name),
  KEY idx_players_team (team_id),
  KEY idx_players_name (player_name),
  KEY idx_players_team_position (team_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$

DROP TRIGGER IF EXISTS players_bi_set_team_name $$
CREATE TRIGGER players_bi_set_team_name
BEFORE INSERT ON players
FOR EACH ROW
BEGIN
  SET NEW.team_name = (SELECT team_name FROM teams WHERE team_id = NEW.team_id);
END$$

DROP TRIGGER IF EXISTS players_bu_set_team_name $$
CREATE TRIGGER players_bu_set_team_name
BEFORE UPDATE ON players
FOR EACH ROW
BEGIN
  IF NEW.team_id <> OLD.team_id THEN
    SET NEW.team_name = (SELECT team_name FROM teams WHERE team_id = NEW.team_id);
  END IF;
END$$

DELIMITER ;


-- matches (stadiums, teams 참조)
CREATE TABLE IF NOT EXISTS matches (
  match_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
  match_date    DATETIME NOT NULL,
  stadium_id    INT NOT NULL,
  home_team_id  INT NOT NULL,
  away_team_id  INT NOT NULL,
  score_home    SMALLINT DEFAULT 0,
  score_away    SMALLINT DEFAULT 0,
  temp          DECIMAL(4,1),   -- celsius
  humidity      DECIMAL(5,2),   -- %
  wind_speed    DECIMAL(5,2),   -- m/s
  rainfall      DECIMAL(6,2),   -- mm
  CONSTRAINT fk_matches_stadium
    FOREIGN KEY (stadium_id) REFERENCES stadiums(stadium_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_matches_home_team
    FOREIGN KEY (home_team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_matches_away_team
    FOREIGN KEY (away_team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_matches_teams_date (home_team_id, away_team_id, match_date),
  KEY idx_matches_date (match_date),
  KEY idx_matches_stadium (stadium_id),
  KEY idx_matches_teams (home_team_id, away_team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- team_match_performance (matches, teams 참조)
CREATE TABLE IF NOT EXISTS team_match_performance (
  team_match_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
  match_id          BIGINT NOT NULL,
  team_id           INT NOT NULL,
  score             SMALLINT DEFAULT 0,
  team_rbi          SMALLINT DEFAULT 0,
  team_homeruns     SMALLINT DEFAULT 0,
  team_errors       SMALLINT DEFAULT 0,
  team_injury_rate  DECIMAL(5,2),
  home_or_away      ENUM('HOME','AWAY') NOT NULL,
  CONSTRAINT fk_tmp_match
    FOREIGN KEY (match_id) REFERENCES matches(match_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_tmp_team
    FOREIGN KEY (team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_tmp_match_team (match_id, team_id),
  KEY idx_tmp_hoa (home_or_away)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- pitching_stats (matches, players 참조)
CREATE TABLE IF NOT EXISTS pitching_stats (
  pitch_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  match_id          BIGINT NOT NULL,
  player_id         INT NOT NULL,
  innings_pitched   DECIMAL(4,1),
  era               DECIMAL(4,2),
  strikeouts        SMALLINT,
  pitch_count       SMALLINT,
  win_lost          ENUM('W','L','ND') DEFAULT 'ND',
  CONSTRAINT fk_pitch_match
    FOREIGN KEY (match_id) REFERENCES matches(match_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pitch_player
    FOREIGN KEY (player_id) REFERENCES players(player_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_pitch_match_player (match_id, player_id),
  KEY idx_pitch_era (era),
  KEY idx_pitch_so (strikeouts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- batting_stats (matches, players 참조)
CREATE TABLE IF NOT EXISTS batting_stats (
  batting_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  match_id            BIGINT NOT NULL,
  player_id           INT NOT NULL,
  batting_number      SMALLINT,
  hits                SMALLINT,
  homeruns            SMALLINT,
  rbi                 SMALLINT,
  batting_avg         DECIMAL(5,3),
  on_base_percentage  DECIMAL(5,3),
  slugging_percentage DECIMAL(5,3),
  CONSTRAINT fk_bat_match
    FOREIGN KEY (match_id) REFERENCES matches(match_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_bat_player
    FOREIGN KEY (player_id) REFERENCES players(player_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_bat_match_player (match_id, player_id),
  KEY idx_bat_avg (batting_avg),
  KEY idx_bat_ops (on_base_percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- users
-- users
CREATE TABLE IF NOT EXISTS users (
  user_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_name     VARCHAR(100) NOT NULL,
  user_bdate    DATE,
  user_phone    VARCHAR(20),
  user_email    VARCHAR(255) NOT NULL,
  user_pass     VARCHAR(255) NOT NULL,
  favorite_team_id   INT NULL, -- 북마크용 추가
  favorite_player_id INT NULL, -- 북마크용 추가

  CONSTRAINT fk_users_fav_team
    FOREIGN KEY (favorite_team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_users_fav_player
    FOREIGN KEY (favorite_player_id) REFERENCES players(player_id)
    ON UPDATE CASCADE ON DELETE SET NULL,

  UNIQUE KEY uk_users_email (user_email),
  UNIQUE KEY uk_users_phone (user_phone),
  KEY idx_users_name (user_name),
  KEY idx_users_fav_team (favorite_team_id),
  KEY idx_users_fav_player (favorite_player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




-- player_weather_performance (고정 버킷)
CREATE TABLE IF NOT EXISTS player_weather_performance (
  player_weather_perf_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  player_id         INT NOT NULL,
  player_name       VARCHAR(120) NOT NULL,
  temp_bucket       VARCHAR(20)  NOT NULL,
  humidity_bucket   VARCHAR(20)  NOT NULL,
  wind_bucket       VARCHAR(20)  NOT NULL,
  rain_bucket       VARCHAR(20)  NOT NULL,
  bat_matches_count   INT NOT NULL DEFAULT 0,
  pitch_matches_count INT NOT NULL DEFAULT 0,
  avg_ba   DECIMAL(5,3) NULL,     -- 타자 타율
  avg_ops  DECIMAL(5,3) NULL,     -- 타자 OPS=OBP+SLG
  avg_era  DECIMAL(5,2) NULL,     -- 투수 era

  CONSTRAINT fk_pwp_player
    FOREIGN KEY (player_id) REFERENCES players(player_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uk_pwp_combo (player_id, temp_bucket, humidity_bucket, wind_bucket, rain_bucket),
  KEY idx_pwp_player (player_id),
  KEY idx_pwp_buckets (temp_bucket, humidity_bucket, wind_bucket, rain_bucket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 이름 동기화 (player_id로 player_name 채움)
DELIMITER $$

DROP TRIGGER IF EXISTS pwp_player_name_sync_ins $$
CREATE TRIGGER pwp_player_name_sync_ins
BEFORE INSERT ON player_weather_performance
FOR EACH ROW
BEGIN
  SET NEW.player_name = (SELECT player_name FROM players WHERE player_id = NEW.player_id);
END$$

DROP TRIGGER IF EXISTS pwp_player_name_sync_upd $$
CREATE TRIGGER pwp_player_name_sync_upd
BEFORE UPDATE ON player_weather_performance
FOR EACH ROW
BEGIN
  IF NEW.player_id <> OLD.player_id THEN
    SET NEW.player_name = (SELECT player_name FROM players WHERE player_id = NEW.player_id);
  END IF;
END$$

DELIMITER ;



-- 데이터 바뀌면 리프레시
DELIMITER $$

CREATE OR REPLACE PROCEDURE refresh_player_weather_performance()
BEGIN
  -- 초기화
  TRUNCATE TABLE player_weather_performance;

  -- batting_stats에서 타자 집계
  INSERT INTO player_weather_performance
    (player_id, temp_bucket, humidity_bucket, wind_bucket, rain_bucket,
     bat_matches_count, pitch_matches_count, avg_ba, avg_ops, avg_era)
  SELECT
    b.player_id,

    -- temp bucket
    CASE
      WHEN m.temp IS NULL THEN 'UNK'
      WHEN m.temp < 10   THEN '<10'
      WHEN m.temp < 15   THEN '10-15'
      WHEN m.temp < 20   THEN '15-20'
      WHEN m.temp < 25   THEN '20-25'
      WHEN m.temp < 30   THEN '25-30'
      ELSE '>=30'
    END AS temp_bucket,

    -- humidity bucket
    CASE
      WHEN m.humidity IS NULL THEN 'UNK'
      WHEN m.humidity < 50   THEN '<50'
      WHEN m.humidity < 60   THEN '50-60'
      WHEN m.humidity < 70   THEN '60-70'
      WHEN m.humidity < 80   THEN '70-80'
      ELSE '>=80'
    END AS humidity_bucket,

    -- wind bucket
    CASE
      WHEN m.wind_speed IS NULL THEN 'UNK'
      WHEN m.wind_speed < 1   THEN '<1'
      WHEN m.wind_speed < 2   THEN '1-2'
      WHEN m.wind_speed < 3   THEN '2-3'
      WHEN m.wind_speed < 5   THEN '3-5'
      ELSE '>=5'
    END AS wind_bucket,

    -- rain bucket
    CASE
      WHEN m.rainfall IS NULL THEN 'UNK'
      WHEN m.rainfall = 0    THEN '0'
      WHEN m.rainfall <= 1   THEN '0-1'
      WHEN m.rainfall <= 5   THEN '1-5'
      WHEN m.rainfall <= 10  THEN '5-10'
      ELSE '>10'
    END AS rain_bucket,
  
    -- 성적 비교를 위한 평균
    COUNT(*)                                    AS bat_matches_count,
    0                                           AS pitch_matches_count,
    ROUND(AVG(b.batting_avg), 3)                AS avg_ba,
    ROUND(AVG(b.on_base_percentage + b.slugging_percentage), 3)
                                                AS avg_ops,
    NULL                                        AS avg_era
  FROM batting_stats b
  JOIN matches m ON m.match_id = b.match_id
  GROUP BY b.player_id, temp_bucket, humidity_bucket, wind_bucket, rain_bucket
  ;

  -- pitching_stats에서 투수 집계 
  INSERT INTO player_weather_performance
    (player_id, temp_bucket, humidity_bucket, wind_bucket, rain_bucket,
     bat_matches_count, pitch_matches_count, avg_ba, avg_ops, avg_era)
  SELECT
    p.player_id,

    CASE
      WHEN m.temp IS NULL THEN 'UNK'
      WHEN m.temp < 10   THEN '<10'
      WHEN m.temp < 15   THEN '10-15'
      WHEN m.temp < 20   THEN '15-20'
      WHEN m.temp < 25   THEN '20-25'
      WHEN m.temp < 30   THEN '25-30'
      ELSE '>=30'
    END AS temp_bucket,

    CASE
      WHEN m.humidity IS NULL THEN 'UNK'
      WHEN m.humidity < 50   THEN '<50'
      WHEN m.humidity < 60   THEN '50-60'
      WHEN m.humidity < 70   THEN '60-70'
      WHEN m.humidity < 80   THEN '70-80'
      ELSE '>=80'
    END AS humidity_bucket,

    CASE
      WHEN m.wind_speed IS NULL THEN 'UNK'
      WHEN m.wind_speed < 1   THEN '<1'
      WHEN m.wind_speed < 2   THEN '1-2'
      WHEN m.wind_speed < 3   THEN '2-3'
      WHEN m.wind_speed < 5   THEN '3-5'
      ELSE '>=5'
    END AS wind_bucket,

    CASE
      WHEN m.rainfall IS NULL THEN 'UNK'
      WHEN m.rainfall = 0    THEN '0'
      WHEN m.rainfall <= 1   THEN '0-1'
      WHEN m.rainfall <= 5   THEN '1-5'
      WHEN m.rainfall <= 10  THEN '5-10'
      ELSE '>10'
    END AS rain_bucket,

    0                        AS bat_matches_count,
    COUNT(*)                 AS pitch_matches_count,
    NULL                     AS avg_ba,
    NULL                     AS avg_ops,
    ROUND(AVG(p.era), 2)     AS avg_era
  FROM pitching_stats p
  JOIN matches m ON m.match_id = p.match_id
  GROUP BY p.player_id, temp_bucket, humidity_bucket, wind_bucket, rain_bucket
  ON DUPLICATE KEY UPDATE

    -- 타자/투수 경기 카운트 분리
    pitch_matches_count = VALUES(pitch_matches_count),
    -- 평균은 투수쪽만 채우고, 타자 평균은 기존 값 유지
    avg_era = VALUES(avg_era),
    avg_ba  = player_weather_performance.avg_ba,
    avg_ops = player_weather_performance.avg_ops,
    bat_matches_count = player_weather_performance.bat_matches_count
  ;

  -- players name 동기화 한 번 더
  UPDATE player_weather_performance pwp
  JOIN players p ON p.player_id = pwp.player_id
  SET pwp.player_name = p.player_name;
END$$

DELIMITER ;











