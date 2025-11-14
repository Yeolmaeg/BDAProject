<?php
// BDAProject/login.php

// 1. 페이지 제목 설정 (header.php의 <title> 태그에 사용됨)
$page_title = "로그인 및 시작";

// 2. 헤더 파일 포함
require_once 'header.php'; 

// 3. 페이지의 본문 내용
?>
    <div class="search-container">
            <input type="search" placeholder="Search">
    </div>

        <div class="card-box welcome-card">
            <h2>환영합니다!</h2>
            <p>회원가입하고 응원하는 팀의 최근 경기와 날씨 분석을 확인하세요</p>
            <div style="margin-top: 30px;">
                <button class="signup-btn" onclick="location.href='signup.php'">회원가입</button>
                <button class="login-btn" onclick="location.href='login_form.php'">로그인</button>
            </div>
        </div>
<?php
// 4. 푸터 파일 포함
require_once 'footer.php';
?>