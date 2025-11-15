<?php
// BDAProject/toggle_bookmark.php

session_start();
header('Content-Type: application/json'); // JSON 응답을 위한 헤더 설정

// 1. 로그인 여부 확인 (필수)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}
$user_id = $_SESSION['user_id']; // 현재 로그인된 사용자 ID

// 2. 클라이언트로부터 JSON 데이터 수신
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$team_id = $data['team_id'] ?? null;

if (!$team_id) {
    echo json_encode(['success' => false, 'message' => '팀 ID가 누락되었습니다.']);
    exit;
}

// 3. DB 연결 (teams.php와 동일한 설정 사용)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT); 

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB 연결 오류']);
    exit;
}
$conn->set_charset("utf8mb4");


// 4. 북마크 상태 확인 및 토글 로직
// (실제 프로젝트에서는 users 테이블에 북마크 필드를 추가하거나, 
//  user_bookmarks라는 별도의 테이블을 사용하는 것이 일반적입니다.)

// 임시 로직: 여기서는 사용자의 응원팀(fav_team_id)을 북마크로 사용한다고 가정합니다.
// 실제 북마크 기능을 위해서는 별도의 테이블 구조가 필요합니다.

$sql_check = "SELECT fav_team_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_fav_team = $result->fetch_assoc()['fav_team_id'] ?? null;
$stmt->close();


if ($current_fav_team == $team_id) {
    // 현재 팀이 이미 응원팀(북마크)이라면 -> 해제
    $sql_update = "UPDATE users SET fav_team_id = NULL WHERE user_id = ?";
    $message = "북마크가 해제되었습니다.";
    $action_success = true;
} else {
    // 현재 팀이 응원팀이 아니라면 -> 설정
    $sql_update = "UPDATE users SET fav_team_id = ? WHERE user_id = ?";
    $message = "북마크가 설정되었습니다.";
    $action_success = true;
}

if (isset($sql_update)) {
    $stmt = $conn->prepare($sql_update);
    
    if ($current_fav_team == $team_id) {
        $stmt->bind_param("i", $user_id); // NULL로 업데이트할 때
    } else {
        $stmt->bind_param("ii", $team_id, $user_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => $action_success, 'message' => $message, 'team_id' => $team_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB 업데이트 오류']);
    }
    $stmt->close();
}


$conn->close();
?>