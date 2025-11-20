// author: Eunhyeon Kwon
<?php
// 세션 시작
session_start();

// 1. 페이지 제목 설정 
$page_title = "matches";

// 데이터베이스 연결 정보 설정 및 연결
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306; // 본인의 XAMPP MySQL 포트에 맞게 설정

$conn_matches = null;
$matches_matches = [];
$teams_matches = [];
$error_message_matches = null;

$conn_matches = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn_matches->connect_error) {
    $error_message_matches = "데이터베이스 연결 실패: " . $conn_matches->connect_error;
} else {
    $conn_matches->set_charset("utf8mb4");
}

// 필터 및 페이지네이션 파라미터 처리
$month_matches = isset($_GET['month']) ? intval($_GET['month']) : 0;
$team_id_matches = isset($_GET['team']) ? intval($_GET['team']) : 0;
$sort_order_matches = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';
$page_matches = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page_matches = 30;
$offset_matches = ($page_matches - 1) * $per_page_matches;

// 전체 팀 목록 조회 (필터용)
if ($conn_matches && !$error_message_matches) {
    $sql_teams = "SELECT team_id, team_name FROM teams ORDER BY team_name ASC";
    $result_teams = $conn_matches->query($sql_teams);
    if ($result_teams) {
        while ($row = $result_teams->fetch_assoc()) {
            $teams_matches[] = $row;
        }
        $result_teams->free();
    }
}

// 경기 데이터 조회 (필터 적용)
if ($conn_matches && !$error_message_matches) {
    // WHERE 조건 구성
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if ($month_matches > 0) {
        $where_clauses[] = "MONTH(m.match_date) = ?";
        $params[] = $month_matches;
        $types .= 'i';
    }
    
    if ($team_id_matches > 0) {
        $where_clauses[] = "(m.away_team_id = ? OR m.home_team_id = ?)";
        $params[] = $team_id_matches;
        $params[] = $team_id_matches;
        $types .= 'ii';
    }
    
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
    
    // 총 레코드 수 조회
    $sql_count = "
        SELECT COUNT(*) as total
        FROM matches m
        $where_sql
    ";
    
    if ($stmt_count = $conn_matches->prepare($sql_count)) {
        if (!empty($params)) {
            $stmt_count->bind_param($types, ...$params);
        }
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $total_records = $result_count->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $per_page_matches);
        $stmt_count->close();
    }
    
    // 경기 데이터 조회
    $sql_matches = "
        SELECT 
            m.match_id,
            m.match_date,
            m.away_team_id,
            m.home_team_id,
            m.score_away,
            m.score_home,
            s.stadium_name,
            t_away.team_name AS away_team_name,
            t_home.team_name AS home_team_name
        FROM 
            matches m
        JOIN stadiums s ON m.stadium_id = s.stadium_id
        JOIN teams t_away ON m.away_team_id = t_away.team_id
        JOIN teams t_home ON m.home_team_id = t_home.team_id
        $where_sql
        ORDER BY m.match_date $sort_order_matches
        LIMIT ? OFFSET ?
    ";
    
    if ($stmt_matches = $conn_matches->prepare($sql_matches)) {
        $all_params = array_merge($params, [$per_page_matches, $offset_matches]);
        $all_types = $types . 'ii';
        
        if (!empty($all_params)) {
            $stmt_matches->bind_param($all_types, ...$all_params);
        }
        
        $stmt_matches->execute();
        $result_matches = $stmt_matches->get_result();
        
        if ($result_matches) {
            while ($row = $result_matches->fetch_assoc()) {
                $matches_matches[] = $row;
            }
        }
        $stmt_matches->close();
    }
}

// Windowing: 직전 3경기 승률 계산 함수
function calculateTrend($conn, $current_match, $selected_team_id) {
    if (!$selected_team_id) {
        return ['trend' => '', 'icon' => ''];
    }
    
    $match_date = $current_match['match_date'];
    $match_id = $current_match['match_id'];
    
    // 현재 경기의 승패 판정
    $is_away = ($current_match['away_team_id'] == $selected_team_id);
    $current_win = $is_away 
        ? ($current_match['score_away'] > $current_match['score_home'])
        : ($current_match['score_home'] > $current_match['score_away']);
    
    // 직전 3경기 조회 (현재 경기 제외, 이전 경기만)
    $sql_prev = "
        SELECT 
            m.away_team_id,
            m.home_team_id,
            m.score_away,
            m.score_home
        FROM matches m
        WHERE (m.away_team_id = ? OR m.home_team_id = ?)
          AND m.match_date < ?
          AND m.match_id != ?
        ORDER BY m.match_date DESC
        LIMIT 3
    ";
    
    $stmt = $conn->prepare($sql_prev);
    $stmt->bind_param('iisi', $selected_team_id, $selected_team_id, $match_date, $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $prev_matches = [];
    while ($row = $result->fetch_assoc()) {
        $prev_matches[] = $row;
    }
    $stmt->close();
    
    // 직전 3경기가 없으면 추세 없음
    if (count($prev_matches) < 3) {
        return ['trend' => 'N/A', 'icon' => '-', 'color' => '#adb5bd'];
    }
    
    // 직전 3경기 승률 계산
    $prev_wins = 0;
    foreach ($prev_matches as $match) {
        $is_away_prev = ($match['away_team_id'] == $selected_team_id);
        $win = $is_away_prev
            ? ($match['score_away'] > $match['score_home'])
            : ($match['score_home'] > $match['score_away']);
        if ($win) $prev_wins++;
    }
    $prev_win_rate = ($prev_wins / 3) * 100;
    
    // 현재 경기 포함 최근 3경기 조회
    $sql_recent = "
        SELECT 
            m.away_team_id,
            m.home_team_id,
            m.score_away,
            m.score_home
        FROM matches m
        WHERE (m.away_team_id = ? OR m.home_team_id = ?)
          AND m.match_date <= ?
        ORDER BY m.match_date DESC
        LIMIT 3
    ";
    
    $stmt2 = $conn->prepare($sql_recent);
    $stmt2->bind_param('iis', $selected_team_id, $selected_team_id, $match_date);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    $recent_matches = [];
    while ($row = $result2->fetch_assoc()) {
        $recent_matches[] = $row;
    }
    $stmt2->close();
    
    // 최근 3경기 승률 계산
    $recent_wins = 0;
    foreach ($recent_matches as $match) {
        $is_away_recent = ($match['away_team_id'] == $selected_team_id);
        $win = $is_away_recent
            ? ($match['score_away'] > $match['score_home'])
            : ($match['score_home'] > $match['score_away']);
        if ($win) $recent_wins++;
    }
    $recent_win_rate = ($recent_wins / 3) * 100;
    
    // 추세 판정
    if ($recent_win_rate > $prev_win_rate) {
        return ['trend' => 'Up', 'icon' => '▲', 'color' => '#28a745'];
    } elseif ($recent_win_rate < $prev_win_rate) {
        return ['trend' => 'Down', 'icon' => '▼', 'color' => '#dc3545'];
    } else {
        return ['trend' => 'Stable', 'icon' => '➖', 'color' => '#6c757d'];
    }
}

// 각 경기에 대한 추세 계산
foreach ($matches_matches as &$match) {
    $trend_data = calculateTrend($conn_matches, $match, $team_id_matches);
    $match['trend'] = isset($trend_data['trend']) ? $trend_data['trend'] : '';
    $match['trend_icon'] = isset($trend_data['icon']) ? $trend_data['icon'] : '';
    $match['trend_color'] = isset($trend_data['color']) ? $trend_data['color'] : '#999';
}

if ($conn_matches) {
    $conn_matches->close();
}

// 2. 헤더 파일 포함
require_once 'header.php'; 
?>

<div class="card-box">
    <h1 class="page-title">2024 KBO League Match Records</h1>
    <p class="page-description">
        Match Results and Trend Analysis<br>
        (Based on the selected team's win rate in the last 3 matches)
    </p>

    <?php if ($error_message_matches): ?>
        <p style="color: red; padding: 10px;"><?php echo htmlspecialchars($error_message_matches); ?></p>
    <?php endif; ?>

    <div class="table-header-row">
        <h2 class="section-title">Match List</h2>
    </div>

    <!-- 필터 바 -->
    <form method="GET" action="matches.php" class="filter-bar">
        <div class="filter-dropdown">
            <select name="month" class="filter-toggle">
                <option value="0">📅 Select Month</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $month_matches == $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="filter-dropdown">
            <select name="team" class="filter-toggle">
                <option value="0">⚾ Select Team</option>
                <?php foreach ($teams_matches as $team): ?>
                    <option value="<?php echo $team['team_id']; ?>" <?php echo $team_id_matches == $team['team_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="filter-toggle" style="padding: 8px 16px;">Apply</button>
        
        <a href="matches.php" class="filter-toggle" style="text-decoration: none; text-align: center; padding: 8px 16px; display: inline-block;">
            Reset
        </a>
    </form>

    <div style="margin: 15px 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>Sort by:</strong>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'desc'])); ?>" 
               style="margin: 0 5px; <?php echo $sort_order_matches === 'DESC' ? 'font-weight: bold; color: #007bff;' : 'color: #6c757d;'; ?>">
               Latest
            </a>
            |
            <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'asc'])); ?>" 
               style="margin: 0 5px; <?php echo $sort_order_matches === 'ASC' ? 'font-weight: bold; color: #007bff;' : 'color: #6c757d;'; ?>">
               Oldest
            </a>
        </div>
        <div>
            <strong>Total <?php echo isset($total_records) ? $total_records : 0; ?> Matches</strong>
        </div>
    </div>

    <div class="player-rank-card">
        <table class="player-rank-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Stadium</th>
                    <th>Matchup</th>
                    <th>Score</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($matches_matches)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                        <?php if ($month_matches || $team_id_matches): ?>
                            No matches found for the selected criteria.
                        <?php else: ?>
                            No data available. Please check the database.
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($matches_matches as $match): ?>
                    <tr>
                        <td style="white-space: nowrap;">
                            <?php echo date('Y-m-d H:i', strtotime($match['match_date'])); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($match['stadium_name']); ?>
                        </td>
                        <td>
                            <a href="match_detail.php?match_id=<?php echo $match['match_id']; ?>" 
                               style="text-decoration: none; color: inherit; display: block;">
                                <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                                    <span style="font-weight: 500;">
                                        <?php echo htmlspecialchars($match['away_team_name']); ?>
                                    </span>
                                    <span style="color: #6c757d;">vs</span>
                                    <span style="font-weight: 500;">
                                        <?php echo htmlspecialchars($match['home_team_name']); ?>
                                    </span>
                                </div>
                            </a>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: center; align-items: center; gap: 8px;">
                                <span style="font-weight: bold; <?php echo $match['score_away'] > $match['score_home'] ? 'color: #007bff;' : ''; ?>">
                                    <?php echo $match['score_away']; ?>
                                </span>
                                <span>:</span>
                                <span style="font-weight: bold; <?php echo $match['score_home'] > $match['score_away'] ? 'color: #007bff;' : ''; ?>">
                                    <?php echo $match['score_home']; ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php if ($team_id_matches > 0): ?>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 1.5em; color: <?php echo $match['trend_color']; ?>;">
                                        <?php echo $match['trend_icon']; ?>
                                    </span>
                                    <span style="font-size: 0.9em; color: <?php echo $match['trend_color']; ?>; font-weight: 500;">
                                        <?php echo $match['trend']; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <span style="color: #adb5bd;">Select a team</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <div class="pagination" style="margin: 20px 0; text-align: center;">
        <?php
        $query_params = $_GET;
        
        // 맨 처음 페이지 (<<)
        if ($page_matches > 1):
            $query_params['page'] = 1;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; border-radius: 4px;">
                &lt;&lt;
            </a>
        <?php endif; ?>
        
        <?php
        // 이전 페이지 (<)
        if ($page_matches > 1):
            $query_params['page'] = $page_matches - 1;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; border-radius: 4px;">
                &lt;
            </a>
        <?php endif; ?>
        
        <?php
        // 페이지 번호 (5개씩)
        $start_page = max(1, $page_matches - 2);
        $end_page = min($total_pages, $start_page + 4);
        
        // 끝 페이지가 5개 미만일 때 시작 페이지 조정
        if ($end_page - $start_page < 4) {
            $start_page = max(1, $end_page - 4);
        }
        
        for ($i = $start_page; $i <= $end_page; $i++):
            $query_params['page'] = $i;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" 
               style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; border-radius: 4px; 
                      <?php echo $i === $page_matches ? 'background: #007bff; color: white; font-weight: bold;' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php
        // 다음 페이지 (>)
        if ($page_matches < $total_pages):
            $query_params['page'] = $page_matches + 1;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; border-radius: 4px;">
                &gt;
            </a>
        <?php endif; ?>
        
        <?php
        // 맨 마지막 페이지 (>>)
        if ($page_matches < $total_pages):
            $query_params['page'] = $total_pages;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; border-radius: 4px;">
                &gt;&gt;
            </a>
        <?php endif; ?>
        
        <div style="margin-top: 10px; color: #6c757d;">
            Page <?php echo $page_matches; ?> of <?php echo $total_pages; ?>
        </div>
    </div>
    <?php endif; ?>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>
