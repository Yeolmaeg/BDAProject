<?php
// author: Eunhyeon Kwon
// 세션 시작
session_start();

// 1. 페이지 제목 설정 
$page_title = "matches";

// config.php를 통한 데이터베이스 연결
require_once __DIR__ . '/config.php';

$matches_matches = [];
$teams_matches = [];
$error_message_matches = null;

// 연결 확인
if (!$conn) {
    $error_message_matches = "데이터베이스 연결 실패";
}

// 필터 및 페이지네이션 파라미터 처리
$month_matches = isset($_GET['month']) ? intval($_GET['month']) : 0;
$team_id_matches = isset($_GET['team']) ? intval($_GET['team']) : 0;
$sort_order_matches = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';
$page_matches = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page_matches = 30;
$offset_matches = ($page_matches - 1) * $per_page_matches;

// 전체 팀 목록 조회 (필터용)
if ($conn && !$error_message_matches) {
    $sql_teams = "SELECT team_id, team_name FROM teams ORDER BY team_name ASC";
    $result_teams = $conn->query($sql_teams);
    if ($result_teams) {
        while ($row = $result_teams->fetch_assoc()) {
            $teams_matches[] = $row;
        }
        $result_teams->free();
    }
}

// 경기 데이터 조회 (필터 적용)
if ($conn && !$error_message_matches) {
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
    
    if ($stmt_count = $conn->prepare($sql_count)) {
        if (!empty($params)) {
            $stmt_count->bind_param($types, ...$params);
        }
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $total_records = $result_count->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $per_page_matches);
        $stmt_count->close();
    }
    
    // Window Function을 사용한 경기 데이터 조회
    if ($team_id_matches > 0) {
        // 팀이 선택된 경우: Window Function으로 추세 계산
        $sql_matches = "
        WITH team_matches AS (
            -- 선택된 팀의 모든 경기 정보
            SELECT 
                m.match_id,
                m.match_date,
                m.away_team_id,
                m.home_team_id,
                m.score_away,
                m.score_home,
                m.stadium_id,
                -- 승패 판정
                CASE 
                    WHEN m.away_team_id = ? THEN 
                        CASE WHEN m.score_away > m.score_home THEN 1 ELSE 0 END
                    WHEN m.home_team_id = ? THEN 
                        CASE WHEN m.score_home > m.score_away THEN 1 ELSE 0 END
                END as is_win
            FROM matches m
            WHERE (m.away_team_id = ? OR m.home_team_id = ?)
        ),
        windowed_stats AS (
            -- Window Function으로 승률 계산
            SELECT 
                match_id,
                match_date,
                away_team_id,
                home_team_id,
                score_away,
                score_home,
                stadium_id,
                is_win,
                -- Recent Window: 현재 경기 포함 최근 3경기 승률
                AVG(is_win) OVER (
                    ORDER BY match_date, match_id
                    ROWS BETWEEN 2 PRECEDING AND CURRENT ROW
                ) * 100 as recent_win_rate,
                -- Past Window: 현재 경기 제외 직전 3경기 승률
                AVG(is_win) OVER (
                    ORDER BY match_date, match_id
                    ROWS BETWEEN 3 PRECEDING AND 1 PRECEDING
                ) * 100 as past_win_rate,
                -- 직전 3경기 데이터 개수 체크 (N/A 판정용)
                COUNT(*) OVER (
                    ORDER BY match_date, match_id
                    ROWS BETWEEN 3 PRECEDING AND 1 PRECEDING
                ) as prev_count
            FROM team_matches
        ),
        final_result AS (
            SELECT 
                ws.match_id,
                ws.match_date,
                ws.away_team_id,
                ws.home_team_id,
                ws.score_away,
                ws.score_home,
                s.stadium_name,
                t_away.team_name AS away_team_name,
                t_home.team_name AS home_team_name,
                -- 추세 판정
                CASE 
                    WHEN ws.prev_count < 3 THEN 'N/A'
                    WHEN ws.recent_win_rate > ws.past_win_rate THEN 'Up'
                    WHEN ws.recent_win_rate < ws.past_win_rate THEN 'Down'
                    ELSE 'Stable'
                END as trend,
                -- 아이콘
                CASE 
                    WHEN ws.prev_count < 3 THEN '-'
                    WHEN ws.recent_win_rate > ws.past_win_rate THEN '▲'
                    WHEN ws.recent_win_rate < ws.past_win_rate THEN '▼'
                    ELSE '➖'
                END as trend_icon,
                -- 색상
                CASE 
                    WHEN ws.prev_count < 3 THEN '#adb5bd'
                    WHEN ws.recent_win_rate > ws.past_win_rate THEN '#28a745'
                    WHEN ws.recent_win_rate < ws.past_win_rate THEN '#dc3545'
                    ELSE '#6c757d'
                END as trend_color
            FROM windowed_stats ws
            JOIN stadiums s ON ws.stadium_id = s.stadium_id
            JOIN teams t_away ON ws.away_team_id = t_away.team_id
            JOIN teams t_home ON ws.home_team_id = t_home.team_id
        )
        SELECT * FROM final_result
        ORDER BY match_date $sort_order_matches
        LIMIT ? OFFSET ?
        ";
        
        // PreparedStatement 바인딩
        if ($stmt_matches = $conn->prepare($sql_matches)) {
            // team_id를 4번 바인딩 (CTE에서 4번 사용)
            $stmt_matches->bind_param('iiiiii', 
                $team_id_matches, $team_id_matches,  // team_matches CTE
                $team_id_matches, $team_id_matches,  // WHERE 절
                $per_page_matches, $offset_matches    // LIMIT, OFFSET
            );
            
            $stmt_matches->execute();
            $result_matches = $stmt_matches->get_result();
            
            if ($result_matches) {
                while ($row = $result_matches->fetch_assoc()) {
                    $matches_matches[] = $row;
                }
            }
            $stmt_matches->close();
        }
        
    } else {
        // 팀이 선택되지 않은 경우: 기본 쿼리 (추세 없음)
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
                t_home.team_name AS home_team_name,
                '' as trend,
                '' as trend_icon,
                '#999' as trend_color
            FROM 
                matches m
            JOIN stadiums s ON m.stadium_id = s.stadium_id
            JOIN teams t_away ON m.away_team_id = t_away.team_id
            JOIN teams t_home ON m.home_team_id = t_home.team_id
            $where_sql
            ORDER BY m.match_date $sort_order_matches
            LIMIT ? OFFSET ?
        ";
        
        if ($stmt_matches = $conn->prepare($sql_matches)) {
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
}

if ($conn) {
    $conn->close();
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
</div>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>
