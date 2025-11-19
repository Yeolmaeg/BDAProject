<?php
// BDAProject/search.php
// author: Jwa Yeonjoo


// 1. DB 연결 설정

$DB_HOST = '127.0.0.1'; // 호스트 (localhost와 동일)
$DB_NAME = 'team04';   // 데이터베이스 이름
$DB_USER = 'root';     // 사용자 이름
$DB_PASS = '';         // 비밀번호 (XAMPP 기본 설정은 공백)
$DB_PORT = 3306;       // 포트 번호

$pdo = null; // PDO 객체 초기화

try {
    // 🚩 PDO 객체 생성 (데이터베이스 연결)
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    
    // 에러 모드를 예외 발생으로 설정하여 오류를 잡을 수 있게 함
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 결과 배열의 키를 컬럼 이름으로 설정 (FETCH_ASSOC)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // 연결 실패 시 오류 메시지 출력 후 스크립트 중단
    die("데이터베이스 연결 실패: " . $e->getMessage() . " (User: {$DB_USER})"); 
}

// header.php는 세션을 필요로 하므로, 세션이 시작되었는지 확인합니다.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// 2. URL에서 검색어 가져오기
$query = trim($_GET['query'] ?? '');

if (empty($query)) {
    header("Location: index.php"); // 검색어 없으면 메인으로
    exit;
}

// === 리다이렉션 우선 로직: 정확히 일치하는 팀/선수 검색 ===

// 1. 정확히 일치하는 팀 이름 검색 (team_id 필요)
$stmt_exact_team = $pdo->prepare("SELECT team_id FROM teams WHERE team_name = :query");
$stmt_exact_team->execute(['query' => $query]);
$exact_team_id = $stmt_exact_team->fetchColumn();

if ($exact_team_id) {
    // 🚩 팀 이름 검색 시 matches.php로 이동하며 team_id를 필터 파라미터로 넘김
    header("Location: matches.php?team_id={$exact_team_id}");
    exit;
}

// 2. 정확히 일치하는 선수 이름 검색 (player_name 필요)
// player_name을 가져오는 이유는 쿼리 파라미터에 한글 이름 그대로 전달하기 위함입니다.
$stmt_exact_player = $pdo->prepare("SELECT player_id, player_name FROM players WHERE player_name = :query");
$stmt_exact_player->execute(['query' => $query]);
$exact_player_data = $stmt_exact_player->fetch();

if ($exact_player_data) {
    // 🚩 선수 이름 검색 시 player_rank.php로 이동하며 player_name을 검색 파라미터로 넘김
    header("Location: player_rank.php?search_player=" . urlencode($exact_player_data['player_name']));
    exit;
}


// === 목록 검색 로직: 부분 일치하는 모든 결과 검색 (정확히 일치하는 결과가 없을 경우) ===

$search_param = "%{$query}%";

// A. 부분 일치하는 모든 팀 목록 검색
$stmt_teams = $pdo->prepare("SELECT team_id, team_name FROM teams WHERE team_name LIKE :query ORDER BY team_name ASC");
$stmt_teams->execute(['query' => $search_param]);
$team_results = $stmt_teams->fetchAll();

// B. 부분 일치하는 모든 선수 목록 검색
$stmt_players = $pdo->prepare("SELECT player_id, player_name FROM players WHERE player_name LIKE :query ORDER BY player_name ASC");
$stmt_players->execute(['query' => $search_param]);
$player_results = $stmt_players->fetchAll();

$has_results = !empty($team_results) || !empty($player_results);

// 3. 페이지 출력
$page_title = $has_results ? "검색 결과" : "검색 결과 없음";
require_once 'header.php';
?>

<div class="search-page-content" style="max-width: 800px; margin: 50px auto; padding: 20px;">
    <h2>'<?php echo htmlspecialchars($query); ?>' 검색 결과</h2>
    
    <?php if ($has_results): ?>
        
        <div class="search-section team-results">
            <h3>⚾ 팀 검색 결과 (<?php echo count($team_results); ?>건)</h3>
            <?php if (!empty($team_results)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($team_results as $team): ?>
                        <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px dashed #eee;">
                            <a href="matches.php?team_id=<?php echo $team['team_id']; ?>" style="text-decoration: none; color: #1e3a8a; font-weight: bold;">
                                <?php echo htmlspecialchars($team['team_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>일치하는 팀이 없습니다.</p>
            <?php endif; ?>
        </div>

        <hr style="margin: 30px 0;">

        <div class="search-section player-results">
            <h3>👤 선수 검색 결과 (<?php echo count($player_results); ?>건)</h3>
            <?php if (!empty($player_results)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($player_results as $player): ?>
                        <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px dashed #eee;">
                            <a href="player_rank.php?search_player=<?php echo urlencode($player['player_name']); ?>" style="text-decoration: none; color: #059669; font-weight: bold;">
                                <?php echo htmlspecialchars($player['player_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>일치하는 선수가 없습니다.</p>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        
        <div style="text-align: center; margin-top: 50px; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2 style="color: #c00;">일치하는 결과를 찾을 수 없습니다.</h2>
            <p>검색어를 다시 확인하거나, 팀 전체 목록에서 찾아보세요.</p>
        </div>
        
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>