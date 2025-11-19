<?php
// BDAProject/match_detail.php

session_start();
$page_title = "match_detail";

// ===========================================
// 1. 데이터베이스 연결
// ===========================================
$DB_HOST = '127.0.0.1';
$DB_NAME = 'team04';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;

$conn_detail = null;
$match_info_detail = null;
$batting_stats_detail = [];
$pitching_stats_detail = [];
$error_message_detail = null;

$conn_detail = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn_detail->connect_error) {
    $error_message_detail = "Database connection failed: " . $conn_detail->connect_error;
} else {
    $conn_detail->set_charset("utf8mb4");
}

// ===========================================
// 2. match_id 파라미터 확인
// ===========================================
$match_id_detail = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;

if ($match_id_detail <= 0) {
    $error_message_detail = "Invalid match ID.";
}

// ===========================================
// 3. 팀 로고 매핑 함수
// ===========================================
function getTeamLogoSrc_detail($team_name) {
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

// ===========================================
// 4. 경기 기본 정보 조회
// ===========================================
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
            t_home.team_name AS home_team_name,
            m.temp,
            m.humidity,
            m.wind_speed,
            m.rainfall
        FROM 
            matches m
        JOIN stadiums s ON m.stadium_id = s.stadium_id
        JOIN teams t_away ON m.away_team_id = t_away.team_id
        JOIN teams t_home ON m.home_team_id = t_home.team_id
        WHERE m.match_id = ?
    ";
    
    if ($stmt_match = $conn_detail->prepare($sql_match)) {
        $stmt_match->bind_param('i', $match_id_detail);
        $stmt_match->execute();
        $result_match = $stmt_match->get_result();
        
        if ($result_match && $result_match->num_rows > 0) {
            $match_info_detail = $result_match->fetch_assoc();
        } else {
            $error_message_detail = "Match not found.";
        }
        $stmt_match->close();
    }
}

// ===========================================
// 5. 타자 기록 조회
// ===========================================
if ($conn_detail && !$error_message_detail && $match_id_detail > 0) {
    $sql_batting = "
        SELECT 
            b.batting_number,
            p.player_name,
            p.team_name,
            b.hits,
            b.homeruns,
            b.rbi,
            b.batting_avg,
            b.on_base_percentage,
            b.slugging_percentage
        FROM 
            batting_stats b
        JOIN players p ON b.player_id = p.player_id
        WHERE b.match_id = ?
        ORDER BY p.team_name, b.batting_number ASC
    ";
    
    if ($stmt_batting = $conn_detail->prepare($sql_batting)) {
        $stmt_batting->bind_param('i', $match_id_detail);
        $stmt_batting->execute();
        $result_batting = $stmt_batting->get_result();
        
        if ($result_batting) {
            while ($row = $result_batting->fetch_assoc()) {
                $batting_stats_detail[] = $row;
            }
        }
        $stmt_batting->close();
    }
}

// ===========================================
// 6. 투수 기록 조회
// ===========================================
if ($conn_detail && !$error_message_detail && $match_id_detail > 0) {
    $sql_pitching = "
        SELECT 
            p.player_name,
            p.team_name,
            ps.innings_pitched,
            ps.era,
            ps.strikeouts,
            ps.pitch_count,
            ps.win_lost
        FROM 
            pitching_stats ps
        JOIN players p ON ps.player_id = p.player_id
        WHERE ps.match_id = ?
        ORDER BY p.team_name, p.player_name ASC
    ";
    
    if ($stmt_pitching = $conn_detail->prepare($sql_pitching)) {
        $stmt_pitching->bind_param('i', $match_id_detail);
        $stmt_pitching->execute();
        $result_pitching = $stmt_pitching->get_result();
        
        if ($result_pitching) {
            while ($row = $result_pitching->fetch_assoc()) {
                $pitching_stats_detail[] = $row;
            }
        }
        $stmt_pitching->close();
    }
}

if ($conn_detail) {
    $conn_detail->close();
}

require_once 'header.php';
?>

<div class="card-box match-detail-card">
    <?php if ($error_message_detail): ?>
        <div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px; margin: 20px 0;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error_message_detail); ?>
            <br><br>
            <a href="matches.php" style="color: #004085; text-decoration: underline;">← Back to Matches</a>
        </div>
    <?php elseif ($match_info_detail): ?>
        
        <!-- 경기 기본 정보 -->
        <div style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 10px;">Match Details</h3>
            <a href="matches.php" style="color: #007bff; text-decoration: none; font-size: 0.9em;">
                ← Back to Matches
            </a>
        </div>

        <!-- 스코어보드 -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 5px;">
                    <?php echo date('Y-m-d (l) H:i', strtotime($match_info_detail['match_date'])); ?>
                </div>
                <div style="font-size: 0.85em; opacity: 0.8;">
                    📍 <?php echo htmlspecialchars($match_info_detail['stadium_name']); ?>
                </div>
            </div>

            <div style="display: flex; justify-content: space-around; align-items: center; max-width: 900px; margin: 0 auto;">
                <!-- Away Team -->
                <div style="text-align: center; min-width: 200px;">
                    <?php
                    $away_logo = getTeamLogoSrc_detail($match_info_detail['away_team_name']);
                    if ($away_logo):
                    ?>
                        <div style="margin-bottom: 15px;">
                            <img src="<?php echo $away_logo; ?>" 
                                 alt="<?php echo htmlspecialchars($match_info_detail['away_team_name']); ?> logo"
                                 style="width: 80px; height: 80px; object-fit: contain; background: white; border-radius: 50%; padding: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                        </div>
                    <?php endif; ?>
                    <div style="font-weight: bold; font-size: 1.2em; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($match_info_detail['away_team_name']); ?>
                    </div>
                    <div style="font-size: 2.5em; font-weight: bold;">
                        <?php echo $match_info_detail['score_away']; ?>
                    </div>
                    <div style="font-size: 0.8em; opacity: 0.8; margin-top: 5px;">Away</div>
                </div>

                <div style="font-size: 2em; font-weight: bold; padding: 0 30px;">VS</div>

                <!-- Home Team -->
                <div style="text-align: center; min-width: 200px;">
                    <?php
                    $home_logo = getTeamLogoSrc_detail($match_info_detail['home_team_name']);
                    if ($home_logo):
                    ?>
                        <div style="margin-bottom: 15px;">
                            <img src="<?php echo $home_logo; ?>" 
                                 alt="<?php echo htmlspecialchars($match_info_detail['home_team_name']); ?> logo"
                                 style="width: 80px; height: 80px; object-fit: contain; background: white; border-radius: 50%; padding: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                        </div>
                    <?php endif; ?>
                    <div style="font-weight: bold; font-size: 1.2em; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($match_info_detail['home_team_name']); ?>
                    </div>
                    <div style="font-size: 2.5em; font-weight: bold;">
                        <?php echo $match_info_detail['score_home']; ?>
                    </div>
                    <div style="font-size: 0.8em; opacity: 0.8; margin-top: 5px;">Home</div>
                </div>
            </div>

            <!-- 날씨 정보 -->
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); display: flex; justify-content: center; gap: 25px; font-size: 0.9em; flex-wrap: wrap;">
                <div>🌡️ <?php echo number_format($match_info_detail['temp'], 1); ?>°C</div>
                <div>💧 <?php echo number_format($match_info_detail['humidity'], 1); ?>%</div>
                <div>🌬️ <?php echo number_format($match_info_detail['wind_speed'], 1); ?>m/s</div>
                <div>🌧️ <?php echo number_format($match_info_detail['rainfall'], 1); ?>mm</div>
            </div>
        </div>

        <!-- 타자 기록 섹션 -->
        <div style="margin-bottom: 40px;">
            <h4 style="padding: 15px; background: #28a745; color: white; border-radius: 8px 8px 0 0; margin: 0;">
                ⚾ Batting Statistics
            </h4>
            
            <?php if (empty($batting_stats_detail)): ?>
                <div style="padding: 30px; text-align: center; background: #f8f9fa; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px;">
                    <p style="color: #6c757d; margin: 0;">No batting records available.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #dee2e6;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Order</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Team</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Player</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Hits</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">HR</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">RBI</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">AVG</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">OBP</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">SLG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_team = '';
                            foreach ($batting_stats_detail as $batter): 
                                if ($current_team !== $batter['team_name']) {
                                    $current_team = $batter['team_name'];
                            ?>
                                <tr style="background: #e7f3ff;">
                                    <td colspan="9" style="padding: 10px; border: 1px solid #dee2e6; font-weight: bold; color: #0056b3;">
                                        <?php echo htmlspecialchars($current_team); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $batter['batting_number']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">
                                    <?php echo htmlspecialchars($batter['team_name']); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #dee2e6; font-weight: 500;">
                                    <?php echo htmlspecialchars($batter['player_name']); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $batter['hits']; ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $batter['homeruns']; ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $batter['rbi']; ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo number_format($batter['batting_avg'], 3); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo number_format($batter['on_base_percentage'], 3); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo number_format($batter['slugging_percentage'], 3); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 투수 기록 섹션 -->
        <div style="margin-bottom: 40px;">
            <h4 style="padding: 15px; background: #dc3545; color: white; border-radius: 8px 8px 0 0; margin: 0;">
                🥎 Pitching Statistics
            </h4>
            
            <?php if (empty($pitching_stats_detail)): ?>
                <div style="padding: 30px; text-align: center; background: #f8f9fa; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px;">
                    <p style="color: #6c757d; margin: 0;">No pitching records available.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #dee2e6;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Team</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Player</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">IP</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">ERA</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">K</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Pitches</th>
                                <th style="padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">W/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_team_pitch = '';
                            foreach ($pitching_stats_detail as $pitcher): 
                                if ($current_team_pitch !== $pitcher['team_name']) {
                                    $current_team_pitch = $pitcher['team_name'];
                            ?>
                                <tr style="background: #ffe7e7;">
                                    <td colspan="7" style="padding: 10px; border: 1px solid #dee2e6; font-weight: bold; color: #c82333;">
                                        <?php echo htmlspecialchars($current_team_pitch); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 10px; border: 1px solid #dee2e6;">
                                    <?php echo htmlspecialchars($pitcher['team_name']); ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #dee2e6; font-weight: 500;">
                                    <?php echo htmlspecialchars($pitcher['player_name']); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo number_format($pitcher['innings_pitched'], 1); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo number_format($pitcher['era'], 2); ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $pitcher['strikeouts']; ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <?php echo $pitcher['pitch_count']; ?>
                                </td>
                                <td style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">
                                    <span style="padding: 4px 12px; background: <?php echo $pitcher['win_lost'] === 'W' ? '#28a745' : '#dc3545'; ?>; color: white; border-radius: 4px; font-weight: bold;">
                                        <?php echo htmlspecialchars($pitcher['win_lost']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="matches.php" style="display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">
                ← Back to All Matches
            </a>
        </div>

    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
