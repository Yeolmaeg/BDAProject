<?php
// BDAProject/logout.php

session_start(); // 현재 세션을 시작합니다.

// 1. 모든 세션 변수를 unset합니다.
$_SESSION = array();

// 2. 세션 쿠키 자체를 파괴합니다 (브라우저에서 세션 ID 삭제).
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. 세션 자체를 완전히 파괴합니다.
session_destroy();

// 4. 로그아웃 후 로그인 페이지로 리다이렉트합니다.
header("Location: login.php?logged_out=true"); 
exit();
?>