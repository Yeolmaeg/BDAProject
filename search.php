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


// 🚩 Helper function: 선수의 주된 역할을 판단합니다. (검색 목록에서 역할 표시용)
function getPlayerRole($pdo, $player_id) {
    // 1. 타격 기록 확인 (야수 우선)
    $stmt_batting = $pdo->prepare("SELECT 1 FROM batting_stats WHERE player_id = ? LIMIT 1");
    $stmt_batting->execute([$player_id]);
    if ($stmt_batting->fetchColumn()) {
        return 'Batter';
    }

    // 2. 투구 기록 확인
    $stmt_pitching = $pdo->prepare("SELECT 1 FROM pitching_stats WHERE player_id = ? LIMIT 1");
    $stmt_pitching->execute([$player_id]);
    if ($stmt_pitching->fetchColumn()) {
        return 'Pitcher';
    }

    // 통계가 없는 경우 기본값
    return 'Unknown'; 
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


// === 리다이렉션 우선 로직: 정확히 일치하는 팀 검색 ===

// 1. 정확히 일치하는 팀 이름 검색 (team_id 필요)
$stmt_exact_team = $pdo->prepare("SELECT team_id FROM teams WHERE team_name = :query");
$stmt_exact_team->execute(['query' => $query]);
$exact_team_id = $stmt_exact_team->fetchColumn();

if ($exact_team_id) {
    // 🚩 팀 이름 검색 시 matches.php로 이동하며 month=0과 team={team_id}를 필터 파라미터로 넘김
    header("Location: matches.php?month=0&team={$exact_team_id}");
    exit;
}

// 2. 정확히 일치하는 선수 이름 검색
//    -> 이제 즉시 리다이렉션 하지 않고, 아래 목록 검색에 포함시켜 페이지를 보여주도록 합니다.
//    (기존 리다이렉션 로직 제거)


// === 목록 검색 로직: 부분 일치하는 모든 결과 검색 ===

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
$page_title = $has_results ? "Search Results" : "No Matching Results";
require_once 'header.php';
?>

<div class="search-page-content" style="max-width: 800px; margin: 50px auto; padding: 20px;">
    <h2>'<?php echo htmlspecialchars($query); ?>' Search Results</h2>
    
    <?php if ($has_results): ?>
        
        <!-- 팀 검색 결과 섹션 -->
        <div class="search-section team-results">
            <h3>⚾ Team search results (<?php echo count($team_results); ?> items)</h3>
            <?php if (!empty($team_results)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($team_results as $team): ?>
                        <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px dashed #eee;">
                            <a href="matches.php?month=0&team=<?php echo $team['team_id']; ?>" style="text-decoration: none; color: #1e3a8a; font-weight: bold;">
                                <?php echo htmlspecialchars($team['team_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No matching teams found.</p>
            <?php endif; ?>
        </div>

        <hr style="margin: 30px 0;">

        <!-- 선수 검색 결과 섹션 -->
        <div class="search-section player-results">
            <h3>👤 Player search results (<?php echo count($player_results); ?> items)</h3>
            <?php if (!empty($player_results)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($player_results as $player): ?>
                        <?php
                            // 🚩 선수의 역할을 결정
                            $player_role = getPlayerRole($pdo, $player['player_id']);
                            // 🚩 search_players.php로 이동하도록 링크 수정
                            $player_url = "search_players.php?player_id=" . $player['player_id'];
                        ?>
                        <li style="margin-bottom: 10px; padding: 8px; border-bottom: 1px dashed #eee;">
                            <a href="<?php echo $player_url; ?>" style="text-decoration: none; color: #059669; font-weight: bold;">
                                <?php echo htmlspecialchars($player['player_name']); ?> (<?php echo $player_role; ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No matching players found.</p>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        
        <!-- 검색 결과 없음 -->
        <div style="text-align: center; margin-top: 50px; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2 style="color: #c00;">No matching results found.</h2>
            <p>Please check your search term or browse the full list of teams.</p>
        </div>
        
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>