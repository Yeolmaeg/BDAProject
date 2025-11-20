<?php
// BDAProject/search_players.php
// author: Jwa Yeonjoo

session_start();

// 1. DB 연결 설정
$DB_HOST = '127.0.0.1'; 
$DB_NAME = 'team04';   
$DB_USER = 'root';     
$DB_PASS = '';         
$DB_PORT = 3306;       

$pdo = null; 

try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage()); 
}

// 2. URL에서 player_id 가져오기
$player_id = $_GET['player_id'] ?? null;
$player_id = filter_var($player_id, FILTER_VALIDATE_INT);

if (!$player_id) {
    header("Location: index.php");
    exit;
}

// 3. 선수 이름 및 팀 ID 가져오기 (players 테이블에서)
$stmt_player_info = $pdo->prepare("
    SELECT p.player_name, t.team_name 
    FROM players p
    JOIN teams t ON p.team_id = t.team_id
    WHERE p.player_id = :player_id
");
$stmt_player_info->execute(['player_id' => $player_id]);
$player_info = $stmt_player_info->fetch();

if (!$player_info) {
    $page_title = "Player Not Found";
    require_once 'header.php';
    echo '<div style="text-align: center; margin-top: 50px;"><h2>No player information found.</h2></div>';
    require_once 'footer.php';
    exit;
}

$player_name = $player_info['player_name'];
$team_name = $player_info['team_name'];
$page_title = $player_name . " Weather-Specific Performance Analysis";


// 4. player_weather_performance 데이터 조회
$stmt_perf = $pdo->prepare("
    SELECT * FROM player_weather_performance 
    WHERE player_id = :player_id
    ORDER BY temp_bucket, humidity_bucket, wind_bucket, rain_bucket
");
$stmt_perf->execute(['player_id' => $player_id]);
$performance_data = $stmt_perf->fetchAll();


// 5. 페이지 출력
require_once 'header.php';
?>

<div class="player-performance-page" style="max-width: 1000px; margin: 50px auto; padding: 20px;">
    
    <h1 style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px;">
        <?php echo htmlspecialchars($player_name); ?> (<?php echo htmlspecialchars($team_name); ?>) Weather-Specific Performance Analysis
    </h1>

    <?php if (empty($performance_data)): ?>
        <div style="text-align: center; margin-top: 40px; padding: 20px; background-color: #f0f0f0; border-radius: 8px;">
            <p style="font-size: 1.2em;">There is no weather-specific statistical data for this player.</p>
        </div>
    <?php else: ?>
    
        <h2 style="margin-top: 30px; font-size: 1.5em; color: #333;">Weather Condition Detailed Statistics</h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #e6e6ff;">
                    <th style="border: 1px solid #ddd; padding: 10px;">Temperature</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">Humidity</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">Wind Speed</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">Rainfall</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">At Bats</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">Pitches</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">Batting Average (BA)</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">OPS</th>
                    <th style="border: 1px solid #ddd; padding: 10px;">ERA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($performance_data as $data): ?>
                    <tr style="text-align: center;">
                        <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($data['temp_bucket']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($data['humidity_bucket']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($data['wind_bucket']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($data['rain_bucket']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;"><?php echo number_format($data['bat_matches_count']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px; font-weight: bold;"><?php echo number_format($data['pitch_matches_count']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px; color: #c00;"><?php echo is_null($data['avg_ba']) ? '-' : sprintf("%.3f", $data['avg_ba']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px; color: #c00;"><?php echo is_null($data['avg_ops']) ? '-' : sprintf("%.3f", $data['avg_ops']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px; color: #0056b3;"><?php echo is_null($data['avg_era']) ? '-' : sprintf("%.2f", $data['avg_era']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>