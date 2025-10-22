<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo "FAIL: " . $mysqli->connect_error . "\n";
  exit;
}
$mysqli->set_charset('utf8mb4');
echo "OK\n";
