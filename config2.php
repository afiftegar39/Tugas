<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username'); // Replace with your database username
define('DB_PASS', 'your_password'); // Replace with your database password
define('DB_NAME', 'db_bookcourt'); // Replace with your database name

// MySQLi connection for procedural style
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check MySQLi connection
if ($mysqli->connect_error) {
    die("Koneksi MySQLi gagal: " . $mysqli->connect_error);
}

// PDO connection for PDO style
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
} catch (PDOException $e) {
    // Catch exception if connection fails
    die("Koneksi PDO gagal: " . $e->getMessage());
}
?>