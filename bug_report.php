<?php
// 페이지 제목 설정
$page_title = "bug_report";

// DB 연결 설정 
$DB_HOST = '127.0.0.1'; //
$DB_NAME = 'team04';    //
$DB_USER = 'root';      //
$DB_PASS = '';          //
$DB_PORT = 3306;        //


// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인 체크 로직
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You need to log in before using this service.');
        location.href = 'login.php'; 
    </script>";
    exit;
}

// 에러 및 성공 메시지 변수
$error_message = '';
$success_message = '';

// 데이터베이스 연결 함수
function getDBConnection() {
    // [수정] 전역 변수를 함수 내에서 사용
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;

    // @new mysqli를 사용하여 연결
    $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT); 
    
    // connect_error를 사용하여 연결 오류 확인
    if ($mysqli->connect_error) { 
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

    // users 테이블에서 이름(user_name)과 이메일(user_email)을 가져옴
    $stmt = $mysqli->prepare("SELECT user_name, user_email FROM users WHERE user_id = ?");
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
        error_log("Prepare failed: " . $mysqli->error);
        $mysqli->close();
        return false;
    }
    
    $stmt->bind_param("issss", $user_id, $email, $name, $inquiry_type, $message);
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("Execute failed: " . $stmt->error);
    }
    
    $inquiry_id = $success ? $mysqli->insert_id : false;
    
    $stmt->close();
    $mysqli->close();
    
    return $inquiry_id;
}

// 이메일 전송 함수
function sendConfirmationEmail($email, $name, $inquiry_id, $inquiry_type, $message) {
    $to = $email;
    $subject = "=?UTF-8?B?" . base64_encode("Inquiry Received - #" . $inquiry_id) . "?=";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: support@team04.com" . "\r\n";
    
    $inquiry_types = [
        'technical' => 'Bug Report',
        'wrongInfo' => 'Request Data Correction',
        'other' => 'Others'
    ];
    
    $type_text = $inquiry_types[$inquiry_type] ?? 'Others';
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body>
        <h2>Your Inquiry has been successfully received.</h2>
        <p>Inquiry No: <strong>#$inquiry_id</strong></p>
        <p>Hi, <strong>$name</strong></p>
        <p><strong>Inquiry Type:</strong> $type_text</p>
        <p><strong>Details:</strong></p>
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
    // CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // 폼 데이터 받기
        $user_id = $_SESSION['user_id'] ?? null; // 로그인한 경우 user_id
        
        // 사용자 정보 가져오기 (로그인한 경우)
        $user_info = null;
        if ($user_id) {
            $user_info = getCurrentUserInfo($user_id);
        }
        
        $name = $user_info['user_name'] ?? 'Guest';
        $email = $user_info['user_email'] ?? 'guest@unknown.com';
        $inquiry_type = $_POST['inquiry-type'] ?? '';
        $message = $_POST['inquiry-message'] ?? '';
        
        // 데이터 유효성 검사
        if (empty($inquiry_type)) {
            $error_message = "Please select an inquiry type.";
        } elseif (empty($message) || strlen($message) < 10) {
            $error_message = "Please enter at least 10 characters for the inquiry.";
        } elseif (strlen($message) > 5000) {
            $error_message = "Message is too long. Maximum 5000 characters.";
        } else {
            // DB에 문의 내용 저장
            $inquiry_id = saveInquiry($user_id, $email, $name, $inquiry_type, $message);
            
            if ($inquiry_id) {
                // 이메일 전송 (선택사항 - 필요시 주석 해제)
                // sendConfirmationEmail($email, $name, $inquiry_id, $inquiry_type, $message);
                
                $success_message = "Your inquiry has been successfully sent. (Inquiry #$inquiry_id)";
                // 성공 후 폼 데이터 초기화
                $_POST = [];
            } else {
                $error_message = "Failed to submit inquiry. Please try again.";
            }
        }
    }
}

require_once 'header.php';
?>

<div class="loading-overlay" id="loadingOverlay">
    <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
        <div class="spinner" style="margin: 0 auto 20px;"></div>
        <p>Sending...</p>
    </div>
</div>

<div class="report-page bug-report-scope">
    
    <?php if ($error_message): ?>
        <div class="alert alert-error" id="errorAlert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success" id="successAlert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <form class="inquiry-form" action="" method="post" id="inquiryForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <h1 class="report">INQUIRY</h1>
        
        <div class="form-group">
            <label for="inquiry-type" class="div">Inquiry Type</label>
            <select id="inquiry-type" name="inquiry-type" class="select-occupation" required>
                <option value="" disabled selected>Select</option>
                <option value="technical">Bug Report</option>
                <option value="wrongInfo">Request Data Correction</option>
                <option value="other">Others</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="inquiry-message" class="div">
                Details 
                <span style="color: #959595ff; font-size: 13px; font-weight: 400; margin-left: 3px;">
                    The response to your inquiry will be sent via email.
                </span>
            </label>
            <textarea id="inquiry-message" name="inquiry-message" class="view-2" placeholder="Please provide the details (At least 10 characters)" required rows="10"></textarea>
            <div style="text-align: right; font-size: 12px; color: #888; margin-top: 5px;">
                <span id="charCount">0</span> / 5000
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="rectangle-2">SEND</button>
        </div>
    </form>
</div>

<script>
// 글자수 세기 및 폼 제출 제어
const textarea = document.getElementById('inquiry-message');
textarea.addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('charCount').textContent = count;
    
    // 5000자 초과 방지
    if (count > 5000) {
        this.value = this.value.substring(0, 5000);
        document.getElementById('charCount').textContent = '5000';
    }
});

document.getElementById('inquiryForm').addEventListener('submit', function(e) {
    const message = textarea.value.trim();
    const inquiryType = document.getElementById('inquiry-type').value;
    
    if (!inquiryType) {
        e.preventDefault();
        alert('Please select an inquiry type');
        return;
    }
    
    if (message.length < 10) {
        e.preventDefault();
        alert('Please enter at least 10 characters');
        textarea.focus();
        return;
    }
    
    document.getElementById('loadingOverlay').style.display = 'flex';
});

// 알림 메시지 자동 숨김
setTimeout(() => {
    const success = document.getElementById('successAlert');
    if(success) success.style.display = 'none';
}, 5000);

setTimeout(() => {
    const error = document.getElementById('errorAlert');
    if(error) error.style.display = 'none';
}, 5000);
</script>

<?php require_once 'footer.php'; ?>
