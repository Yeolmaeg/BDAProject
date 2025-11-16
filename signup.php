<?php
// BDAProject/signup.php

session_start();
$page_title = "signup";

// 1. DB 연결 설정 파일을 불러옵니다.
// DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT 변수가 포함되어야 합니다.
// teams.php에서 DB 연결 정보를 직접 정의했으므로, 여기서는 임시로 재정의합니다.
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306; 

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
$teams_list = [];
$error_message = null;

if ($conn->connect_error) {
    $error_message = "데이터베이스 연결 실패: " . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");

    // 2. teams 테이블에서 팀 이름 목록을 가져옵니다.
    $sql = "SELECT team_id, team_name FROM teams ORDER BY team_name ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $teams_list[] = $row;
        }
        $result->free();
    } else {
        $error_message = "팀 목록을 불러올 수 없습니다. teams 테이블을 확인하세요.";
    }

    $conn->close();
}

// 3. 헤더 파일 포함
require_once 'header.php'; 
?>

<div class="signup-page-container">
    
    <div class="signup-modal-card">
        
        <div class="modal-header">
            <h1 class="modal-title">회원가입</h1>
        </div>
                
        <form action="process_signup.php" method="POST" class="signup-form">
            
            <?php if ($error_message): ?>
                <p style="color: red; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <label for="name">이름</label>
            <input type="text" id="name" name="name" placeholder="이름을 입력하세요" required>
            
            <label for="email">이메일</label>
            <input type="email" id="email" name="email" placeholder="example@email.com" required>

            <label for="bdate">생년월일</label>
            <input type="date" id="bdate" name="bdate" required>

            <label for="phone">휴대폰 번호</label>
            <input type="tel" id="phone" name="phone" placeholder="010-0000-0000" required>
            
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" placeholder="비밀번호 (4자 이상)" minlength="4" required>
            
            
            <button type="submit" class="submit-btn">가입하기</button>
            
        </form>
    </div>
</div>

<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>