USE team04;

SET FOREIGN_KEY_CHECKS = 0;

-- 1) teams
CREATE TABLE teams (
  team_id       INT AUTO_INCREMENT PRIMARY KEY,
  team_name     VARCHAR(100) NOT NULL,
  city          VARCHAR(100),
  stadium_id    INT NOT NULL,  
  founded_year  SMALLINT,
  winnings      INT DEFAULT 0,
  CONSTRAINT KEY fk_teams_stadium
    FOREIGN KEY (stadium_id) REFERENCES stadiums(stadium_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_teams_name (team_name),
  KEY idx_matches_stadium (stadium_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) players
CREATE TABLE players (
  player_id     INT AUTO_INCREMENT PRIMARY KEY,
  player_name   VARCHAR(120) NOT NULL,
  position      VARCHAR(50),
  age           SMALLINT,
  nationality   VARCHAR(80),
  team_id       INT NOT NULL,
  salary        DECIMAL(12,2),
  debuted_year  SMALLINT,
  CONSTRAINT fk_players_team
    FOREIGN KEY (team_id) REFERENCES teams(team_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  KEY idx_players_team (team_id),
  KEY idx_players_name (player_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) stadiums
CREATE TABLE stadiums (
  stadium_id    INT AUTO_INCREMENT PRIMARY KEY,
  stadium_name  VARCHAR(120) NOT NULL,
  location      VARCHAR(200),
  built_year    SMALLINT,
  roof_type     ENUM('OPEN','RETRACTABLE','DOME','UNKNOWN') DEFAULT 'UNKNOWN',
  UNIQUE KEY uk_stadiums_name (stadium_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) matches
CREATE TABLE matches (
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
  KEY idx_matches_date (match_date),
  KEY idx_matches_stadium (stadium_id),
  KEY idx_matches_teams (home_team_id, away_team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) team_match_performance
CREATE TABLE team_match_performance (
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

-- 6) pitching_stats
CREATE TABLE pitching_stats (
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

-- 7) batting_stats
CREATE TABLE batting_stats (
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


SET FOREIGN_KEY_CHECKS = 1;
