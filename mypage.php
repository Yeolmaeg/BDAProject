<?php
// author: Sumin Son

session_start();

// 로그인 안 되어 있으면 로그인 페이지로 보냄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = "My Page";

require_once __DIR__ . '/config/config.php';

// =======================
// 0. 팀 로고 매핑
// =======================
function getTeamLogoSrc($team_name) {
    $key = strtolower(trim($team_name));

    $map = [
        'kia tigers'      => 'kia',
        'kt wiz'          => 'kt',
        'hanwha eagles'   => 'hanwha',
        'doosan bears'    => 'doosan',
        'samsung lions'   => 'samsung',
        'ssg landers'     => 'ssg',
        'kiwoom heroes'   => 'kiwoom',
        'nc dinos'        => 'nc',
        'lotte giants'    => 'lotte',
        'lg twins'        => 'lg',
    ];

    if (!isset($map[$key])) {
        return null;
    }

    $code = $map[$key];
    return "logos/{$code}.png";
}

$user          = null;
$error_message = null;
$user_id       = $_SESSION['user_id'];

// =======================
// 1. 회원 탈퇴 처리
// =======================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'delete_account'
) {
    // config.php에서 만든 $conn 사용
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if ($stmt === false) {
        $error_message = "계정 삭제 중 오류가 발생했습니다. 다시 시도해 주세요. (prepare 실패)";
    } else {
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();

            // 세션 정리 후 메인으로 이동
            session_unset();
            session_destroy();

            header("Location: index.php");
            exit;
        } else {
            $error_message = "계정 삭제 중 오류가 발생했습니다. 다시 시도해 주세요.";
            $stmt->close();
        }
    }
}

// =======================
// 2. 현재 로그인된 유저 정보 + 응원팀 정보 가져오기
// =======================
$conn->set_charset("utf8mb4");

$sql = "
    SELECT
        u.user_name,
        u.user_bdate,
        u.user_phone,
        u.user_email,
        u.favorite_team_id,
        t.team_name,
        t.city,
        t.founded_year
    FROM users u
    LEFT JOIN teams t
      ON u.favorite_team_id = t.team_id
    WHERE u.user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $error_message = "사용자 정보를 불러오는 중 오류가 발생했습니다.";
} else {
    $stmt->bind_param('i', $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $user = $row;
        } else {
            $error_message = "사용자 정보를 찾을 수 없습니다.";
        }
        $result && $result->free();
    } else {
        $error_message = "사용자 정보를 불러오는 중 오류가 발생했습니다.";
    }
    $stmt->close();
}


include 'header.php';
?>

<div class="card-box">
    <h1 class="page-title">My Page</h1>
    <p class="page-description">
        This page shows your account details and favorite team.
        <br>
        You can also delete your account if you no longer wish to use this service.
    </p>

    <?php if (!empty($error_message)): ?>
        <div class="mypage-error-box">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
    <div class="mypage-layout">

        <!-- 1. 계정 정보 섹션 -->
        <section class="mypage-section">
            <h2 class="section-title">Account Information</h2>
            <table class="mypage-table">
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>
                        <?php echo $user['user_phone']
                            ? htmlspecialchars($user['user_phone'])
                            : '-'; ?>
                    </td>
                </tr>
                <tr>
                    <th>Birth Date</th>
                    <td>
                        <?php echo $user['user_bdate']
                            ? htmlspecialchars($user['user_bdate'])
                            : '-'; ?>
                    </td>
                </tr>
            </table>
        </section>

        <!-- 2. 응원 팀 섹션 -->
        <section class="mypage-section">
            <h2 class="section-title">Favorite Team</h2>

            <?php if (!empty($user['favorite_team_id']) && !empty($user['team_name'])): ?>
                <div class="mypage-fav-team">
                    <?php
                    $logo = getTeamLogoSrc($user['team_name']);
                    if ($logo):
                    ?>
                        <img
                            src="<?php echo $logo; ?>"
                            alt="<?php echo htmlspecialchars($user['team_name']); ?> logo"
                            class="team-logo"
                        >
                    <?php endif; ?>

                    <div class="mypage-fav-team-text">
                        <div class="fav-team-name">
                            <?php echo htmlspecialchars($user['team_name']); ?>
                        </div>
                        <div class="fav-team-meta">
                            <?php if (!empty($user['city'])): ?>
                                <?php echo htmlspecialchars($user['city']); ?>
                            <?php endif; ?>
                            <?php if (!empty($user['city']) && !empty($user['founded_year'])): ?>
                                ·
                            <?php endif; ?>
                            <?php if (!empty($user['founded_year'])): ?>
                                Founded <?php echo htmlspecialchars($user['founded_year']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>You have not selected a favorite team yet.</p>
            <?php endif; ?>
        </section>

        <!-- 3. 회원 탈퇴 섹션 -->
        <section class="mypage-section mypage-danger">
            <h2 class="section-title">Delete Account</h2>
            <p class="danger-text">
                Once you delete your account, your user information and preferences will be permanently removed.
                <br>
                This action cannot be undone.
            </p>

            <form method="post" id="delete-account-form">
                <input type="hidden" name="action" value="delete_account">
                <button type="submit" class="btn-danger">
                    Delete Account
                </button>
            </form>
        </section>

    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteForm = document.getElementById('delete-account-form');
    if (!deleteForm) return;

    deleteForm.addEventListener('submit', function (e) {
        const ok = confirm(
            "Are you sure you want to delete your account?\n\n"
            + "If you continue, your account information and favorite team settings will be permanently removed.\n\n"
            + "This action cannot be undone."
        );
        if (!ok) {
            e.preventDefault();
        }
    });
});
</script>

<?php
require_once 'footer.php';
?>
