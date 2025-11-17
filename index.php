<?php
// BDAProject/index.php

// 1. 세션 시작 (로그인 상태를 사용하기 위해 필수)
session_start();

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
        
        // 2. 좋아하는 팀의 가장 최근 경기 정보 조회
        $stmt_match = $pdo->prepare("
            SELECT 
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
            $score_win = "{$favorite_score}:{$opponent_score} (" . ($is_win ? '승' : '패') . ")";
            
            $weather_temp_raw = $match_data['temp'] ?? 0;
            $weather_temp = floor($weather_temp_raw) . "°C"; 
            
            // ************ 임시 데이터 유지 (선수 정보는 복잡한 DB 쿼리가 필요함) ************
            $winning_pitcher = "플럿코 (DB에서 가져와야 함)";
            $best_hitter = "김현수 (4타수 2안타 2타점) (DB에서 가져와야 함)";
            $weather_note = "다음 경기 실황의 기상 조건이 경기장에 미치는 영향 분석 예정."; 
            // *************************************************************************

        } else {
            // 경기가 없는 경우 (대시보드 내용을 빈 값으로 설정)
            $match_date = "최근 경기 없음";
            $score_win = "N/A";
            $opponent = "없음";
            $winning_pitcher = "-";
            $best_hitter = "-";
            $weather_temp = "-";
            $weather_note = "등록된 최근 경기 정보가 없습니다.";
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
                            </div>

        <?php else: ?>
            
            <?php if ($favorite_team_id): 
                $score_class = ($is_win) ? 'win' : 'lose'; // 승패 클래스
            ?>

            <div class="card-box dashboard-box">
                <h2><?php echo $team_name; ?> 대시보드</h2>

                                <div class="recent-match-summary">
                    <div class="match-info">
                        <p>최근 경기 요약</p>
                        <p class="match-date"><?php echo $match_date; ?></p>
                        
                        <div class="score-and-logo">
                            <img src="/assets/logos/<?php echo $team_name; ?>.png" alt="<?php echo $team_name; ?> 로고" class="team-logo">
                            <div class="match-score <?php echo $score_class; ?>">
                                <?php echo $score_win; ?>
                            </div>
                        </div>

                        <p>vs <?php echo $opponent; ?></p>
                    </div>
                    
                                    </div>
                
                                <div class="weather-info">
                    <h3>오늘의 경기장 날씨</h3>
                    <div class="weather-details">
                        <span></span>                         <span><?php echo $weather_temp; ?></span>
                    </div>
                                    </div>

            <?php else: // 좋아하는 팀이 없는 경우 ?>
                
                <div class="card-box welcome-card">
                                    </div>

            <?php endif; ?>
        <?php endif; ?>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>