<!-- author: Jwa Yeonjoo -->
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
        <a href="index.php" class="logo">
            <img src="public/logo_team04.png" alt="TEAM04 Logo" class="header-logo-img">
        </a>

        <form id="search-form" action="search.php" method="GET" class="header-search-container">
            <input 
                type="search" 
                name="query" 
                id="header-search-input" 
                class="header-search-input" 
                placeholder="Search for player or team name"
            >
            </form>

        <div class="header-menu">
            <?php if (!$is_logged_in): ?>
                <a href="login.php" <?php if($current_page == 'login.php') echo 'class="active"'; ?>>login</a>
                <a href="signup.php">SIGN UP</a>
            <?php else: ?>
                <a href="logout.php">log out</a>
                <a href="bug_report.php">bug report</a>
            <?php endif; ?>
            
        </div>
    </header>

    <nav class="nav-bar">
        <a href="teams.php" <?php if($current_page == 'teams.php') echo 'class="active"'; ?>>teams</a>
        <a href="players.php" <?php if($current_page == 'players.php') echo 'class="active"'; ?>>players</a>
        <a href="matches.php" <?php if($current_page == 'matches.php') echo 'class="active"'; ?>>matches</a>
        <a href="player_rank.php" <?php if($current_page == 'player_rank.php') echo 'class="active"'; ?>>player ranking by weather</a>
        <a href="team_rank.php" <?php if($current_page == 'team_rank.php') echo 'class="active"'; ?>>team ranking by weather</a>
    </nav>

    <main class="main-content">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('header-search-input');
            const searchForm = document.getElementById('search-form');

            // 폼 제출 이벤트 리스너 (불필요한 공백 제거)
            searchForm.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                if (query === "") {
                    e.preventDefault(); // 빈 검색어는 제출 방지
                    alert("Please enter a search term.");
                } else {
                    // 검색어의 앞뒤 공백을 제거하여 깔끔한 쿼리를 전송합니다.
                    searchInput.value = query;
                }
            });
        });
    </script>