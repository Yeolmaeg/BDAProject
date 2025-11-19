<?php
// 페이지 제목 설정
$page_title = "Players";
require_once 'header.php'; 
require_once __DIR__ . '/config/config.php';

// DB 연결
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);
if ($conn->connect_errno) die("Database connection failed: " . $conn->connect_error);
$conn->set_charset('utf8mb4'); 
$conn->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'"); 


// 파라미터 처리
$selected_team = isset($_GET['team']) ? $_GET['team'] : '';
$selected_position = isset($_GET['position']) ? $_GET['position'] : '';

// [쿼리 1] 팀별 통계 (OLAP Drilldown by Position 구현 및 TRIM 적용)
$team_stats_query_base = "
    SELECT 
        t.team_name,
        COUNT(p.player_id) as player_count,
        COALESCE(ROUND(AVG(p.salary), 0), 0) as avg_salary,
        COALESCE(MIN(p.salary), 0) as min_salary,
        COALESCE(MAX(p.salary), 0) as max_salary
    FROM teams t
    LEFT JOIN players p ON TRIM(t.team_name) = TRIM(p.team_name) 
    WHERE 1=1
";

$team_stats_params = [];
$team_stats_types = "";

if ($selected_position) { 
    // 포지션이 선택되었을 경우, 해당 포지션으로 집계 필터링 (Drilldown)
    $team_stats_query_base .= " AND TRIM(p.position) = ?"; 
    $team_stats_params[] = $selected_position; 
    $team_stats_types .= "s"; 
}

$team_stats_query_base .= " GROUP BY t.team_name ORDER BY t.team_name";

$team_stats_stmt = $conn->prepare($team_stats_query_base);

if ($team_stats_params) {
    $team_stats_stmt->bind_param($team_stats_types, ...$team_stats_params);
}

$team_stats_stmt->execute();
$team_stats_result = $team_stats_stmt->get_result(); 


// [쿼리 2] 선수 목록 (TRIM() 적용)
$player_query = "SELECT p.player_id, p.player_name, t.team_name, p.position, p.salary
                 FROM players p JOIN teams t ON p.team_id = t.team_id WHERE 1=1";
$params = [];
$types = "";

if ($selected_team) { 
    $player_query .= " AND TRIM(t.team_name) = ?"; 
    $params[] = $selected_team; 
    $types .= "s";
}
if ($selected_position) { $player_query .= " AND p.position = ?"; $params[] = $selected_position; $types .= "s"; }

$player_query .= " ORDER BY p.salary DESC";
$stmt = $conn->prepare($player_query);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$player_result = $stmt->get_result();

// [쿼리 3] 포지션 필터용
$position_query = "SELECT DISTINCT position FROM players ORDER BY position";
$position_result = $conn->query($position_query);
?>

<div class="players-container">
    
    <div class="panel left">
        <div class="panel-header">
            <div class="panel-title">Teams Overview</div>
            <div class="panel-subtitle">
                <?php 
                $subtitle = "팀별 연봉 현황 및 선수단 규모";
                if ($selected_position) {
                    $subtitle = "선택된 포지션 (" . htmlspecialchars($selected_position) . ") 기준 통계";
                }
                echo $subtitle; 
                ?>
            </div>
        </div>

        <div class="custom-table">
            <div class="table-head">
                <div class="t-cell col-team">팀명</div>
                <div class="t-cell col-count">인원</div>
                <div class="t-cell col-salary">평균 연봉</div>
                <div class="t-cell col-salary">최소 연봉</div>
                <div class="t-cell col-salary">최대 연봉</div>
            </div>
            <div class="table-body">
                <?php 
                if ($team_stats_result->num_rows > 0):
                    while ($team = $team_stats_result->fetch_assoc()): 
                ?>
                    <div class="t-row clickable <?php echo ($selected_team == $team['team_name']) ? 'selected' : ''; ?>" 
                         onclick="selectTeam('<?php echo htmlspecialchars($team['team_name']); ?>')">
                        
                        <div class="t-cell col-team" style="font-weight:bold; color:#001F63;">
                            <?php echo htmlspecialchars($team['team_name']); ?>
                        </div>
                        
                        <div class="t-cell col-count">
                            <?php echo number_format((int)$team['player_count']); ?>명 
                        </div>
                        
                        <div class="t-cell col-salary">
                            <?php echo number_format((float)$team['avg_salary']); ?>
                        </div>
                        
                        <div class="t-cell col-salary">
                            <?php echo number_format((int)$team['min_salary']); ?>
                        </div>
                        
                        <div class="t-cell col-salary">
                            <?php echo number_format((int)$team['max_salary']); ?>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else:
                ?>
                    <div class="t-row">
                        <div class="t-cell" style="width:100%; padding:20px;">데이터가 없습니다.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="info-text">* 팀 이름을 클릭하여 해당 팀의 선수 목록을 조회하세요.</div>
    </div>

    <div class="panel right">
        <div class="panel-header">
            <div class="panel-title">
                <?php echo $selected_team ? htmlspecialchars($selected_team) . " 선수 목록" : "All Players"; ?>
            </div>
            <div class="filter-box">
                <label>포지션:</label>
                <select id="positionFilter" onchange="applyFilter()">
                    <option value="">모든 포지션</option>
                    <?php 
                    // 쿼리 결과를 처음부터 다시 가져와서 사용
                    $position_result->data_seek(0); 
                    while($row = $position_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['position']); ?>" 
                            <?php echo ($selected_position == $row['position']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['position']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button 
                    id="resetButton" 
                    onclick="resetFilters()" 
                    style="<?php echo (!$selected_team && !$selected_position) ? 'visibility: hidden;' : ''; ?>"
                >
                    초기화
                </button>
            </div>
        </div>

        <div class="custom-table">
            <div class="table-head">
                <div class="t-cell col-id">ID</div>
                <div class="t-cell col-name">이름</div>
                <div class="t-cell col-team">소속 팀</div>
                <div class="t-cell col-pos">포지션</div>
                <div class="t-cell col-sal">연봉</div>
            </div>
            
            <div class="table-body">
                <?php if ($player_result->num_rows > 0): ?>
                    <?php while ($player = $player_result->fetch_assoc()): ?>
                    <div class="t-row">
                        <div class="t-cell col-id"><?php echo htmlspecialchars($player['player_id']); ?></div>
                        <div class="t-cell col-name" style="font-weight:500;"><?php echo htmlspecialchars($player['player_name']); ?></div>
                        <div class="t-cell col-team"><?php echo htmlspecialchars($player['team_name']); ?></div>
                        <div class="t-cell col-pos"><?php echo htmlspecialchars($player['position']); ?></div>
                        <div class="t-cell col-sal"><?php echo number_format($player['salary']); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="t-row">
                        <div class="t-cell" style="width:100%; padding:20px;">해당 조건의 선수가 없습니다.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function selectTeam(teamName) {
    const currentPosition = document.getElementById('positionFilter').value;
    let url = 'players.php?team=' + encodeURIComponent(teamName);
    if (currentPosition) url += '&position=' + encodeURIComponent(currentPosition);
    window.location.href = url;
}

function applyFilter() {
    const position = document.getElementById('positionFilter').value;
    let url = 'players.php';
    const urlParams = new URLSearchParams(window.location.search);
    const team = urlParams.get('team');
    
    let params = [];
    if (team) params.push('team=' + encodeURIComponent(team));
    if (position) params.push('position=' + encodeURIComponent(position));
    
    if (params.length > 0) url += '?' + params.join('&');
    window.location.href = url;
}

function resetFilters() {
    window.location.href = 'players.php';
}
</script>

<?php 
// 쿼리 결과 메모리 해제
if (isset($team_stats_stmt)) $team_stats_stmt->close();
if (isset($stmt)) $stmt->close();
$position_result->close();
$conn->close();

require_once 'footer.php'; 
?>
