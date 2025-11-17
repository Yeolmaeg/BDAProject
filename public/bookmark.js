document.addEventListener('DOMContentLoaded', () => {
    // 1. 모든 북마크 아이콘 요소를 가져옵니다.
    const bookmarkIcons = document.querySelectorAll('.bookmark-icon');
    
    // 이 함수는 모든 아이콘에서 북마크 스타일을 제거합니다.
    function clearAllBookmarks() {
        bookmarkIcons.forEach(icon => {
            icon.classList.remove('is-bookmarked');
        });
    }

    // 2. 각 아이콘에 클릭 이벤트 리스너를 추가합니다.
    bookmarkIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            // 클릭된 아이콘에서 team_id를 가져옵니다.
            const teamId = this.dataset.teamId;
            const clickedIcon = this;

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
            .then(response => {
                // HTTP 상태 코드에 따라 오류 처리
                if (!response.ok) {
                    // 4xx, 5xx 오류 처리
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || '서버 응답 오류');
                    });
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    console.log('북마크 상태 변경 성공:', result.message);
                    
                    // 5. 단일 선택 로직 강화:
                    // 모든 북마크 클래스를 먼저 제거합니다.
                    clearAllBookmarks();

                    // 서버에서 받은 메시지를 통해 현재 상태를 정확히 판단하여 클래스를 적용/해제합니다.
                    if (result.message.includes('설정되었습니다')) {
                        // 새로운 팀이 북마크로 설정됨
                        clickedIcon.classList.add('is-bookmarked');
                    } else if (result.message.includes('해제되었습니다')) {
                        // 기존 팀의 북마크가 해제됨 (clearAllBookmarks()로 이미 처리됨)
                        // 추가 작업 필요 없음
                    }
                    
                } else {
                    // 서버에서 실패를 반환했을 경우
                    alert('북마크 변경 실패: ' + result.message);
                }
            })
            .catch(error => {
                console.error('AJAX 통신 오류:', error);
                // 서버 처리 오류 메시지 또는 네트워크 오류 메시지를 사용자에게 표시
                alert('서버와 통신 중 오류가 발생했습니다: ' + error.message);
            });
        });
    });
    
    // teams.php에서 이미 초기 북마크 상태를 HTML 클래스로 반영하고 있으므로, 
    // 추가적인 초기화 로직은 필요하지 않습니다.
});