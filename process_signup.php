<?php
// BDAProject/process_signup.php

session_start();

// 1. DB 연결 정보 설정 (이전과 동일)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;

// 2. 요청이 POST 방식인지 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit();
}

// 3. 데이터 검증 및 정리
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// 👇 추가된 변수
$bdate = $_POST['bdate'] ?? null; // YYYY-MM-DD 형식으로 들어옴
$phone = trim($_POST['phone'] ?? '');

// 필수 필드 검사 (bdate와 phone 추가)
if (empty($name) || empty($email) || empty($password) || empty($bdate) || empty($phone)) {
    header("Location: signup.php?error=missing_fields");
    exit();
}

// 비밀번호 길이 검사 (4자 이상 가정)
if (strlen($password) < 4) {
    header("Location: signup.php?error=password_short");
    exit();
}

// 🚩 수정: 전화번호 형식 검증 로직 추가
// 간단한 전화번호 형식 (숫자와 하이픈(-)만 허용)
if (!preg_match("/^\d{2,4}-?\d{3,4}-?\d{4}$/", $phone)) {
    header("Location: signup.php?error=phone_invalid");
    exit();
}
// 🚩 수정 끝

// 4. 비밀번호 해시
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 5. 데이터베이스 연결
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_error) {
    error_log("DB Connection Failed: " . $conn->connect_error);
    header("Location: signup.php?error=db_connect_failed");
    exit();
}

$conn->set_charset("utf8mb4");

try {
    // 6. SQL 쿼리 준비 (컬럼명 수정 반영 및 user_bdate, user_phone 추가)
    $sql = "INSERT INTO users (user_name, user_bdate, user_phone, user_email, user_pass, favorite_team_id, favorite_player_id) 
             VALUES (?, ?, ?, ?, ?, NULL, NULL)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $bdate, $phone, $email, $hashed_password);

    if ($stmt->execute()) {
        // 성공적으로 삽입되면 세션 설정 및 성공 페이지로 리다이렉트
        $_SESSION['user_id'] = $conn->insert_id; 
        $_SESSION['username'] = $name;

        header("Location: signup_success.php"); 
        exit();
    } else {
        // 🚩 수정: execute()가 false를 반환할 때의 오류 처리
        $error_message_key = ($conn->errno == 1062) ? "email_exists" : "signup_failed"; 
        header("Location: signup.php?error=" . $error_message_key);
        exit();
    }

} catch (Exception $e) {
    // 🚩 수정: Exception 발생 시 오류 코드(e->getCode)를 사용하여 정확한 원인 파악
    $error_code = $e->getCode();
    
    if ($error_code == 1062) {
        $error_message_key = "email_exists";
    } else {
        error_log("Signup Exception: " . $e->getMessage() . " Code: " . $error_code);
        $error_message_key = "exception";
    }

    header("Location: signup.php?error=" . $error_message_key);
    exit();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}