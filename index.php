<?php
// 실제 PHP에서는 세션 등을 통해 로그인 상태를 확인하고 리다이렉션합니다.
// 여기서는 로그아웃 상태를 가정합니다.

$is_logged_in = false; // 로그인 상태 여부
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KBO 통계 - 로그인</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

    <header class="header">
        <a href="/" class="logo">team04</a>
        <div class="header-menu">
            <?php if (!$is_logged_in): ?>
                <a href="/login.php" class="active">login</a>
                <a href="/signup.php">SIGN UP</a>
            <?php else: ?>
                <a href="/myaccount.php">my account</a>
                <a href="/logout.php">log out</a>
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

        <div class="card-box welcome-card">
            <h2>환영합니다!</h2>
            <p>회원가입하고 응원하는 팀의 최근 경기와 날씨 분석을 확인하세요</p>
            <div style="margin-top: 30px;">
                <button class="signup-btn" onclick="location.href='/signup.php'">회원가입</button>
                <button class="login-btn" onclick="location.href='/login.php'">로그인</button>
            </div>
        </div>
    </main>

</body>
</html>