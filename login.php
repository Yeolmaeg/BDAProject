<?php
session_start();

// 페이지 제목 설정
$page_title = "login";

// 에러 메시지 초기화
$error_message = "";

// 이미 로그인된 경우 메인 페이지로 리다이렉트
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// POST 요청 처리 (로그인 시도)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // require_once 'config/config.php'; // 기존 config 파일 로드는 제거됨
    
    // 입력값 가져오기
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // 입력값 검증
    if (empty($email) || empty($password)) {
        $error_message = "Please enter your email and password.";
    } else {
        try {
            // 1. DB 연결 설정 (signup.php에서 가져옴)
            $DB_HOST = '127.0.0.1'; 
            $DB_NAME = 'team04';    
            $DB_USER = 'root';      
            $DB_PASS = '';          
            $DB_PORT = 3306;       
            
            // 2. 데이터베이스 연결
            $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
            
            // 3. 오류 처리 및 인코딩 설정
            if ($mysqli->connect_error) {
                throw new Exception("Database connection failed: " . $mysqli->connect_error);
            }
            
            $mysqli->set_charset('utf8mb4');
            
            // 사용자 정보 조회
            $stmt = $mysqli->prepare("SELECT user_id, user_name, user_pass, user_email FROM users WHERE user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            
            if ($user && password_verify($password, $user['user_pass'])) {
                // 로그인 성공
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['user_name'];      
                $_SESSION['email'] = $user['user_email'];        
                
                $stmt->close();
                $mysqli->close();
                
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
            
            $stmt->close();
            $mysqli->close();
            
        } catch (Exception $e) {
            $error_message = "An error occurred while logging in.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}

require_once 'header.php';
?>

<div class="wrapper">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="login-page">
            <div class="login-header">
                <h1 id="login-heading">LOGIN</h1>
                <p class="subtitle">Best way to look up for baseball records</p>
            </div>
            <div class="already-logged-in-message" style="text-align: center; padding: 40px; margin-top: 20px;">
                <h2 style="font-size: 24px; color: var(--kbo-dark-blue); margin-bottom: 15px;">You are already logged in.
                </h2>
                <p style="margin-top: 15px; color: #555;">Welcome back!</p>
                <a href="index.php" class="btn-primary"
                    style="display: inline-block; width: 100%; margin-top: 30px; text-decoration: none; padding: 15px;">Back
                    to the main page</a>
            </div>
        </div>
    <?php else: ?>
        <div class="login-page">
            <div class="login-header">
                <h1 id="login-heading">LOGIN</h1>
                <p class="subtitle">Best way to look up for baseball records</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"
                    style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email"
                        placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                    <label for="email" class="form-label">EMAIL</label>
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required
                            autocomplete="current-password" placeholder=" "  />
                        <label for="password" class="form-label">PASSWORD</label>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="비밀번호 표시">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="submit-btn">
                    CONTINUE
                </button>
            </form>

            <div class="signup-link">
                <a href="signup.php">Not a member yet?</a>
            </div>
        </div>

       <script>
            // 비밀번호 표시/숨김 토글
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eye-icon');
                const eyeOffIcon = document.getElementById('eye-off-icon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                }
            }

            // 폼 제출 시 버튼 비활성화 (버튼 클릭 및 Enter 키 모두에 적용)
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('form');
                const submitBtn = document.getElementById('submit-btn');

                if (form && submitBtn) {
                    // 1. 폼 제출 시 버튼 비활성화 로직은 그대로 유지
                    form.addEventListener('submit', function () {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Logging in...';
                    });
                }

            });
        </script>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
