<?php
// author: Sumin Son

session_start();
$page_title = "team_rank";

// =======================
// 0. 팀 로고 매핑핑
// =======================
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
        return null; // 로고 없으면 null
    }

    $code = $map[$key];
    return "logos/{$code}.png";  //
}

// =======================
// 1. DB 연결 세팅
// =======================

require_once __DIR__ . '/config/config.php';

$teams = [];
$error_message = null;


// =======================
// 2. 필터 값 받기
// =======================
$month        = isset($_GET['month']) ? $_GET['month'] : 'ALL';
$temp_bucket  = isset($_GET['temp'])  ? $_GET['temp']  : 'ALL';
$humid_bucket = isset($_GET['humid']) ? $_GET['humid'] : 'ALL';
$wind_bucket  = isset($_GET['wind'])  ? $_GET['wind']  : 'ALL';
$rain_bucket  = isset($_GET['rain'])  ? $_GET['rain']  : 'ALL';

// 월 옵션
$month_options = [
    'ALL' => 'All Season',
    '3'   => 'March',
    '4'   => 'April',
    '5'   => 'May',
    '6'   => 'June',
    '7'   => 'July',
    '8'   => 'August',
    '9'   => 'September',
    '10'  => 'October',
];

// 날씨 버킷 옵션 (player_rank.php 와 동일)
$temp_options  = ['ALL','<10','10-15','15-20','20-25','25-30','>=30'];
$humid_options = ['ALL','<50','50-60','60-70','70-80','>=80'];
$wind_options  = ['ALL','<1','1-2','2-3','3-5','>=5'];
$rain_options  = ['ALL','0','0-1','1-5','5-10','>10','UNK'];


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
    // 3. WHERE 조건 만들기
    // =======================
    $where  = [];
    $types  = '';
    $params = [];

    // 월 필터 (3~10, 그 외는 ALL)
    if ($month !== 'ALL') {
        $where[]  = "MONTH(m.match_date) = ?";
        $types   .= 'i';
        $params[] = (int)$month;
    }

    // --- 온도 버킷 ---
    if ($temp_bucket !== 'ALL') {
        switch ($temp_bucket) {
            case '<10':
                $where[] = "m.temp < 10";
                break;
            case '10-15':
                $where[] = "m.temp >= 10 AND m.temp < 15";
                break;
            case '15-20':
                $where[] = "m.temp >= 15 AND m.temp < 20";
                break;
            case '20-25':
                $where[] = "m.temp >= 20 AND m.temp < 25";
                break;
            case '25-30':
                $where[] = "m.temp >= 25 AND m.temp < 30";
                break;
            case '>=30':
                $where[] = "m.temp >= 30";
                break;
        }
    }

    // --- 습도 버킷 ---
    if ($humid_bucket !== 'ALL') {
        switch ($humid_bucket) {
            case '<50':
                $where[] = "m.humidity < 50";
                break;
            case '50-60':
                $where[] = "m.humidity >= 50 AND m.humidity < 60";
                break;
            case '60-70':
                $where[] = "m.humidity >= 60 AND m.humidity < 70";
                break;
            case '70-80':
                $where[] = "m.humidity >= 70 AND m.humidity < 80";
                break;
            case '>=80':
                $where[] = "m.humidity >= 80";
                break;
        }
    }

    // --- 풍속 버킷 ---
    if ($wind_bucket !== 'ALL') {
        switch ($wind_bucket) {
            case '<1':
                $where[] = "m.wind_speed < 1";
                break;
            case '1-2':
                $where[] = "m.wind_speed >= 1 AND m.wind_speed < 2";
                break;
            case '2-3':
                $where[] = "m.wind_speed >= 2 AND m.wind_speed < 3";
                break;
            case '3-5':
                $where[] = "m.wind_speed >= 3 AND m.wind_speed < 5";
                break;
            case '>=5':
                $where[] = "m.wind_speed >= 5";
                break;
        }
    }

    // --- 강수량 버킷 ---
    if ($rain_bucket !== 'ALL') {
        switch ($rain_bucket) {
            case '0':
                $where[] = "m.rainfall = 0";
                break;
            case '0-1':
                $where[] = "m.rainfall > 0 AND m.rainfall <= 1";
                break;
            case '1-5':
                $where[] = "m.rainfall > 1 AND m.rainfall <= 5";
                break;
            case '5-10':
                $where[] = "m.rainfall > 5 AND m.rainfall <= 10";
                break;
            case '>10':
                $where[] = "m.rainfall > 10";
                break;
            case 'UNK':
                $where[] = "m.rainfall IS NULL";
                break;
        }
    }

    $where_sql = '';
    if (!empty($where)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where);
    }

    // =======================
    // 4. 팀별 승수/경기 수 집계
    // =======================
    $sql = "
        SELECT
            t.team_id,
            t.team_name,
            t.city,
            t.founded_year,
            t.winnings,
            COUNT(*) AS games,
            SUM(
                CASE
                    WHEN (m.home_team_id = t.team_id AND m.score_home > m.score_away)
                      OR (m.away_team_id = t.team_id AND m.score_away > m.score_home)
                    THEN 1
                    ELSE 0
                END
            ) AS wins
        FROM teams t
        JOIN matches m
          ON m.home_team_id = t.team_id
          OR m.away_team_id = t.team_id
        $where_sql
        GROUP BY
            t.team_id,
            t.team_name,
            t.city,
            t.founded_year,
            t.winnings
        ORDER BY
            wins DESC,
            games DESC
    ";

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
                $rank = 0;
                $row_index = 0;
                $prev_wins  = null;
                $prev_games = null;

                while ($row = $result->fetch_assoc()) {
                    $row_index++;

                    $current_wins  = (int)$row['wins'];
                    $current_games = (int)$row['games'];

                    // 바로 이전 팀과 wins, games 둘 다 같으면 → 같은 순위 유지
                    if ($prev_wins === $current_wins && $prev_games === $current_games) {
                        // $rank 그대로 사용
                    } else {
                        // 다르면 → 현재 줄 번호를 새 순위로 사용
                        $rank = $row_index;
                        $prev_wins  = $current_wins;
                        $prev_games = $current_games;
                    }

                    $row['rank'] = $rank;
                    $teams[] = $row;
                }
            } else {
                $error_message = "조건에 맞는 팀 데이터가 없습니다.";
            }
            $result && $result->free();
        } else {
            $error_message = "쿼리 실행 오류: " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}


$colspan = 7;

require_once 'header.php';
?>

<div class="card-box">
    <h1 class="page-title">Team Ranking by Weather</h1>
    <p class="page-description">
        Select month and weather conditions<br>
        to see which teams win the most under each climate.
    </p>

    <div class="table-header-row">
        <h2 class="section-title">Ranking Table - Teams</h2>
    </div>

    <!-- 필터 바 -->
    <form method="get" class="filter-bar">
        <!-- Month -->
        <div class="filter-dropdown">
            <select name="month" class="filter-toggle">
                <?php foreach ($month_options as $value => $label): ?>
                    <option value="<?php echo $value; ?>"
                        <?php if ((string)$month === (string)$value) echo 'selected'; ?>>
                        📅 <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
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
                    <th>Team</th>
                    <th>City</th>
                    <th>Founded</th>
                    <th>All-time Wins (KBO)</th>
                    <th>Wins (Filtered)</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($error_message)): ?>
                <tr>
                    <td colspan="<?php echo $colspan; ?>" style="padding: 12px; color: red;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </td>
                </tr>
            <?php elseif (empty($teams)): ?>
                <tr>
                    <td colspan="<?php echo $colspan; ?>" style="padding: 12px; text-align:center;">
                        No data.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo $t['rank']; ?></td>
                        <td class="col-team">
                            <?php
                            $logo = getTeamLogoSrc($t['team_name']);
                            if ($logo):
                            ?>
                                <img src="<?php echo $logo; ?>"
                                     alt="<?php echo htmlspecialchars($t['team_name']); ?> logo"
                                     class="team-logo">
                            <?php endif; ?>
                            <span class="team-name">
                                <?php echo htmlspecialchars($t['team_name']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($t['city']); ?></td>
                        <td><?php echo htmlspecialchars($t['founded_year']); ?></td>
                        <td><?php echo htmlspecialchars($t['winnings']); ?></td>
                        <td><?php echo $t['wins']; ?></td>
                        <td><?php echo $t['games']; ?></td>
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
