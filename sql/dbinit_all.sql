USE team04;

source sql/dbcreate.sql;
source sql/insert_stadiums.sql;
source sql/insert_teams.sql;
source sql/insert_players.sql;
source sql/insertMatch.sql;
source sql/insertTeamMatchPerformance.sql;
source sql/insertPitchingStats.sql;
source sql/batting_stats.sql;
source sql/insert_users.sql;

CALL refresh_player_weather_performance();
