<?php
// BDAProject/signup.php
// author: Jwa Yeonjoo

session_start();
$page_title = "signup";

// 1. DB 연결 설정 불러오기 (config/config.php 사용)
require_once 'config/config.php';

$teams_list = [];
$db_team_error = null; // 팀 목록 로드 시 발생한 DB 오류 메시지

// config.php에서 $conn이 생성되었는지 확인
if (!isset($conn) || $conn->connect_error) {
    $db_team_error = "데이터베이스 연결 실패: " . ($conn->connect_error ?? 'Connection object not found');
} else {
    // 2. teams 테이블에서 팀 이름 목록을 가져옵니다.

    $sql = "SELECT team_id, team_name FROM teams ORDER BY team_name ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $teams_list[] = $row;
        }
        $result->free();
    } else {
        // 팀 데이터가 없거나 쿼리 실패 시
        if ($conn->error) {
             $db_team_error = "팀 목록 조회 오류: " . $conn->error;
        } else if (count($teams_list) === 0) {
             // DB 연결은 성공했으나 데이터가 없는 경우 (에러는 아님)
             // 필요하다면 메시지 설정
        }
    }
    
}


// === 3. 회원가입 실패 시 전달된 오류 메시지 처리 (기존 로직 유지) ===
$error_code = $_GET['error'] ?? null;
$submission_error = ""; // 폼 제출 실패로 인한 오류 메시지

if ($error_code) {
    switch ($error_code) {
        case 'missing_fields':
            $submission_error = "모든 필수 항목(이름, 이메일, 비밀번호, 생년월일, 연락처)을 입력해 주세요.";
            break;
        case 'password_short':
            $submission_error = "비밀번호는 최소 4자 이상이어야 합니다.";
            break;
        case 'email_exists':
            $submission_error = "이미 사용 중인 이메일 주소입니다. 다른 이메일을 사용해 주세요.";
            break;
        case 'phone_invalid':
            $submission_error = "유효한 전화번호 형식이 아닙니다. 올바른 형식(예: 010-1234-5678)으로 입력해 주세요.";
            break;
        case 'db_connect_failed':
            $submission_error = "서버 오류: 데이터베이스 연결에 실패했습니다. 잠시 후 다시 시도해 주세요.";
            break;
        case 'signup_failed':
            $submission_error = "회원가입 처리 중 알 수 없는 오류가 발생했습니다. 입력 정보를 확인해 주세요.";
            break;
        case 'exception':
            $submission_error = "시스템 오류가 발생했습니다. 문제가 지속되면 관리자에게 문의하세요.";
            break;
        default:
            $submission_error = "회원가입 처리 중 알 수 없는 오류가 발생했습니다.";
            break;
    }
}
// === 끝 ===


// 4. 헤더 파일 포함
require_once 'header.php'; 
?>

<!-- ============================================== -->
<!-- 🚩 Custom Alert Modal HTML 구조 -->
<!-- ============================================== -->
<div id="custom-alert-modal" style="
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0; 
    top: 0; 
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.4);
    font-family: sans-serif;
">
    <div style="
        background-color: #fff;
        margin: 15% auto; 
        padding: 25px;
        border: 1px solid #c00;
        width: 80%;
        max-width: 400px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    ">
        <h3 style="color: #c00; margin-top: 0; border-bottom: 2px solid #c00; padding-bottom: 10px;">⚠️ 오류 발생</h3>
        <p id="alert-modal-message" style="color: #333; font-size: 1.1em; margin: 15px 0;"></p>
        <button onclick="document.getElementById('custom-alert-modal').style.display='none';" style="
            background-color: #c00;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
            margin-top: 10px;
        ">확인</button>
        <div style="clear: both;"></div>
    </div>
</div>
<!-- ============================================== -->


<div class="signup-page-container">
    
    <div class="signup-modal-card">
        
        <div class="modal-header">
            <h1 class="modal-title">Sign Up</h1>
        </div>
        
        <form action="process_signup.php" method="POST" class="signup-form">
            
            <!-- 팀 목록 로드 DB 오류 메시지 -->
            <?php if ($db_team_error): ?>
                <p style="color: red; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($db_team_error); ?></p>
            <?php endif; ?>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="example@email.com" required>

            <label for="bdate">Date of Birth</label>
            <input type="date" id="bdate" name="bdate" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="010-0000-0000" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password (4 characters or more)" minlength="4" required>
            
            
            <button type="submit" class="submit-btn">Sign Up</button>
            
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- 🚩 JavaScript 로직 -->
<!-- ============================================== -->
<script>
    // PHP 변수를 직접 문자열로 인코딩하여 JS에 전달
    const signupErrorMessage = "<?php echo htmlspecialchars($submission_error, ENT_QUOTES, 'UTF-8'); ?>";

    /**
     * 커스텀 모달을 화면에 표시하는 함수
     * @param {string} message - 표시할 오류 메시지
     */
    function showCustomErrorModal(message) {
        const modal = document.getElementById('custom-alert-modal');
        const messageElement = document.getElementById('alert-modal-message');
        
        if (modal && messageElement) {
            messageElement.textContent = message;
            modal.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 메시지가 비어있지 않으면 바로 모달 표시
        if (signupErrorMessage.length > 0) {
            showCustomErrorModal(signupErrorMessage);
        }
    });
</script>


<?php
// 5. 푸터 파일 포함
require_once 'footer.php';
?>