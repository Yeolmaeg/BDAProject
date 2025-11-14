<?php
// BDAProject/header.php

// 실제 PHP에서는 세션 등을 통해 로그인 상태를 확인하여 메뉴를 변경합니다.
$is_logged_in = isset($_SESSION['user_id']); // 가상의 로그인 상태 체크

// 현재 페이지 경로를 기반으로 'active' 클래스를 설정하기 위한 로직
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? "KBO 통계"; ?></title>
    <link rel="stylesheet" href="public/style.css"> 
</head>
<body>

    <header class="header">
        <a href="/" class="logo">
            <img src="public/logo_team04.png" alt="TEAM04 Logo" class="header-logo-img">
        </a>
        <div class="header-menu">
            <?php if (!$is_logged_in): ?>
                <a href="login.php" <?php if($current_page == 'login.php') echo 'class="active"'; ?>>login</a>
                <a href="signup.php">SIGN UP</a>
            <?php else: ?>
                <a href="logout.php">log out</a>
            <?php endif; ?>
            <a href="bug_report.php">bug report</a>
        </div>
    </header>

    <nav class="nav-bar">
        <a href="teams.php" <?php if($current_page == 'teams.php') echo 'class="active"'; ?>>teams</a>
        <a href="players.php" <?php if($current_page == 'players.php') echo 'class="active"'; ?>>players</a>
        <a href="matches.php" <?php if($current_page == 'matches.php') echo 'class="active"'; ?>>matches</a>
        <a href="player_rank.php" <?php if($current_page == 'player_rank.php') echo 'class="active"'; ?>>rank(players)</a>
        <a href="team_rank.php" <?php if($current_page == 'team_rank.php') echo 'class="active"'; ?>>rank(teams)</a>
    </nav>

    <main class="main-content">