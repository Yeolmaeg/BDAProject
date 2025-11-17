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
$DB_PORT = 3306; 

$conn = null;
$teams = [];
$error_message = null;
$result = false;
// 현재 사용자의 북마크 팀 ID를 저장할 변수
$current_favorite_team_id = null;

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

// 연결 오류 확인
if ($conn->connect_error) {
    $error_message = "데이터베이스 연결 실패: " . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");

    // 1-1. 현재 로그인 사용자의 북마크 팀 ID를 조회합니다. (DB 연결 성공 시 실행)
    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        $sql_fav = "SELECT favorite_team_id FROM users WHERE user_id = ?";
        $stmt_fav = $conn->prepare($sql_fav);
        
        if ($stmt_fav) {
            $stmt_fav->bind_param("i", $user_id);
            $stmt_fav->execute();
            $result_fav = $stmt_fav->get_result();
            if ($row_fav = $result_fav->fetch_assoc()) {
                $current_favorite_team_id = $row_fav['favorite_team_id'];
            }
            $stmt_fav->close();
        } else {
             // 디버그 코드 제거 (오류 메시지만 남김)
             $error_message .= " [Favorite team SQL prep failed]";
        }
    } else {
        // 디버그 코드 제거 (오류 메시지만 남김)
        $error_message .= " [Session user_id is missing]";
    }


    // 2. SQL 쿼리 실행
    $sql = "
        SELECT 
            t.team_id, 
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
                'id' => $row['team_id'],
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
}
// DB 연결 종료는 데이터 로딩 완료 후, HTML 출력 직전에 수행합니다.
if ($conn && !$conn->connect_error) {
    $conn->close();
}


// 5. 헤더 및 푸터 파일 포함
require_once 'header.php'; 
?>

<!-- 디버깅을 위한 정보 출력 제거 -->

<div class="card-box team-list-card">
    <?php if ($error_message): ?>
        <p style="color: red; padding: 10px;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <h3>2024 KBO 리그 팀 정보</h3>

    <p class="description">한국야구위원회(KBO) 정규리그 10개 구단 정보</p><br>

    <table class="team-table">
        <thead>
            <tr>
                <!-- 헤더 추가 (가독성 향상) -->
                <th>팀 이름</th>
                <th>연고지</th>
                <th>홈 구장</th>
                <th>창단 연도</th>
                <th>우승 횟수</th>
            </tr>
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
                        <?php 
                        // 현재 팀 ID와 북마크 팀 ID가 일치하면 is-bookmarked 클래스를 추가합니다.
                        // $team['id']와 $current_favorite_team_id 모두 정수형으로 비교합니다.
                        $bookmark_class = ((int)$team['id'] === (int)$current_favorite_team_id) ? 'is-bookmarked' : '';
                        ?>
                        <span class="bookmark-icon <?php echo $bookmark_class; ?>" data-team-id="<?php echo htmlspecialchars($team['id']); ?>">★</span>
                        <?php echo htmlspecialchars($team['name']); ?>
                    </td>
                    
                    <td><span class="icon">📍</span> <?php echo htmlspecialchars($team['location']); ?></td>
                    <td><?php echo htmlspecialchars($team['stadium']); ?></td>
                    <td><span class="icon">📅</span> <?php echo htmlspecialchars($team['founded']); ?>년</td>
                    <td>
                        <!-- 우승 횟수 중복 출력 로직 수정 -->
                        <span class="icon">🏆</span> 
                        <span class="<?php if ($team['championships'] == 0) echo 'zero-championships'; ?>">
                            <?php echo htmlspecialchars($team['championships']); ?>회
                        </span>
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