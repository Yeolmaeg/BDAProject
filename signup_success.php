<?php
// BDAProject/signup_success.php

session_start();
$page_title = "회원가입 완료";

// 헤더와 푸터 파일 포함
require_once 'header.php'; 
?>

<div class="signup-page-container">
    <div class="signup-success-card">
        <h2>🎉 회원가입을 환영합니다!</h2>
        <p class="success-message">회원님의 가입이 성공적으로 완료되었습니다.</p>
        <p class="sub-message">이제 로그인하여 KBO 통계 서비스를 이용해 보세요.</p>
        
        <button 
            class="go-to-home-btn" 
            onclick="location.href='index.php'">
            메인 페이지로
        </button>
    </div>
</div>

<?php
require_once 'footer.php';
?>