<?php
//author: Jwa Yeonjoo
// BDAProject/index.php

// 1. 세션 시작
session_start();

// 2. DB 연결 설정 불러오기 (config/config.php 사용)
require_once __DIR__ . '/config/config.php'; 

// 로고 가져오는 함수
function getTeamLogoSrc($team_name) {
    $key = strtolower(trim($team_name));
    $map = [
        'kia tigers'      => 'kia',
        'kt wiz'          => 'kt',
        'hanwha eagles'   => 'hanwha',
        'doosan bears'    => 'doosan',
        'samsung lions'   => 'samsung',
        'ssg landers'     => 'ssg',
        'kiwoom heroes'   => 'kiwoom',
        'nc dinos'        => 'nc',
        'lotte giants'    => 'lotte',
        'lg twins'        => 'lg',
    ];

    if (!isset($map[$key])) {
        return null; 
    }
    $code = $map[$key];
    return "logos/{$code}.png"; 
}

// 3. 로그인 상태 확인 및 사용자 ID 가져오기
$is_logged_in = isset($_SESSION['user_id']); 
$user_id = $_SESSION['user_id'] ?? null; 

// 변수 초기화
$favorite_team_id = null;
$team_name = null;
$team_rbi = "N/A";
$team_homeruns = "N/A";
$team_errors = "N/A";

// --- [DB 연동 로직 시작 (MySQLi 버전)] ---
if ($is_logged_in && $user_id && isset($conn)) {
    
    // 1. users 테이블에서 favorite_team_id 및 팀 이름 조회
    $sql_user = "SELECT u.favorite_team_id, t.team_name 
                 FROM users u
                 LEFT JOIN teams t ON u.favorite_team_id = t.team_id
                 WHERE u.user_id = ?";
    
    $stmt_user = $conn->prepare($sql_user);
    // user_id가 문자열이면 "s", 정수면 "i". 안전하게 "s"로 처리하거나 상황에 맞게 변경
    $stmt_user->bind_param("s", $user_id); 
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_data = $result_user->fetch_assoc();
    $stmt_user->close();

    if ($user_data) {
        $favorite_team_id = $user_data['favorite_team_id'];
        $team_name = $user_data['team_name'];
    }

    if ($favorite_team_id && $team_name) {
        $page_title = "응원팀 대시보드";
        
        // 2. 좋아하는 팀의 가장 최근 경기 정보 조회
        $sql_match = "SELECT m.match_id, m.match_date, m.score_home, m.score_away, m.temp, m.home_team_id, m.away_team_id
                      FROM matches m
                      WHERE m.home_team_id = ? OR m.away_team_id = ?
                      ORDER BY m.match_date DESC
                      LIMIT 1";
                      
        $stmt_match = $conn->prepare($sql_match);
        $stmt_match->bind_param("ii", $favorite_team_id, $favorite_team_id);
        $stmt_match->execute();
        $result_match = $stmt_match->get_result();
        $match_data = $result_match->fetch_assoc();
        $stmt_match->close();

        if ($match_data) {
            
            // 3. 상대팀 ID 및 이름 조회
            $opponent_id = ($match_data['home_team_id'] == $favorite_team_id) 
                         ? $match_data['away_team_id'] 
                         : $match_data['home_team_id'];
            
            $sql_opponent = "SELECT team_name FROM teams WHERE team_id = ?";
            $stmt_opponent = $conn->prepare($sql_opponent);
            $stmt_opponent->bind_param("i", $opponent_id);
            $stmt_opponent->execute();
            $result_opponent = $stmt_opponent->get_result();
            $opponent_row = $result_opponent->fetch_assoc();
            $opponent = $opponent_row['team_name'] ?? "Unknown";
            $stmt_opponent->close();

            // 4. 데이터 가공
            $is_favorite_home = ($match_data['home_team_id'] == $favorite_team_id);
            $favorite_score = $is_favorite_home ? $match_data['score_home'] : $match_data['score_away'];
            $opponent_score = $is_favorite_home ? $match_data['score_away'] : $match_data['score_home'];
            
            $match_date = date('Y. m. d', strtotime($match_data['match_date']));
            $is_win = ($favorite_score > $opponent_score);
            $score_win = "{$favorite_score}:{$opponent_score}";
            
            $weather_temp_raw = $match_data['temp'] ?? 0;
            $weather_temp = floor($weather_temp_raw) . "°C"; 
            
            // 5. team_match_performance에서 팀별 기록 조회
            $match_id = $match_data['match_id']; 
            
            $sql_perf = "SELECT team_rbi, team_homeruns, team_errors
                         FROM team_match_performance
                         WHERE match_id = ? AND team_id = ?";
                         
            $stmt_perf = $conn->prepare($sql_perf);
            $stmt_perf->bind_param("ii", $match_id, $favorite_team_id);
            $stmt_perf->execute();
            $result_perf = $stmt_perf->get_result();
            $performance_data = $result_perf->fetch_assoc();
            $stmt_perf->close();

            if ($performance_data) {
                $team_rbi = $performance_data['team_rbi'];
                $team_homeruns = $performance_data['team_homeruns'];
                $team_errors = $performance_data['team_errors'];
            }

        } else {
            // 경기가 없는 경우
            $match_date = "최근 경기 없음";
            $score_win = "N/A";
            $opponent = "없음";
            $weather_temp = "-";
        }

    } else {
        $page_title = "응원팀 설정 필요";
    }
} else {
    $page_title = "환영합니다! 로그인/회원가입";
}
// --- [DB 연동 로직 끝] ---

// 3. 헤더 파일 포함
require_once 'header.php'; 
?>

<?php if (!$is_logged_in): ?>
    <div class="card-box welcome-card">
        <h2>Welcome!</h2>
        <p>Sign up and check out your favorite team's latest matches and weather analysis.</p>
        <div class="login-actions">
            <button onclick="location.href='signup.php'" class="welcome-card signup-btn">Sign Up</button>
            <button onclick="location.href='login.php'" class="welcome-card login-btn">Log In</button>
        </div>
    </div>
<?php else: ?>
    <?php if ($favorite_team_id): 
        $score_class = ($is_win) ? 'win' : 'lose'; 
        $logo_src = getTeamLogoSrc($team_name);
        $default_logo = 'logos/default.png'; 
        $team_logo_src = $logo_src ?: $default_logo;
    ?>
        <div class="card-box dashboard-box">
            <div class="dashboard-header"> <img src="<?php echo $team_logo_src; ?>" alt="<?php echo $team_name; ?> Logo" class="team-logo-main-dashboard"> <h2><?php echo $team_name; ?> Dashboard</h2>
            </div>

            <div class="dashboard-content-wrapper"> 
                <div class="recent-match-summary">
                    <div class="match-info">
                        <h3>Recent Match Summary</h3>
                        <p class="match-date">Date: <?php echo $match_date; ?></p>
                        <p class="vs-opponent">vs <?php echo $opponent; ?></p>

                        <div class="score-and-opponent"> 
                            <div class="match-score <?php echo $score_class; ?>">
                                <?php echo $score_win; ?>
                            </div>
                            <p class="match-score <?php echo $score_class; ?>">(<?php echo ($is_win ? 'WIN' : 'LOSE'); ?>)</p>
                        </div>
                        
                        <div class="player-stats">
                            <p class="stats-label">Performance</p>
                            
                            <p>RBI: <?php echo $team_rbi; ?></p>
                            <p>Homeruns: <?php echo $team_homeruns; ?></p>
                            <p>Errors: <?php echo $team_errors; ?></p>
                            </div>
                    </div>
                </div>
                
                <div class="vertical-divider"></div> 
                
                <div class="weather-info">
                    <h3>Stadium Weather</h3>
                    <div class="weather-details">
                        <span><?php echo $weather_temp; ?></span>
                    </div>
                </div>
            </div> 
        </div>

    <?php else: ?>
        <div class="card-box welcome-card">
            <h2>Need to set up a cheering team</h2>
            <p>Please select your favorite team on the team page!</p> 
            <div class="button-container">
                <button onclick="location.href='teams.php'" class="signup-btn">Go to Team Page</button>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>