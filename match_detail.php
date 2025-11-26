<?php
// BDAProject/match_detail.php
// author: Eunhyeon Kwon

// 세션 시작 
session_start();

$page_title = "match_detail";

// config.php만 불러오면 DB 연결 자동 처리됨
require_once __DIR__ . '/config/config.php';

// config.php에서 제공하는 DB 객체를 $conn으로 사용한다고 가정
$conn_detail = $conn;

$match_info_detail = null;
$batting_away = [];
$batting_home = [];
$pitching_away = [];
$pitching_home = [];
$error_message_detail = null;

// match_id 파라미터 확인
$match_id_detail = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;

if ($match_id_detail <= 0) {
    $error_message_detail = "Invalid match ID.";
}

// 경기 기본 정보 조회
if (!$error_message_detail && $match_id_detail > 0) {

    $sql_match = "
        SELECT 
            m.match_id,
            m.match_date,
            m.score_away,
            m.score_home,
            s.stadium_name,
            t_away.team_id AS away_team_id,
            t_away.team_name AS away_team_name,
            t_home.team_id AS home_team_id,
            t_home.team_name AS home_team_name
        FROM matches m
        JOIN stadiums s ON m.stadium_id = s.stadium_id
        JOIN teams t_away ON m.away_team_id = t_away.team_id
        JOIN teams t_home ON m.home_team_id = t_home.team_id
        WHERE m.match_id = ?
    ";

    $stmt = $conn_detail->prepare($sql_match);
    $stmt->bind_param('i', $match_id_detail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $match_info_detail = $result->fetch_assoc();
    } else {
        $error_message_detail = "Match not found.";
    }
    $stmt->close();
}

// 타자 기록 조회 함수화
function loadBatting($conn, $match_id, $team_name) {
    $list = [];

    $sql = "
        SELECT 
            p.player_name,
            b.hits,
            b.homeruns,
            b.rbi,
            b.batting_avg,
            b.on_base_percentage,
            b.slugging_percentage
        FROM batting_stats b
        JOIN players p ON b.player_id = p.player_id
        WHERE b.match_id = ? AND p.team_name = ?
        ORDER BY b.batting_number ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $match_id, $team_name);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }

    $stmt->close();
    return $list;
}

// 투수 기록 조회 함수화
function loadPitching($conn, $match_id, $team_name) {
    $list = [];

    $sql = "
        SELECT 
            p.player_name,
            ps.innings_pitched,
            ps.era,
            ps.strikeouts,
            ps.pitch_count,
            ps.win_lost
        FROM pitching_stats ps
        JOIN players p ON ps.player_id = p.player_id
        WHERE ps.match_id = ? AND p.team_name = ?
        ORDER BY p.player_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $match_id, $team_name);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }

    $stmt->close();
    return $list;
}

// 데이터 로딩
if ($match_info_detail) {
    $batting_away = loadBatting($conn_detail, $match_id_detail, $match_info_detail['away_team_name']);
    $batting_home = loadBatting($conn_detail, $match_id_detail, $match_info_detail['home_team_name']);
    $pitching_away = loadPitching($conn_detail, $match_id_detail, $match_info_detail['away_team_name']);
    $pitching_home = loadPitching($conn_detail, $match_id_detail, $match_info_detail['home_team_name']);
}

require_once 'header.php';
?>
