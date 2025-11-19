<?php
// BDAProject/index.php

// 1. 세션 시작 (로그인 상태를 사용하기 위해 필수)
session_start();

function getTeamLogoSrc($team_name) {
    $key = strtolower(trim($team_name));

    // 실제 teams 테이블의 team_name 과 최대한 맞춰서 작성
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
        return null; // 로고 없으면 null
    }

    $code = $map[$key];
    return "logos/{$code}.png";  // team04/logos 안에 있는 파일
}

// 2. 로그인 상태 확인 및 사용자 ID 가져오기
$is_logged_in = isset($_SESSION['user_id']); 
$user_id = $_SESSION['user_id'] ?? null; 

// === [DB 설정 및 연결] ===
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306; 

$pdo = null;
try {
    $pdo = new PDO("mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 실제 운영 환경에서는 오류 메시지를 사용자에게 직접 보여주지 않습니다.
    // 여기서는 개발 편의를 위해 표시합니다.
    die("DB 연결 실패: " . $e->getMessage()); 
}

// --- [DB 연동 로직 시작] ---
$favorite_team_id = null;
$team_name = null;
// 성능 지표 변수 초기화
$team_rbi = "N/A";
$team_homeruns = "N/A";
$team_errors = "N/A";


if ($is_logged_in && $user_id && $pdo) {
    
    // 1. users 테이블에서 favorite_team_id 및 팀 이름 조회
    $stmt_user = $pdo->prepare("
        SELECT 
            u.favorite_team_id, 
            t.team_name 
        FROM users u
        LEFT JOIN teams t ON u.favorite_team_id = t.team_id
        WHERE u.user_id = :user_id
    ");
    $stmt_user->execute(['user_id' => $user_id]);
    $user_data = $stmt_user->fetch();

    if ($user_data) {
        $favorite_team_id = $user_data['favorite_team_id'];
        $team_name = $user_data['team_name'];
    }

    if ($favorite_team_id && $team_name) {
        // === 좋아하는 팀이 설정되었을 때 필요한 DB 데이터 설정 ===
        $page_title = "응원팀 대시보드";
        
        // 2. 좋아하는 팀의 가장 최근 경기 정보 조회 (match_id 추가)
        $stmt_match = $pdo->prepare("
            SELECT 
                m.match_id, /* <<< match_id 추가 */
                m.match_date, 
                m.score_home, 
                m.score_away, 
                m.temp,
                m.home_team_id,
                m.away_team_id
            FROM matches m
            WHERE m.home_team_id = :fav_team_id OR m.away_team_id = :fav_team_id
            ORDER BY m.match_date DESC
            LIMIT 1
        ");
        $stmt_match->execute(['fav_team_id' => $favorite_team_id]);
        $match_data = $stmt_match->fetch();

        if ($match_data) {
            
            // 3. 상대팀 ID 및 이름 조회
            $opponent_id = ($match_data['home_team_id'] == $favorite_team_id) 
                         ? $match_data['away_team_id'] 
                         : $match_data['home_team_id'];
            
            $stmt_opponent = $pdo->prepare("SELECT team_name FROM teams WHERE team_id = :opponent_id");
            $stmt_opponent->execute(['opponent_id' => $opponent_id]);
            $opponent = $stmt_opponent->fetchColumn();

            // 4. 데이터 가공 및 변수 설정
            $is_favorite_home = ($match_data['home_team_id'] == $favorite_team_id);
            $favorite_score = $is_favorite_home ? $match_data['score_home'] : $match_data['score_away'];
            $opponent_score = $is_favorite_home ? $match_data['score_away'] : $match_data['score_home'];
            
            $match_date = date('Y. m. d', strtotime($match_data['match_date']));
            $is_win = ($favorite_score > $opponent_score);
            $score_win = "{$favorite_score}:{$opponent_score}";
            
            $weather_temp_raw = $match_data['temp'] ?? 0;
            $weather_temp = floor($weather_temp_raw) . "°C"; 
            
            // 5. team_match_performance에서 팀별 기록 조회 (새로운 로직)
            $match_id = $match_data['match_id']; 

            $stmt_performance = $pdo->prepare("
                SELECT 
                    team_rbi, 
                    team_homeruns, 
                    team_errors
                FROM team_match_performance
                WHERE match_id = :match_id AND team_id = :team_id
            ");
            $stmt_performance->execute([
                'match_id' => $match_id,
                'team_id' => $favorite_team_id
            ]);
            $performance_data = $stmt_performance->fetch();

            if ($performance_data) {
                $team_rbi = $performance_data['team_rbi'];
                $team_homeruns = $performance_data['team_homeruns'];
                $team_errors = $performance_data['team_errors'];
            }
            // --- 새로운 로직 끝 ---


        } else {
            // 경기가 없는 경우 (대시보드 내용을 빈 값으로 설정)
            $match_date = "최근 경기 없음";
            $score_win = "N/A";
            $opponent = "없음";
            $weather_temp = "-";
        }

    } else {
        // === 로그인 했지만, 좋아하는 팀이 설정되지 않았을 때 필요한 데이터 설정 ===
        $page_title = "응원팀 설정 필요";
    }
} else {
    // === 로그인 안 했을 때 필요한 데이터 설정 ===
    $page_title = "환영합니다! 로그인/회원가입";
}
// --- [DB 연동 로직 끝] ---

// 3. 헤더 파일 포함 (header.php가 위에서 설정된 변수들을 사용)
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
                $score_class = ($is_win) ? 'win' : 'lose'; // 승패 클래스
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
                            <h3>Today's Stadium Weather</h3>
                            <div class="weather-details">
                                <span><?php echo $weather_temp; ?></span>
                            </div>
                        </div>
                    </div> 
                </div>

            <?php else: // 좋아하는 팀이 없는 경우 ?>
                
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