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

try {
    // Membuat koneksi ke database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error untuk PDO
} catch (PDOException $e) {
    // Menangkap exception jika koneksi gagal
    echo "Koneksi gagal: " . $e->getMessage();
}
