// BDAProject/public/bookmark.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. 모든 북마크 아이콘 요소를 가져옵니다.
    const bookmarkIcons = document.querySelectorAll('.bookmark-icon');

    // 2. 각 아이콘에 클릭 이벤트 리스너를 추가합니다.
    bookmarkIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            // 클릭된 아이콘에서 team_id를 가져옵니다.
            const teamId = this.dataset.teamId;

            // 3. 서버에 보낼 데이터 객체를 준비합니다.
            const data = {
                team_id: teamId
            };

            // 4. AJAX 요청을 통해 서버(PHP)에 북마크 상태 토글을 요청합니다.
            fetch('toggle_bookmark.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // 5. 서버 응답이 성공적이면 아이콘의 클래스를 토글하여 색상을 변경합니다.
                    this.classList.toggle('is-bookmarked');
                    console.log('북마크 상태 변경 성공:', result.message);
                    
                    // (선택 사항) 대시보드 업데이트 로직 등을 여기에 추가할 수 있습니다.
                } else {
                    alert('북마크 변경 실패: ' + result.message);
                }
            })
            .catch(error => {
                console.error('AJAX 통신 오류:', error);
                alert('서버와 통신 중 오류가 발생했습니다.');
            });
        });
    });
});