<?php
// 페이지 제목 설정
$page_title = "문의사항";

// 설정 파일 포함
require_once __DIR__ . '/config/config.php';

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인 체크 로직
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('로그인이 필요한 서비스입니다.');
        location.href = 'login.php'; 
    </script>";
    exit;
}

// 에러 및 성공 메시지 변수
$error_message = '';
$success_message = '';

// 데이터베이스 연결 함수
function getDBConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($mysqli->connect_errno) {
        error_log("Database connection failed: " . $mysqli->connect_error);
        return false;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

// 현재 로그인한 사용자 정보 가져오기 함수
function getCurrentUserInfo($user_id) {
    $mysqli = getDBConnection();
    if (!$mysqli) return false;

    // users 테이블에서 이름(name)과 이메일(email)을 가져옴
    $stmt = $mysqli->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $mysqli->close();
    
    return $user;
}

// CSRF 토큰 생성
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 문의사항 저장 함수
function saveInquiry($user_id, $email, $name, $inquiry_type, $message) {
    $mysqli = getDBConnection();
    if (!$mysqli) return false;
    
    $stmt = $mysqli->prepare("INSERT INTO inquiries (user_id, email, name, inquiry_type, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    if (!$stmt) {
        $mysqli->close();
        return false;
    }
    
    $stmt->bind_param("issss", $user_id, $email, $name, $inquiry_type, $message);
    $success = $stmt->execute();
    $inquiry_id = $success ? $mysqli->insert_id : false;
    
    $stmt->close();
    $mysqli->close();
    
    return $inquiry_id;
}

// 이메일 전송 함수
function sendConfirmationEmail($email, $name, $inquiry_id, $inquiry_type, $message) {
    $to = $email;
    $subject = "=?UTF-8?B?" . base64_encode("문의사항 접수 확인 - #" . $inquiry_id) . "?=";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: support@team04.com" . "\r\n"; // 보내는 사람 주소 확인 필요
    
    $inquiry_types = [
        'technical' => '오류 신고',
        'wrongInfo' => '잘못된 정보 정정 요청',
        'other' => '기타'
    ];
    
    $type_text = $inquiry_types[$inquiry_type] ?? '기타';
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body>
        <h2>문의사항이 접수되었습니다</h2>
        <p>문의번호: <strong>#$inquiry_id</strong></p>
        <p>안녕하세요, <strong>$name</strong>님</p>
        <p><strong>문의 종류:</strong> $type_text</p>
        <p><strong>문의 내용:</strong></p>
        <div style='background: #f8f9fa; padding: 15px;'>
            " . nl2br(htmlspecialchars($message)) . "
        </div>
    </body>
    </html>
    ";
    
    return @mail($to, $subject, $body, $headers);
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "보안 검증에 실패했습니다. 다시 시도해주세요.";
    } else {
        $user_id = $_SESSION['user_id'];
        
        // DB에서 사용자 정보 자동 조회
        $userInfo = getCurrentUserInfo($user_id);
        
        if (!$userInfo) {
            $error_message = "사용자 정보를 불러올 수 없습니다. 다시 로그인해주세요.";
        } else {
            $name = $userInfo['name'];
            $email = $userInfo['email'];
            $inquiry_type = isset($_POST['inquiry-type']) ? trim($_POST['inquiry-type']) : '';
            $message = isset($_POST['inquiry-message']) ? trim($_POST['inquiry-message']) : '';
            
            $valid_types = ['technical', 'wrongInfo', 'other'];
            
            if (empty($inquiry_type) || !in_array($inquiry_type, $valid_types)) {
                $error_message = "문의 종류를 선택해주세요.";
            } elseif (empty($message) || mb_strlen($message) < 10) {
                $error_message = "문의 내용을 10자 이상 입력해주세요.";
            } else {
                $inquiry_id = saveInquiry($user_id, $email, $name, $inquiry_type, $message);
                
                if ($inquiry_id) {
                    sendConfirmationEmail($email, $name, $inquiry_id, $inquiry_type, $message);
                    $success_message = "문의사항이 성공적으로 접수되었습니다.\n답변은 {$email}로 전송됩니다.";
                    $_POST = array();
                } else {
                    $error_message = "오류가 발생했습니다. 다시 시도해주세요.";
                }
            }
        }
    }
}

require_once 'header.php';
?>

<div class="loading-overlay" id="loadingOverlay">
    <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
        <div class="spinner" style="margin: 0 auto 20px;"></div>
        <p>전송 중입니다...</p>
    </div>
</div>

<div class="report-page">
    
    <?php if ($error_message): ?>
        <div class="alert alert-error" id="errorAlert"><?php echo nl2br(htmlspecialchars($error_message)); ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success" id="successAlert"><?php echo nl2br(htmlspecialchars($success_message)); ?></div>
    <?php endif; ?>
    
    <form class="inquiry-form" action="" method="post" id="inquiryForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <h1 class="report">문의사항</h1>
        
        <div class="form-group">
            <label for="inquiry-type" class="div">문의 종류</label>
            <select id="inquiry-type" name="inquiry-type" class="select-occupation" required>
                <option value="" disabled selected>선택</option>
                <option value="technical">오류 신고</option>
                <option value="wrongInfo">잘못된 정보 정정 요청</option>
                <option value="other">기타</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="inquiry-message" class="div">
                문의 내용 
                <span style="color: #959595ff; font-size: 11px; font-weight: 400; margin-left: 3px;">
                    문의 내용에 대한 답변은 이메일로 발송됩니다.
                </span>
            </label>
            <textarea id="inquiry-message" name="inquiry-message" class="view-2" placeholder="문의 내용을 입력해주세요 (최소 10자)" required rows="10"></textarea>
            <div style="text-align: right; font-size: 12px; color: #888; margin-top: 5px;">
                <span id="charCount">0</span> / 5000
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="rectangle-2">전송</button>
        </div>
    </form>
</div>

<script>
// 글자수 세기 및 폼 제출 제어
const textarea = document.getElementById('inquiry-message');
textarea.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

document.getElementById('inquiryForm').addEventListener('submit', function(e) {
    if (textarea.value.trim().length < 10) {
        e.preventDefault();
        alert('문의 내용을 10자 이상 입력해주세요.');
        textarea.focus();
    } else {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
});

// 알림 메시지 자동 숨김
setTimeout(() => {
    const success = document.getElementById('successAlert');
    if(success) success.style.display = 'none';
}, 5000);
</script>

<?php require_once 'footer.php'; ?>
