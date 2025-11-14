<?php
// dashboard.php

// 실제 PHP에서는 세션 등을 통해 로그인 상태 및 응원팀을 확인합니다.
$is_logged_in = true;
$team_name = "LG 트윈스"; // 로그인한 사용자의 응원팀
$team_eng_name = "LG TWINS";

// 가상 데이터 (실제는 DB에서 가져옴)
$match_date = "2025. 10. 25";
$score_win = "5:3 (승)";
$opponent = "KT 위즈";
$winning_pitcher = "플럿코";
$best_hitter = "김현수 (4타수 2안타 2타점)";
$weather_temp = "23°C";
$weather_note = "다음 경기 실황의 기상 조건이 경기장에 미치는 영향 분석 예정.";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KBO 통계 - <?php echo $team_name; ?> 대시보드</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

    <header class="header">
        <a href="/" class="logo">team04</a>
        <div class="header-menu">
            <?php if ($is_logged_in): ?>
                <a href="/myaccount.php">my account</a>
                <a href="/logout.php">log out</a>
            <?php else: ?>
                <a href="/login.php">login</a>
                <a href="/signup.php">SIGN UP</a>
            <?php endif; ?>
            <a href="/bugreport.php">bug report</a>
        </div>
    </header>

    <nav class="nav-bar">
        <a href="/teams.php">teams</a>
        <a href="/players.php">players</a>
        <a href="/matches.php">matches</a>
        <a href="/rank_batter_pitcher.php">rank(타자/투수)</a>
        <a href="/rank_teams.php">rank(teams)</a>
    </nav>
    
    <main class="main-content">
        <div class="search-container">
            <input type="search" placeholder="Search">
        </div>

        <div class="card-box dashboard-box">
            <h2>✨ <?php echo $team_name; ?> 대시보드</h2>

            <div class="recent-match-summary">
                <div class="match-info">
                    <p>⚾ **최근 경기 요약**</p>
                    <p class="match-date"><?php echo $match_date; ?></p>
                    <div class="match-score">
                        <span style="font-size: 20px; margin-right: 5px;"></span>
                        <?php echo $score_win; ?>
                    </div>
                    <p>vs <?php echo $opponent; ?></p>
                </div>
                
                <div class="player-stats">
                    <p>승리 투수: **<?php echo $winning_pitcher; ?>**</p>
                    <p>최다수 타자: **<?php echo $best_hitter; ?>**</p>
                </div>
            </div>
            
            <div class="weather-info">
                <h3>☀️ 오늘의 경기장 날씨</h3>
                <div class="weather-details">
                    <span style="font-size: 24px; font-family: 'KBO_Bold', sans-serif;">
                        <?php echo $weather_temp; ?>
                    </span>
                    <span style="margin-left: 10px;">맑음</span>
                </div>
                <p style="margin-top: 10px; font-size: 13px; color: #777;">
                    <?php echo $weather_note; ?>
                </p>
            </div>
        </div>
    </main>

</body>
</html>