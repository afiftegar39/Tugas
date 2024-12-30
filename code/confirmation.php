<?php
session_start();

// Cek jika data pemesanan ada di session
if (!isset($_SESSION['name'])) {
    header("Location: booking.php");
    exit();
}

// Ambil data dari session
$name = htmlspecialchars($_SESSION['name']);
$phone = htmlspecialchars($_SESSION['phone']);
$date = htmlspecialchars($_SESSION['date']);
$time = htmlspecialchars($_SESSION['time']);
$duration = htmlspecialchars($_SESSION['duration']);
$court = htmlspecialchars($_SESSION['court']);
$payment = htmlspecialchars($_SESSION['payment']);

// Harga per lapangan
$courtPrices = [
    "Lapangan 1" => 50000,
    "Lapangan 2" => 50000,
    "Lapangan 3" => 50000,
    "Lapangan 4" => 50000,
];

$pricePerHour = $courtPrices[$court];
$totalPrice = $pricePerHour * $duration;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Konfirmasi Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7fafc; /* Warna latar belakang */
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: rgba(255, 255, 255, 0.9); /* Transparan untuk background */
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: #ff8c00; /* Warna tombol */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #ffa500; /* Warna saat hover */
        }
    </style>
</head>
<body>
    <header class="flex justify-between items-center p-4">
        <img alt='Paragon logo' src="gambar/logo2.png" class="h-12" />
        <nav>
            <ul class="flex space-x-6">
                <li><a href='index.php' class="text-black font-bold">Home</a></li>
                <li><a href='booking.php' class="text-black font-bold">Pesan Lagi</a></li>
            </ul>
        </nav>
    </header>

    <div class="container mt-20">
        <h2 class="text-2xl font-bold text-center mb-6">Konfirmasi Pemesanan</h2>
        <p class="mb-4">Terima kasih, <strong><?php echo $name; ?></strong>! Berikut adalah rincian pemesanan Anda:</p>
        
        <ul class="list-disc mb-4 pl-5">
            <li><strong>Nama:</strong> <?php echo $name; ?></li>
            <li><strong>Nomor Telepon:</strong> <?php echo $phone; ?></li>
            <li><strong>Tanggal:</strong> <?php echo $date; ?></li>
            <li><strong>Waktu Mulai:</strong> <?php echo $time; ?></li>
            <li><strong>Durasi:</strong> <?php echo $duration; ?> jam</li>
            <li><strong>Lapangan:</strong> <?php echo $court; ?></li>
            <li><strong>Total Harga:</strong> Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></li>
            <li><strong>Metode Pembayaran:</strong> <?php echo $payment; ?></li>
        </ul>

        <p class="text-gray-600 mb-4">Silakan melakukan pembayaran sesuai dengan metode yang Anda pilih. Setelah pembayaran, Anda akan mendapatkan konfirmasi lebih lanjut melalui email atau telepon.</p>
        
        <a href="index.php" class="btn-primary w-full text-center">Kembali ke Beranda</a>
    </div>

    <?php
    // Hapus data dari session setelah konfirmasi
    session_unset();
    session_destroy();
    ?>
</body>
</html>