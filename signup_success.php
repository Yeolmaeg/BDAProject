<?php
// BDAProject/signup_success.php

session_start();
$page_title = "회원가입 완료";

// 헤더와 푸터 파일 포함
require_once 'header.php'; 
?>

<div class="signup-page-container">
    <div class="signup-success-card">
        <h2>Welcome to membership!</h2>
        <p class="success-message">Your registration has been successfully completed.</p>
        <p class="sub-message">Now, log in to enjoy the KBO statistics service.</p>
        
        <button 
            class="go-to-home-btn" 
            onclick="location.href='index.php'">
            Go to Home Page
        </button>
    </div>
</div>

<?php
require_once 'footer.php';
?>