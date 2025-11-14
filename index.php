<?php
// BDAProject/index.php

// 1. 세션 시작 (로그인 상태를 사용하기 위해 필수)
session_start();

// 2. 로그인 상태 확인 및 대시보드 데이터 설정
// header.php에서 $is_logged_in을 사용하므로, 여기서 세션 상태를 가져옵니다.
$is_logged_in = isset($_SESSION['user_id']); // header.php와 동일한 로직

if ($is_logged_in) {
    // === 로그인 했을 때 필요한 가상 데이터 설정 ===
    $page_title = "응원팀 대시보드";
    
    // 실제로는 DB에서 해당 사용자의 응원팀과 데이터를 가져와야 합니다.
    $team_name = "LG 트윈스"; 
    $match_date = "2025. 10. 25";
    $score_win = "5:3 (승)";
    $opponent = "KT 위즈";
    $winning_pitcher = "플럿코";
    $best_hitter = "김현수 (4타수 2안타 2타점)";
    $weather_temp = "23°C";
    $weather_note = "다음 경기 실황의 기상 조건이 경기장에 미치는 영향 분석 예정.";

} else {
    // === 로그인 안 했을 때 필요한 데이터 설정 ===
    $page_title = "환영합니다! 로그인/회원가입";
}

// 3. 헤더 파일 포함 (header.php가 위에서 설정된 변수들을 사용)
require_once 'header.php'; 

?>

        <?php if (!$is_logged_in): ?>
            
            <div class="card-box welcome-card">
                <h2>환영합니다!</h2>
                <p>회원가입하고 응원하는 팀의 최근 경기와 날씨 분석을 확인하세요</p>
                <div style="margin-top: 30px;">
                    <button class="signup-btn" onclick="location.href='signup.php'">회원가입</button>
                    <button class="login-btn" onclick="location.href='login.php'">로그인</button>
                </div>
            </div>

        <?php else: ?>
            
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

        <?php endif; ?>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>