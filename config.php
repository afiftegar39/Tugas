<?php
$host = 'localhost'; // Host database
$db   = 'db_bookcourt'; // Nama database
$user = 'root'; // Username database
$pass = ''; // Password database

try {
    // Membuat koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error untuk PDO
} catch (PDOException $e) {
    // Menangkap exception jika koneksi gagal
    echo "Koneksi gagal: " . $e->getMessage();
}
?>