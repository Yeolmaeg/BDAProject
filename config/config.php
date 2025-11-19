<?php
$DB_HOST = 'localhost';
$DB_NAME = 'team04';
$DB_USER = 'team04';
$DB_PASS = 'team04';
$DB_PORT = 3306;

// 로컬 작업시
$local = __DIR__ . '/config.local.php';
if (file_exists($local)) require $local; 

define('DB_HOST', $DB_HOST);
define('DB_NAME', $DB_NAME);
define('DB_USER', $DB_USER);
define('DB_PASS', $DB_PASS);
define('DB_PORT', $DB_PORT);

