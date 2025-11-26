<?php
$DB_HOST = 'localhost';
$DB_NAME = 'team04';
$DB_USER = 'team04';
$DB_PASS = 'team04';
$DB_PORT = 3306;

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
