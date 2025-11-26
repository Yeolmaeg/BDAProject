<?php
// BDAProject/match_detail.php
// author: Eunhyeon Kwon

// 세션 시작 
session_start();

$page_title = "match_detail";

// 데이터베이스 연결
require_once 'config.php';

$conn_detail = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
$match_info_detail = null;
$batting_away = [];
$batting_home = [];
$pitching_away = [];
$pitching_home = [];
$error_message_detail = null;

if ($conn_detail->connect_error) {
    $error_message_detail = "Database connection failed: " . $conn_detail->connect_error;
} else {
    $conn_detail->set_charset("utf8mb4");
}

// match_id 파라미터 확인
$match_id_detail = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;

if ($match_id_detail <= 0) {
    $error_message_detail = "Invalid match ID.";
}

// 경기 기본 정보 조회
if ($conn_detail && !$error_message_detail && $match_id_detail > 0) {
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

// 타자 기록 조회 (Away 팀)
if ($conn_detail && $match_info_detail) {
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
    
    $stmt = $conn_detail->prepare($sql);
    $stmt->bind_param('is', $match_id_detail, $match_info_detail['away_team_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $batting_away[] = $row;
    }
    $stmt->close();
}

// 타자 기록 조회 (Home 팀)
if ($conn_detail && $match_info_detail) {
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
    
    $stmt = $conn_detail->prepare($sql);
    $stmt->bind_param('is', $match_id_detail, $match_info_detail['home_team_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $batting_home[] = $row;
    }
    $stmt->close();
}

// 투수 기록 조회 (Away 팀)
if ($conn_detail && $match_info_detail) {
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
    
    $stmt = $conn_detail->prepare($sql);
    $stmt->bind_param('is', $match_id_detail, $match_info_detail['away_team_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pitching_away[] = $row;
    }
    $stmt->close();
}

// 투수 기록 조회 (Home 팀)
if ($conn_detail && $match_info_detail) {
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
    
    $stmt = $conn_detail->prepare($sql);
    $stmt->bind_param('is', $match_id_detail, $match_info_detail['home_team_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pitching_home[] = $row;
    }
    $stmt->close();
}

if ($conn_detail) {
    $conn_detail->close();
}

require_once 'header.php';
?>

<div class="card-box">
    <?php if ($error_message_detail): ?>
        <div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px; margin: 20px 0;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error_message_detail); ?>
            <br><br>
            <a href="matches.php" style="color: #004085;">← Back to Matches</a>
        </div>
    <?php elseif ($match_info_detail): ?>

        <!-- 경기 헤더 -->
        <h1 class="page-title">
            <?php echo htmlspecialchars($match_info_detail['away_team_name']); ?> 
            <span style="font-size: 1.2em; margin: 0 15px; color: #007bff;"><?php echo $match_info_detail['score_away']; ?></span>
            VS
            <span style="font-size: 1.2em; margin: 0 15px; color: #007bff;"><?php echo $match_info_detail['score_home']; ?></span>
            <?php echo htmlspecialchars($match_info_detail['home_team_name']); ?>
        </h1>
        <p class="page-description">
            📅 <?php echo date('Y-m-d (l) H:i', strtotime($match_info_detail['match_date'])); ?><br>
            📍 <?php echo htmlspecialchars($match_info_detail['stadium_name']); ?>
        </p>

        <!-- 타자 기록 -->
        <div class="table-header-row">
            <h2 class="section-title">🏏 Batting Statistics</h2>
        </div>
        
        <!-- Away 팀 타자 -->
        <div style="background: #f8f9fa; padding: 12px 20px; margin: 0 0 0 0; font-weight: 600; color: #495057; border-left: 4px solid #007bff;">
            <?php echo htmlspecialchars($match_info_detail['away_team_name']); ?> (Away)
        </div>
        <?php if (empty($batting_away)): ?>
            <div style="padding: 30px; text-align: center; color: #6c757d; background: white; border: 1px solid #dee2e6;">
                No batting records available for this team.
            </div>
        <?php else: ?>
            <div class="player-rank-card" style="margin-top: 0;">
                <table class="player-rank-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Hits</th>
                            <th>HR</th>
                            <th>RBI</th>
                            <th>AVG</th>
                            <th>OBP</th>
                            <th>SLG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batting_away as $batter): ?>
                        <tr>
                            <td style="text-align: left; font-weight: 500;">
                                <?php echo htmlspecialchars($batter['player_name']); ?>
                            </td>
                            <td><?php echo $batter['hits']; ?></td>
                            <td><?php echo $batter['homeruns']; ?></td>
                            <td><?php echo $batter['rbi']; ?></td>
                            <td><?php echo number_format($batter['batting_avg'], 3); ?></td>
                            <td><?php echo number_format($batter['on_base_percentage'], 3); ?></td>
                            <td><?php echo number_format($batter['slugging_percentage'], 3); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Home 팀 타자 -->
        <div style="background: #f8f9fa; padding: 12px 20px; margin: 20px 0 0 0; font-weight: 600; color: #495057; border-left: 4px solid #007bff;">
            <?php echo htmlspecialchars($match_info_detail['home_team_name']); ?> (Home)
        </div>
        <?php if (empty($batting_home)): ?>
            <div style="padding: 30px; text-align: center; color: #6c757d; background: white; border: 1px solid #dee2e6;">
                No batting records available for this team.
            </div>
        <?php else: ?>
            <div class="player-rank-card" style="margin-top: 0;">
                <table class="player-rank-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Hits</th>
                            <th>HR</th>
                            <th>RBI</th>
                            <th>AVG</th>
                            <th>OBP</th>
                            <th>SLG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batting_home as $batter): ?>
                        <tr>
                            <td style="text-align: left; font-weight: 500;">
                                <?php echo htmlspecialchars($batter['player_name']); ?>
                            </td>
                            <td><?php echo $batter['hits']; ?></td>
                            <td><?php echo $batter['homeruns']; ?></td>
                            <td><?php echo $batter['rbi']; ?></td>
                            <td><?php echo number_format($batter['batting_avg'], 3); ?></td>
                            <td><?php echo number_format($batter['on_base_percentage'], 3); ?></td>
                            <td><?php echo number_format($batter['slugging_percentage'], 3); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- 투수 기록 -->
        <div class="table-header-row" style="margin-top: 40px;">
            <h2 class="section-title">🥎 Pitching Statistics</h2>
        </div>
        
        <!-- Away 팀 투수 -->
        <div style="background: #f8f9fa; padding: 12px 20px; margin: 0 0 0 0; font-weight: 600; color: #495057; border-left: 4px solid #007bff;">
            <?php echo htmlspecialchars($match_info_detail['away_team_name']); ?> (Away)
        </div>
        <?php if (empty($pitching_away)): ?>
            <div style="padding: 30px; text-align: center; color: #6c757d; background: white; border: 1px solid #dee2e6;">
                No pitching records available for this team.
            </div>
        <?php else: ?>
            <div class="player-rank-card" style="margin-top: 0;">
                <table class="player-rank-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>IP</th>
                            <th>ERA</th>
                            <th>K</th>
                            <th>Pitches</th>
                            <th>W/L</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pitching_away as $pitcher): ?>
                        <tr>
                            <td style="text-align: left; font-weight: 500;">
                                <?php echo htmlspecialchars($pitcher['player_name']); ?>
                            </td>
                            <td><?php echo number_format($pitcher['innings_pitched'], 1); ?></td>
                            <td><?php echo number_format($pitcher['era'], 2); ?></td>
                            <td><?php echo $pitcher['strikeouts']; ?></td>
                            <td><?php echo $pitcher['pitch_count']; ?></td>
                            <td>
                                <?php if ($pitcher['win_lost'] === 'W'): ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #28a745; color: white;">
                                        W
                                    </span>
                                <?php elseif ($pitcher['win_lost'] === 'L'): ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #dc3545; color: white;">
                                        L
                                    </span>
                                <?php else: ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #adb5bd; color: white;">
                                        <?php echo !empty($pitcher['win_lost']) ? htmlspecialchars($pitcher['win_lost']) : '-'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Home 팀 투수 -->
        <div style="background: #f8f9fa; padding: 12px 20px; margin: 20px 0 0 0; font-weight: 600; color: #495057; border-left: 4px solid #007bff;">
            <?php echo htmlspecialchars($match_info_detail['home_team_name']); ?> (Home)
        </div>
        <?php if (empty($pitching_home)): ?>
            <div style="padding: 30px; text-align: center; color: #6c757d; background: white; border: 1px solid #dee2e6;">
                No pitching records available for this team.
            </div>
        <?php else: ?>
            <div class="player-rank-card" style="margin-top: 0;">
                <table class="player-rank-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>IP</th>
                            <th>ERA</th>
                            <th>K</th>
                            <th>Pitches</th>
                            <th>W/L</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pitching_home as $pitcher): ?>
                        <tr>
                            <td style="text-align: left; font-weight: 500;">
                                <?php echo htmlspecialchars($pitcher['player_name']); ?>
                            </td>
                            <td><?php echo number_format($pitcher['innings_pitched'], 1); ?></td>
                            <td><?php echo number_format($pitcher['era'], 2); ?></td>
                            <td><?php echo $pitcher['strikeouts']; ?></td>
                            <td><?php echo $pitcher['pitch_count']; ?></td>
                            <td>
                                <?php if ($pitcher['win_lost'] === 'W'): ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #28a745; color: white;">
                                        W
                                    </span>
                                <?php elseif ($pitcher['win_lost'] === 'L'): ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #dc3545; color: white;">
                                        L
                                    </span>
                                <?php else: ?>
                                    <span style="padding: 4px 12px; border-radius: 4px; font-weight: bold; background: #adb5bd; color: white;">
                                        <?php echo !empty($pitcher['win_lost']) ? htmlspecialchars($pitcher['win_lost']) : '-'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="matches.php" style="display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">
                ← Back to All Matches
            </a>
        </div>

    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
