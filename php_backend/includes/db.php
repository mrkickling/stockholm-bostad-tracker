<?php

$env = parse_ini_file('.env');
$host = $env['MYSQL_HOST'];
$db   = $env['MYSQL_DATABASE'];
$user = $env['MYSQL_USER'];
$pass = $env['MYSQL_PASSWORD'];

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");
?>
