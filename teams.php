<?php
// BDAProject/teams.php

session_start();
$page_title = "teams";

// ===========================================
// 1. 데이터베이스 연결 정보 설정 및 연결
// ===========================================
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306; // XAMPP에서 3306 포트가 충돌한다면, 3307 등으로 변경하세요.

// MySQLi 연결 시 호스트 이름에 포트를 포함시키지 않고, 별도의 인자로 전달합니다.
$conn = null;
$teams = [];
$error_message = null;
$result = false;

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

// 연결 오류 확인
if ($conn->connect_error) {
    // 개발 단계에서는 상세 오류를 보여주는 것이 좋습니다.
    $error_message = "데이터베이스 연결 실패: " . $conn->connect_error;
} else {
    // 한글 깨짐 방지
    $conn->set_charset("utf8mb4");

    // 2. SQL 쿼리 수정: t.team_id 추가
    $sql = "
        SELECT 
            t.team_id,          -- **새로 추가**
            t.team_name, 
            t.city, 
            t.founded_year, 
            t.winnings, 
            s.stadium_name 
        FROM 
            teams t 
        JOIN 
            stadiums s ON t.stadium_id = s.stadium_id 
        ORDER BY 
            t.team_name ASC
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $teams[] = [
                'id' => $row['team_id'],       // **새로 추가**
                'name' => $row['team_name'],
                'location' => $row['city'],
                'stadium' => $row['stadium_name'], 
                'founded' => $row['founded_year'],
                'championships' => $row['winnings']
            ];
        }
        $result->free();
    } else {
        $error_message = "팀 정보를 불러올 수 없습니다. 테이블을 확인하세요.";
    }

    $conn->close();
}

// 5. 헤더 및 푸터 파일 포함
require_once 'header.php'; 
?>

<div class="card-box team-list-card">
    <?php if ($error_message): ?>
        <p style="color: red; padding: 10px;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <h3>2024 KBO 리그 팀 정보</h3>

    <p class="description">한국야구위원회(KBO) 정규리그 10개 구단 정보</p><br>

    <table class="team-table">
        <thead>
            </thead>
        <tbody>
            <?php if (empty($teams)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">데이터가 없습니다. 데이터베이스를 확인하세요.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($teams as $team): ?>
                <tr>
                    <td data-team-id="<?php echo htmlspecialchars($team['id']); ?>">
                        <span class="bookmark-icon" data-team-id="<?php echo htmlspecialchars($team['id']); ?>">★</span>
                        <?php echo htmlspecialchars($team['name']); ?>
                    </td>
                    
                    <td><span class="icon">📍</span> <?php echo htmlspecialchars($team['location']); ?></td>
                    <td><?php echo htmlspecialchars($team['stadium']); ?></td>
                    <td><span class="icon">📅</span> <?php echo htmlspecialchars($team['founded']); ?>년</td>
                    <td>
                        <span class="icon">🏆</span> <?php echo htmlspecialchars($team['championships']); ?>회
                        <?php 
                        if ($team['championships'] == 0): ?>
                            <span class="zero-championships">0회</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'footer.php';
?>

<script src="public/bookmark.js"></script>