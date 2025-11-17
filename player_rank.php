<?php
// player_rank.php

session_start();
$page_title = "player_rank";

// =======================
// 1. DB 연결 세팅
// =======================
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
$players = [];
$error_message = null;

// =======================
// 2. 필터 값 받기 (GET)
// =======================
$position       = isset($_GET['position']) ? $_GET['position'] : 'pitchers';  // 'batters' or 'pitchers'
$temp_bucket    = isset($_GET['temp'])     ? $_GET['temp']     : 'ALL';
$humid_bucket   = isset($_GET['humid'])    ? $_GET['humid']    : 'ALL';
$wind_bucket    = isset($_GET['wind'])     ? $_GET['wind']     : 'ALL';
$rain_bucket    = isset($_GET['rain'])     ? $_GET['rain']     : 'ALL';

// 버킷 옵션 (폼 렌더링용)
$temp_options = ['ALL','<10','10-15','15-20','20-25','25-30','>=30'];
$humid_options = ['ALL','<50','50-60','60-70','70-80','>=80'];
$wind_options  = ['ALL','<1','1-2','2-3','3-5','>=5'];
$rain_options  = ['ALL','0','0-1','1-5','5-10','>10','UNK'];

// =============================
// 2-1. 화면 표시용 label 매핑
// =============================
$temp_labels = [
    'ALL' => 'Temperature (All)',
    '<10' => 'Below 10℃',
    '10-15' => '10–15℃',
    '15-20' => '15–20℃',
    '20-25' => '20–25℃',
    '25-30' => '25–30℃',
    '>=30' => '30℃ and above'
];

$humid_labels = [
    'ALL' => 'Humidity (All)',
    '<50' => 'Below 50%',
    '50-60' => '50-60%',
    '60-70' => '60-70%',
    '70-80' => '70-80%',
    '>=80' => '80% and above'
];

$rain_labels = [
    'ALL' => 'Rainfall (All)',
    '0' => '0mm',
    '0-1' => '0-1mm',
    '1-5' => '1-5mm',
    '5-10' => '5-10mm',
    '>10' => '10mm and above',
    'UNK' => 'unknown'
];


$wind_labels = [
    'ALL' => 'Wind Speed(All)',
    '<1' => 'Below 1m/s',
    '1-2' => '1-2m/s',
    '2-3' => '2-3m/s',
    '3-5' => '3-4m/s',
    '>=5' => '5m/s and above'
];



if ($conn->connect_error) {
    $error_message = "데이터베이스 연결 실패: " . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");

    // =======================
    // 3. 기본 WHERE 조건 생성
    // =======================
    $where = [];
    $params = [];
    $types  = '';

    // 포지션에 따른 조건
    if ($position === 'batters') {
        $where[] = "pwp.bat_matches_count > 0 AND pwp.avg_ba IS NOT NULL";
        $order_by = "pwp.avg_ba DESC, pwp.bat_matches_count DESC";
    } else { // pitchers
        $position = 'pitchers'; // 혹시 이상한 값 들어오면 강제 세팅
        $where[] = "pwp.pitch_matches_count > 0 AND pwp.avg_era IS NOT NULL";
        $order_by = "pwp.avg_era ASC, pwp.pitch_matches_count DESC";
    }

    // 날씨 버킷 필터
    if ($temp_bucket !== 'ALL') {
        $where[] = "pwp.temp_bucket = ?";
        $params[] = $temp_bucket;
        $types   .= 's';
    }
    if ($humid_bucket !== 'ALL') {
        $where[] = "pwp.humidity_bucket = ?";
        $params[] = $humid_bucket;
        $types   .= 's';
    }
    if ($wind_bucket !== 'ALL') {
        $where[] = "pwp.wind_bucket = ?";
        $params[] = $wind_bucket;
        $types   .= 's';
    }
    if ($rain_bucket !== 'ALL') {
        $where[] = "pwp.rain_bucket = ?";
        $params[] = $rain_bucket;
        $types   .= 's';
    }

    $where_sql = '';
    if (!empty($where)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where);
    }

    // =======================
    // 4. 최종 SQL
    //   player_weather_performance + players + teams
    // =======================
    $sql = "
        SELECT 
            pwp.player_id,
            pwp.player_name,
            t.team_name,
            pwp.bat_matches_count,
            pwp.pitch_matches_count,
            pwp.avg_ba,
            pwp.avg_ops,
            pwp.avg_era
        FROM player_weather_performance pwp
        JOIN players pl ON pl.player_id = pwp.player_id
        JOIN teams   t  ON t.team_id = pl.team_id
        $where_sql
        ORDER BY $order_by
        LIMIT 50
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error_message = "쿼리 준비 중 오류: " . $conn->error;
    } else {
        if (!empty($params)) {
            // PHP 7+ 가정
            $stmt->bind_param($types, ...$params);
        }
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $rank = 1;
                while ($row = $result->fetch_assoc()) {
                    $row['rank'] = $rank++;
                    $players[] = $row;
                }
            } else {
                $error_message = "조건에 맞는 선수 데이터가 없습니다.";
            }
            $result && $result->free();
        } else {
            $error_message = "쿼리 실행 오류: " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}

// =======================
// 5. 화면 출력
// =======================
require_once 'header.php';
?>

<div class="card-box">
    <h1 class="page-title">Player Ranking by Weather</h1>
    <p class="page-description">
        Set weather conditions to discover which players perform best under each climate.
    </p>


    <h2 class="section-title">
        Ranking Table - <?php echo ($position === 'batters') ? 'Batters' : 'Pitchers'; ?>
    </h2>

    <!-- 필터 폼 -->
    <form method="get" class="filter-bar">
        <!-- Player Position -->
        <div class="filter-dropdown">
            <select name="position" class="filter-toggle">
                <option value="batters"  <?php if ($position === 'batters')  echo 'selected'; ?>>Batters</option>
                <option value="pitchers" <?php if ($position === 'pitchers') echo 'selected'; ?>>Pitchers</option>
            </select>
        </div>

        <!-- Temperature -->
        <div class="filter-dropdown">
            <select name="temp" class="filter-toggle">
                <?php foreach ($temp_options as $opt): ?>
                    <?php
                        $label = isset($temp_labels[$opt]) ? $temp_labels[$opt] : $opt;
                    ?>
                    <option value="<?php echo $opt; ?>" <?php if ($temp_bucket === $opt) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Humidity -->
        <div class="filter-dropdown">
            <select name="humid" class="filter-toggle">
                <?php foreach ($humid_options as $opt): ?>
                    <?php
                        $label = isset($humid_labels[$opt]) ? $humid_labels[$opt] : $opt;
                    ?>                    
                    <option value="<?php echo $opt; ?>" <?php if ($humid_bucket === $opt) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Wind -->
        <div class="filter-dropdown">
            <select name="wind" class="filter-toggle">
                <?php foreach ($wind_options as $opt): ?>
                    <?php
                        $label = isset($wind_labels[$opt]) ? $wind_labels[$opt] : $opt;
                    ?> 
                    <option value="<?php echo $opt; ?>" <?php if ($wind_bucket === $opt) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Rain -->
        <div class="filter-dropdown">
            <select name="rain" class="filter-toggle">
                <?php foreach ($rain_options as $opt): ?>
                    <?php
                        $label = isset($rain_labels[$opt]) ? $rain_labels[$opt] : $opt;
                    ?> 
                    <option value="<?php echo $opt; ?>" <?php if ($rain_bucket === $opt) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="filter-toggle">Apply</button>
    </form>


    <div class="player-rank-card">
        <table class="player-rank-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Player</th>
                    <th>Team</th>
                    <?php if ($position === 'batters'): ?>
                        <th>Avg BA</th>
                        <th>Avg OPS</th>
                        <th>Games</th>
                    <?php else: ?>
                        <th>Avg ERA</th>
                        <th>Games</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($error_message)): ?>
                <tr>
                    <td colspan="6" style="padding: 12px; color: red;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </td>
                </tr>
            <?php elseif (empty($players)): ?>
                <tr>
                    <td colspan="6" style="padding: 12px; text-align:center;">
                        No data.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($players as $p): ?>
                    <tr>
                        <td><?php echo $p['rank']; ?></td>
                        <td><?php echo htmlspecialchars($p['player_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['team_name']); ?></td>

                        <?php if ($position === 'batters'): ?>
                            <td><?php echo $p['avg_ba']; ?></td>
                            <td><?php echo $p['avg_ops']; ?></td>
                            <td><?php echo $p['bat_matches_count']; ?></td>
                        <?php else: ?>
                            <td><?php echo $p['avg_era']; ?></td>
                            <td><?php echo $p['pitch_matches_count']; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'footer.php';
?>
