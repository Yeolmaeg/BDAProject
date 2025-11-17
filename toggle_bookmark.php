<?php
// BDAProject/toggle_bookmark.php

session_start();
header('Content-Type: application/json'); // JSON 응답을 위한 헤더 설정

// 1. 로그인 여부 확인 (필수)
if (!isset($_SESSION['user_id'])) {
    // HTTP 401 Unauthorized 상태 코드를 사용하는 것이 더 적절합니다.
    http_response_code(401); 
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}
// 실제 DB에서 user_id의 데이터 타입(e.g., INT)에 맞게 캐스팅하여 사용합니다.
$user_id = (int)$_SESSION['user_id']; 

// 2. 클라이언트로부터 JSON 데이터 수신
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 팀 ID는 클라이언트로부터 문자열로 올 수 있으므로 정수로 변환하여 사용합니다.
$team_id = isset($data['team_id']) ? (int)$data['team_id'] : null;

if (!$team_id) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'message' => '팀 ID가 누락되었습니다. (Team ID Missing)']);
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
    http_response_code(503); // 503 Service Unavailable
    echo json_encode(['success' => false, 'message' => 'DB 연결 오류: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");

$stmt = null;
$message = '';
$action_success = false;

try {
    // 4-1. 현재 응원팀(북마크) 상태 확인 - 'favorite_team_id'로 수정
    $sql_check = "SELECT favorite_team_id FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_check);
    
    if (!$stmt) {
        throw new Exception("SQL Prepare Error (Check): " . $conn->error);
    }
    
    // 'i'는 $user_id가 정수형임을 나타냅니다.
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 'favorite_team_id' 컬럼 이름으로 결과 가져오기
    $current_fav_team = $result->fetch_assoc()['favorite_team_id'] ?? null;
    $stmt->close();


    // 4-2. 북마크 상태 토글 로직
    if ($current_fav_team == $team_id) {
        // 현재 팀이 이미 응원팀(북마크)이라면 -> 해제 (NULL로 설정) - 'favorite_team_id'로 수정
        $sql_update = "UPDATE users SET favorite_team_id = NULL WHERE user_id = ?";
        $stmt = $conn->prepare($sql_update);
        
        if (!$stmt) {
            throw new Exception("SQL Prepare Error (Unset): " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $message = "북마크가 해제되었습니다.";
        $action_success = true;
    } else {
        // 현재 팀이 응원팀이 아니라면 -> 설정 - 'favorite_team_id'로 수정
        $sql_update = "UPDATE users SET favorite_team_id = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql_update);
        
        if (!$stmt) {
            throw new Exception("SQL Prepare Error (Set): " . $conn->error);
        }
        
        // 'ii'는 $team_id와 $user_id가 모두 정수형임을 나타냅니다.
        $stmt->bind_param("ii", $team_id, $user_id);
        $message = "북마크가 설정되었습니다.";
        $action_success = true;
    }

    // 4-3. 업데이트 실행
    if ($stmt->execute()) {
        echo json_encode(['success' => $action_success, 'message' => $message, 'team_id' => $team_id]);
    } else {
        throw new Exception("SQL Execute Error: " . $stmt->error);
    }

} catch (Exception $e) {
    // 모든 예외를 잡아서 클라이언트에 오류 메시지를 전달
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['success' => false, 'message' => '서버 처리 오류: ' . $e->getMessage()]);
}

// 5. 연결 종료 (try-catch 블록 밖에서 안전하게 종료)
if ($stmt) {
    $stmt->close();
}
if ($conn) {
    $conn->close();
}
?>