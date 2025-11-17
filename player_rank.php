
<?php
// player_rank.php

session_start();
$page_title = "player_rank";

// =======================
// 0. 국기 이미지 매핑 함수
// =======================
function getFlagHtml($nationality) {
    $key = strtolower(trim($nationality));

    $map = [
        'republic of korea'                => 'kr',
        'united states of america'         => 'us',
        'dominican republic'               => 'do',
        'commonwealth of puerto rico'      => 'pr',
        'canada'                           => 'ca',
        'bolivarian republic of venezuela' => 've',
        'republic of cuba'                 => 'cu',
        'republic of panama'               => 'pa',
        'japan'                            => 'jp',
    ];

    if (!isset($map[$key])) {
        // 매핑 안 된 국가는 그냥 텍스트로 보여주기
        return htmlspecialchars($nationality);
    }

    $code = $map[$key];
    $src  = "flags/{$code}.png"; // 실제 파일 경로에 맞게 수정

    return '<img src="' . $src . '" alt="' . strtoupper($code) . ' flag" class="flag-icon">';
}




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
$player_keyword = isset($_GET['player'])   ? trim($_GET['player']) : '';

// ▽▽▽ NEW: 날씨 중 하나라도 ALL 인지 체크 ▽▽▽
$has_any_all_weather =
    ($temp_bucket  === 'ALL') ||
    ($humid_bucket === 'ALL') ||
    ($wind_bucket  === 'ALL') ||
    ($rain_bucket  === 'ALL');
// △△△ NEW △△△

// 버킷 옵션 (폼 렌더링용)
$temp_options  = ['ALL','<10','10-15','15-20','20-25','25-30','>=30'];
$humid_options = ['ALL','<50','50-60','60-70','70-80','>=80'];
$wind_options  = ['ALL','<1','1-2','2-3','3-5','>=5'];
$rain_options  = ['ALL','0','0-1','1-5','5-10','>10','UNK'];

// =============================
// 2-1. 화면 표시용 label 매핑
// =============================
$temp_labels = [
    'ALL' => '🌡️ Temperature (All)',
    '<10' => 'Below 10℃',
    '10-15' => '10–15℃',
    '15-20' => '15–20℃',
    '20-25' => '20–25℃',
    '25-30' => '25–30℃',
    '>=30' => '30℃ and above'
];

$humid_labels = [
    'ALL' => '💦 Humidity (All)',
    '<50' => 'Below 50%',
    '50-60' => '50-60%',
    '60-70' => '60-70%',
    '70-80' => '70-80%',
    '>=80' => '80% and above'
];

$rain_labels = [
    'ALL' => '☔ Rainfall (All)',
    '0' => '0mm',
    '0-1' => '0-1mm',
    '1-5' => '1-5mm',
    '5-10' => '5-10mm',
    '>10' => '10mm and above',
    'UNK' => 'unknown'
];

$wind_labels = [
    'ALL' => '🍃 Wind Speed (All)',
    '<1' => 'Below 1m/s',
    '1-2' => '1-2m/s',
    '2-3' => '2-3m/s',
    '3-5' => '3-5m/s',
    '>=5' => '5m/s and above'
];

if ($conn->connect_error) {
    $error_message = "데이터베이스 연결 실패: " . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");

    // =======================
    // 3. 기본 WHERE 조건 생성
    // =======================
    $where  = [];
    $params = [];
    $types  = '';

    // 포지션에 따른 조건 + ORDER BY
    if ($position === 'batters') {
        // 버킷 단위든, 선수 합계든 "타자 데이터가 실제로 있는 행만" 사용
        $where[] = "pwp.bat_matches_count > 0 AND pwp.avg_ba IS NOT NULL";

        // ALL이 있으면 집계된 컬럼 기준, 아니면 원래 pwp 기준
        if ($has_any_all_weather) {
            $order_by = "avg_ops DESC, avg_ba DESC, bat_matches_count DESC";
        } else {
            $order_by = "pwp.avg_ops DESC, pwp.avg_ba DESC, pwp.bat_matches_count DESC";
        }
        // △△△
    } else { // pitchers
        $position = 'pitchers'; 
        $where[] = "pwp.pitch_matches_count > 0 AND pwp.avg_era IS NOT NULL";

        if ($has_any_all_weather) {
            $order_by = "avg_era ASC, pitch_matches_count DESC";
        } else {
            $order_by = "pwp.avg_era ASC, pwp.pitch_matches_count DESC";
        }
    }

    // 날씨 버킷 필터 (ALL이 아닌 애들만 WHERE에 추가)
    if ($temp_bucket !== 'ALL') {
        $where[]  = "pwp.temp_bucket = ?";
        $params[] = $temp_bucket;
        $types   .= 's';
    }
    if ($humid_bucket !== 'ALL') {
        $where[]  = "pwp.humidity_bucket = ?";
        $params[] = $humid_bucket;
        $types   .= 's';
    }
    if ($wind_bucket !== 'ALL') {
        $where[]  = "pwp.wind_bucket = ?";
        $params[] = $wind_bucket;
        $types   .= 's';
    }
    if ($rain_bucket !== 'ALL') {
        $where[]  = "pwp.rain_bucket = ?";
        $params[] = $rain_bucket;
        $types   .= 's';
    }

    if ($player_keyword !== '') {
    $where[]  = "pwp.player_name LIKE ?";
    $params[] = '%' . $player_keyword . '%';
    $types   .= 's';  
    }

    $where_sql = '';
    if (!empty($where)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where);
    }

    // =======================
    // 4. 최종 SQL
    //   (1) 날씨 중 하나라도 ALL → 선수당 1행으로 집계
    //   (2) ALL이 전혀 없음 → 원래처럼 버킷별 행 그대로
    // =======================

    if ($has_any_all_weather) {
        // ▽▽▽ 선수별 집계 모드 ▽▽▽
        if ($position === 'batters') {
            // 타자: 경기 수 합산 + 가중 평균 BA/OPS
            $sql = "
                SELECT
                    pwp.player_id,
                    pwp.player_name,
                    t.team_name,
                    pl.position,
                    pl.age,
                    pl.nationality,
                    pl.salary,
                    SUM(pwp.bat_matches_count)   AS bat_matches_count,
                    0                             AS pitch_matches_count,
                    ROUND(
                        SUM(pwp.avg_ba  * pwp.bat_matches_count) /
                        NULLIF(SUM(pwp.bat_matches_count), 0),
                        3
                    ) AS avg_ba,
                    ROUND(
                        SUM(pwp.avg_ops * pwp.bat_matches_count) /
                        NULLIF(SUM(pwp.bat_matches_count), 0),
                        3
                    ) AS avg_ops,
                    NULL AS avg_era
                FROM player_weather_performance pwp
                JOIN players pl ON pl.player_id = pwp.player_id
                JOIN teams   t  ON t.team_id    = pl.team_id
                $where_sql
                GROUP BY
                    pwp.player_id,
                    pwp.player_name,
                    t.team_name,
                    pl.position,
                    pl.age,
                    pl.nationality,
                    pl.salary
                ORDER BY $order_by
            ";
        } else {
            // 투수: 경기 수 합산 + 가중 평균 ERA
            $sql = "
                SELECT
                    pwp.player_id,
                    pwp.player_name,
                    t.team_name,
                    pl.position,
                    pl.age,
                    pl.nationality,
                    pl.salary,
                    0                            AS bat_matches_count,
                    SUM(pwp.pitch_matches_count) AS pitch_matches_count,
                    NULL AS avg_ba,
                    NULL AS avg_ops,
                    ROUND(
                        SUM(pwp.avg_era * pwp.pitch_matches_count) /
                        NULLIF(SUM(pwp.pitch_matches_count), 0),
                        2
                    ) AS avg_era
                FROM player_weather_performance pwp
                JOIN players pl ON pl.player_id = pwp.player_id
                JOIN teams   t  ON t.team_id    = pl.team_id
                $where_sql
                GROUP BY
                    pwp.player_id,
                    pwp.player_name,
                    t.team_name,
                    pl.position,
                    pl.age,
                    pl.nationality,
                    pl.salary
                ORDER BY $order_by
            ";
        }
    } else {
        // 원래 방식: (선수+4버킷) 조합별 한 행
        $sql = "
            SELECT 
                pwp.player_id,
                pwp.player_name,
                t.team_name,
                pl.position,
                pl.age,
                pl.nationality,
                pl.salary,
                pwp.bat_matches_count,
                pwp.pitch_matches_count,
                pwp.avg_ba,
                pwp.avg_ops,
                pwp.avg_era
            FROM player_weather_performance pwp
            JOIN players pl ON pl.player_id = pwp.player_id
            JOIN teams   t  ON t.team_id    = pl.team_id
            $where_sql
            ORDER BY $order_by
        ";
    }


    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error_message = "쿼리 준비 중 오류: " . $conn->error;
    } else {
        if (!empty($params)) {
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

$colspan = ($position === 'batters') ? 10 : 9;


require_once 'header.php';
?>

<div class="card-box">
    <h1 class="page-title">Player Ranking by Weather</h1>
    <p class="page-description">
        Set weather conditions to discover<br>
        which players perform best under each climate.
    </p>

    <div class="table-header-row">
        <h2 class="section-title">
            Ranking Table - <?php echo ($position === 'batters') ? 'Batters' : 'Pitchers'; ?>
        </h2>
    </div>

        <div class = "search-row">
            <form method="get" class="player-search-form">
                <!-- 지금 쓰고 있는 hidden 필터들 그대로 유지 -->
                <input type="hidden" name="position" value="<?php echo htmlspecialchars($position); ?>">
                <input type="hidden" name="temp"     value="<?php echo htmlspecialchars($temp_bucket); ?>">
                <input type="hidden" name="humid"    value="<?php echo htmlspecialchars($humid_bucket); ?>">
                <input type="hidden" name="wind"     value="<?php echo htmlspecialchars($wind_bucket); ?>">
                <input type="hidden" name="rain"     value="<?php echo htmlspecialchars($rain_bucket); ?>">

                <input
                    type="text"
                    name="player"
                    class="player-search-input"
                    placeholder="Search player"
                    value="<?php echo htmlspecialchars($player_keyword); ?>"
                >
                <button type="submit" class="player-search-button">Search</button>
            </form>
        </div>

    <!-- 필터 폼 -->
    <form method="get" class="filter-bar">
        <!-- Player Position -->
        <div class="filter-dropdown">
            <select name="position" class="filter-toggle">
                <option value="batters"  <?php if ($position === 'batters')  echo 'selected'; ?>>🧢 Batters</option>
                <option value="pitchers" <?php if ($position === 'pitchers') echo 'selected'; ?>>🧢 Pitchers</option>
            </select>
        </div>

        <!-- Temperature -->
        <div class="filter-dropdown">
            <select name="temp" class="filter-toggle">
                <?php foreach ($temp_options as $opt): ?>
                    <?php $label = isset($temp_labels[$opt]) ? $temp_labels[$opt] : $opt; ?>
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
                    <?php $label = isset($humid_labels[$opt]) ? $humid_labels[$opt] : $opt; ?>                    
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
                    <?php $label = isset($wind_labels[$opt]) ? $wind_labels[$opt] : $opt; ?> 
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
                    <?php $label = isset($rain_labels[$opt]) ? $rain_labels[$opt] : $opt; ?> 
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
                    <th>Position</th>
                    <th>Age</th>
                    <th>Nationality</th>
                    <th class="col-salary">Salary(KRW)</th>
                    <?php if ($position === 'batters'): ?>
                        <th>Avg OPS</th>
                        <th>Batting AVG</th>
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
                    <td colspan="<?php echo $colspan; ?>" style="padding: 12px; color: red;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </td>
                </tr>
            <?php elseif (empty($players)): ?>
                <tr>
                    <td colspan="<?php echo $colspan; ?>" style="padding: 12px; text-align:center;">
                        No data.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($players as $p): ?>
                    <tr>
                        <td><?php echo $p['rank']; ?></td>
                        <td><?php echo htmlspecialchars($p['player_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['position']); ?></td>
                        <td><?php echo $p['age']; ?></td>
                        <td class="col-nationality">
                            <?php echo getFlagHtml($p['nationality']); ?>
                        </td>
                        <td class="col-salary">
                            <?php
                            if ($p['salary'] !== null) {
                                echo number_format($p['salary']); // 30,000,000 이런 형식
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <?php if ($position === 'batters'): ?>
                            <td><?php echo $p['avg_ops']; ?></td>
                            <td><?php echo $p['avg_ba']; ?></td>
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
