<?php
// Mengatur header untuk menghindari caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Content-Type: text/html; charset=utf-8");

// Menghubungkan ke database
include 'config.php'; // Pastikan ini terhubung ke database

// Cek apakah ID dikirim melalui POST
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Ambil path foto bukti bayar dari tabel booking_requests
    $stmt = $pdo->prepare("SELECT photo_path FROM booking_requests WHERE id = ?");
    $stmt->execute([$id]);
    $photo_path = $stmt->fetchColumn();

    // Cek apakah ada foto yang ditemukan
    if ($photo_path) {
        // Tampilkan gambar bukti bayar
        echo '<img src="' . htmlspecialchars($photo_path) . '" alt="Bukti Bayar" style="max-width: 100%;">';
    } else {
        // Jika tidak ada bukti bayar
        echo 'Tidak ada bukti bayar';
    }
} else {
    // Jika ID tidak dikirim
    echo 'ID tidak valid.';
}
?>