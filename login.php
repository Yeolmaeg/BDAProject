<?php
session_start();

$page_title = "login";

// --- 로그인 처리 PHP 로직 (변경 없음) ---
$error_message = "";
if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/config.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = "Please Enter your email and password.";
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
                $error_message = "Your email or password is not valid.";
            }
            
            $stmt->close();
            $mysqli->close();
            
        } catch (Exception $e) {
            $error_message = "Error occurred while logging in.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}

require_once 'header.php';
?>

<?php if (isset($_SESSION['user_id'])): ?>
    
    <div class="login-page">
        <div class="login-header">
            <h1 id="login-heading">LOGIN</h1>
            <p class="subtitle">Best way to look up for baseball records</p>
        </div>
        <div class="already-logged-in-message" style="text-align: center; padding: 40px; margin-top: 20px;">
            <h2 style="font-size: 24px; color: var(--kbo-dark-blue); margin-bottom: 15px;">You are already logged in.</h2>
            <p style="margin-top: 15px; color: #555;">Please try after logging in.</p>
            <a href="public/index.php" class="btn-primary" style="display: inline-block; width: 100%; margin-top: 30px; text-decoration: none; padding: 15px;">Back to the main page</a>
        </div>
    </div>

<?php else: ?>

<div class="login-page">
        <div class="login-header">
            <h1 id="login-heading">LOGIN</h1>
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
                <label for="email" class="form-label">EMAIL</label>
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
                CONTINUE
            </button>
        </form>

        <div class="signup-link">
            <a href="signup.php">Not a member yet?</a>
        </div>
    </div>

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
                alert('Please enter both your email and password');
                return false;
            }
            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Please enter valid form of email.');
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
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
<?php endif; ?>
<?php
    require_once 'footer.php';
?>
