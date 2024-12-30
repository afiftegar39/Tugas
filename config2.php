<?php
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    $dbHost = $env["DB_HOST"];
    $dbUsername = $env["DB_USERNAME"];
    $dbPassword = $env["DB_PASSWORD"];
    $dbName = $env["DB_NAME"];
} else {
    $dbHost = getenv("DB_HOST");
    $dbUsername = getenv("DB_USERNAME");
    $dbPassword = getenv("DB_PASSWORD");
    $dbName = getenv("DB_NAME");
}

// MySQLi connection for procedural style
$mysqli = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check MySQLi connection
if ($mysqli->connect_error) {
    die("Koneksi MySQLi gagal: " . $mysqli->connect_error);
}

// PDO connection for PDO style
try {
    $pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
} catch (PDOException $e) {
    // Catch exception if connection fails
    die("Koneksi PDO gagal: " . $e->getMessage());
}
