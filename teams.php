<?php
// BDAProject/teams.php
// <!-- author: Jwa Yeonjoo -->

session_start();
$page_title = "teams";

// 1. DB 연결 설정 불러오기 (config/config.php 사용)
require_once 'config/config.php';

$teams = [];
$error_message = null;
$result = false;
// 현재 사용자의 북마크 팀 ID를 저장할 변수
$current_favorite_team_id = null;

// config.php에서 $conn이 생성되었는지 확인
if (!isset($conn) || $conn->connect_error) {
    $error_message = "데이터베이스 연결 실패: " . ($conn->connect_error ?? 'Connection object not found');
} else {
    // config.php에서 이미 utf8mb4 설정을 완료했다고 가정하거나, 필요시 추가
    // $conn->set_charset("utf8mb4");

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
             // 쿼리 준비 실패 시 오류 메시지 추가 (디버깅용)
             // 실제 서비스에서는 로그로 남기는 것이 좋습니다.
             $error_message .= " [Favorite team SQL prep failed: " . $conn->error . "]";
        }
    } 

    // 2. SQL 쿼리 실행 (팀 목록 조회)
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
        if ($conn->error) {
            $error_message = "팀 정보 조회 오류: " . $conn->error;
        } else {
            $error_message = "팀 정보를 불러올 수 없습니다. 데이터가 존재하는지 확인하세요.";
        }
    }
}

// 5. 헤더 및 푸터 파일 포함
require_once 'header.php'; 
?>

<!-- 디버깅을 위한 정보 출력 제거 -->

<div class="card-box team-list-card">
    <?php if ($error_message): ?>
        <p style="color: red; padding: 10px;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <h3>2024 KBO League Team Information</h3>

    <p class="description">Korea Baseball Organization (KBO) Regular League 10 Teams Information</p><br>

    <table class="team-table">
        <thead>
            <tr>
                <!-- 헤더 추가 (가독성 향상) -->
                <th>Team Name</th>
                <th>Location</th>
                <th>Home Stadium</th>
                <th>Founded Year</th>
                <th>Championships</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teams)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No data available. Please check the database.</td>
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
                    <td>Since </span> <?php echo htmlspecialchars($team['founded']); ?></td>
                    <td>
                        <!-- 우승 횟수 중복 출력 로직 수정 -->
                        <span class="icon">🏆</span> 
                        <span class="<?php if ($team['championships'] == 0) echo 'zero-championships'; ?>">
                            <?php echo htmlspecialchars($team['championships']); ?>
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