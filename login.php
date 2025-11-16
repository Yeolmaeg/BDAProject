<?php
session_start();

$page_title = "login";
// NOTE: config.php가 header.php보다 먼저 필요할 경우 위치를 조정하세요.
// 이 예시에서는 로그인 처리 로직 내에서만 사용되므로 그대로 둡니다.

// --- 로그인 처리 PHP 로직 (변경 없음) ---
$error_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/config.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = "이메일과 비밀번호를 입력해주세요.";
    } else {
        try {
            // 데이터베이스 연결 및 로그인 로직 (생략: 기존 코드와 동일)
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);
            
            if ($mysqli->connect_errno) {
                throw new Exception("데이터베이스 연결 실패: " . $mysqli->connect_error);
            }
            
            $mysqli->set_charset('utf8mb4');
            
            $stmt = $mysqli->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $email;
                
                $update_stmt = $mysqli->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                $stmt->close();
                $mysqli->close();
                
                header("Location: public/index.php");
                exit();
            } else {
                $error_message = "이메일 또는 비밀번호가 올바르지 않습니다.";
            }
            
            $stmt->close();
            $mysqli->close();
            
        } catch (Exception $e) {
            $error_message = "로그인 처리 중 오류가 발생했습니다.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}

require_once 'header.php';
?>
<style>

    body {
        font-family: 'KBO Dia Gothic', sans-serif;
        background: linear-gradient(135deg, #8AB4F8 0%, #001F63 100%);
        min-height: 100vh;
        position: relative;
        overflow: hidden !important;
        padding: 0 !important;
        display: block !important;
    }

    .header, .nav-bar {
        width: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }
    .login-page {
        width: 100%;
        max-width: 450px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        padding: 50px 40px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }

    /* KBO 폰트 적용 */
    @font-face {
        font-family: 'KBO Dia Gothic';
        src: url('/BDAProject/public/KBO Dia Gothic_light.ttf') format('truetype');
        font-weight: 300;
        font-style: normal;
    }
    
    @font-face {
        font-family: 'KBO Dia Gothic';
        src: url('BDAProject/public/KBO Dia Gothic_medium.ttf') format('truetype');
        font-weight: 500;
        font-style: normal;
    }
    
    @font-face {
        font-family: 'KBO Dia Gothic';
        src: url('BDAProject/public/KBO Dia Gothic_bold.ttf') format('truetype');
        font-weight: 700;
        font-style: normal;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* 배경 장식 요소 */
    body::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 810"><path fill="%23ffffff" fill-opacity="0.1" d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,250.7C1248,256,1344,288,1392,304L1440,320L1440,810L1392,810C1344,810,1248,810,1152,810C1056,810,960,810,864,810C768,810,672,810,576,810C480,810,384,810,288,810C192,810,96,810,48,810L0,810Z"></path></svg>') no-repeat;
        background-size: cover;
        opacity: 0.3;
        transform: rotate(-5deg);
    }

    .login-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    #login-heading {
        font-family: 'KBO Dia Gothic', sans-serif;
        font-size: 52px;
        font-weight: 700;
        color: #000000;
        margin-bottom: 5px;
    }
    
    .subtitle {
        font-size: 14px;
        color: #757575;
        font-weight: 300;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 10px;
        position: relative;
    }
    
    .form-control {
        width: 100%;
        padding: 20px 20px;
        font-size: 16px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        background: #f8f8f8;
        transition: all 0.3s ease;
        font-family: 'KBO Dia Gothic', sans-serif;
        font-weight: 500;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-label {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #999;
        font-weight: 500;
        pointer-events: none;
        transition: all 0.3s ease;
        padding: 0 5px;
    }
    
    .form-control:focus + .form-label,
    .form-control:not(:placeholder-shown) + .form-label {
        top: 0;
        font-size: 12px;
        color: #667eea;
        background: white;
    }
    
    .password-wrapper {
        position: relative;
    }
    
    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        color: #999;
        transition: color 0.3s ease;
    }
    
    .toggle-password:hover {
        color: #667eea;
    }
    
    .toggle-password svg {
        width: 20px;
        height: 20px;
    }
    
    .btn-primary {
        width: 100%;
        padding: 20px;
        font-size: 20px;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #000000 0%, #000000 100%);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
        font-family: 'KBO Dia Gothic', sans-serif;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    .signup-link {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .signup-link a {
        color: #616161;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .signup-link a:hover {
        color: #001F63;
        text-decoration: underline;
    }
    
    .error-message {
        background: #fee;
        color: #c33;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .error-message::before {
        content: '⚠';
        font-size: 18px;
    }

       .footer {
        position: fixed !important;
        bottom: 0 !important;  
        left: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;

        text-align: center !important;
        padding: 20px !important;
        color: #777 !important;
        height: 50px;
        background-color: white;
    }
    
    /* 로딩 상태 */
    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* 반응형 디자인 */
    @media (max-width: 480px) {
        .login-page {
            margin: 20px auto; /* 중앙 정렬 유지 */
            padding: 40px 30px;
        }
        
        #login-heading {
            font-size: 28px;
        }
    }
    
    /* 애니메이션 */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .login-page {
        animation: fadeIn 0.6s ease;
    }

</style>

<main class="login-page">
        <div class="login-header">
            <h1 id="login-heading">로그인</h1>
            <p class="subtitle">Best way to look up for baseball records</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate onsubmit="return validateForm()">
            <div class="form-group">
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    required
                    autocomplete="email"
                    placeholder=" "
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                />
                <label for="email" class="form-label">EMAIL ADDRESS</label>
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="current-password"
                        placeholder=" "
                    />
                    <label for="password" class="form-label">PASSWORD</label>
                    <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="비밀번호 표시">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-off-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="submit-btn">
                다음
            </button>
        </form>

        <div class="signup-link">
            <a href="signup.php">아직 회원이 아니신가요?</a>
        </div>
    </main>

    <script>
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

        function validateForm() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submit-btn');
            
            if (!email || !password) {
                alert('이메일과 비밀번호를 모두 입력해주세요.');
                return false;
            }
            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('올바른 이메일 형식을 입력해주세요.');
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = '로그인 중...';
            
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const form = this.closest('form');
                        if (form) {
                            form.dispatchEvent(new Event('submit', { cancelable: true }));
                        }
                    }
                });
            });
        });
    </script>
<?php
    require_once 'footer.php';
?>
