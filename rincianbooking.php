<?php
session_start();
include 'config.php';

// Database connection
$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$pageTitle = "Rincian Booking Futsal";
$headerLogo = "gambar/logogirifutsal.png";

// Ambil data dari query string
$court = isset($_GET['court']) ? $_GET['court'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$slots = isset($_GET['slots']) ? json_decode($_GET['slots']) : [];
$price = isset($_GET['price']) ? $_GET['price'] : 0;
$startTime = isset($_GET['startTime']) ? $_GET['startTime'] : '';
$endTime = isset($_GET['endTime']) ? $_GET['endTime'] : '';

// Validasi jam mulai dan jam selesai
$errorMessage = '';
if (empty($startTime) || empty($endTime)) {
    $errorMessage = "Waktu mulai dan waktu selesai harus diisi.";
} else {
    // Cek format waktu
    $startTimeFormat = DateTime::createFromFormat('H:i', $startTime);
    $endTimeFormat = DateTime::createFromFormat('H:i', $endTime);
    
    if (!$startTimeFormat || !$endTimeFormat) {
        $errorMessage = "Format waktu tidak valid. Gunakan format HH:MM.";
    } elseif ($startTimeFormat >= $endTimeFormat) {
        $errorMessage = "Waktu mulai harus lebih awal dari waktu selesai.";
    }
}

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Pengguna"; 
}

function renderHeader($logo) {
    $loginLogoutItem = isset($_SESSION['user_id']) ?
        ['href' => '../logout.php', 'icon' => 'fas fa-sign-out-alt', 'text' => 'Logout'] :
        ['href' => '../login.php', 'icon' => 'fas fa-sign-in-alt', 'text' => 'Login'];

    return <<<HTML
    <header id="navbar" class="flex justify-between items-center p-4 bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center" id="logo-container">
            <img alt='Logo' src='$logo' class="h-10" />
            <span id="logo-text" class="text-2xl font-bold text-black ml-2">Girifutsal</span>
        </div>
        <div class="flex items-center">
            <div class="bg-black text-white p-2 rounded-lg shadow-md transition duration-300 hover:bg-gray-800 flex items-center">
                <a href="{$loginLogoutItem['href']}" class="flex items-center small-button text-lg">
                    <i class="{$loginLogoutItem['icon']} mr-1"></i>
                    {$loginLogoutItem['text']}
                </a>
            </div>
        </div>
    </header>
    HTML;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            background-color: #f9f9f9; 
            font-family: 'Poppins', sans-serif; 
            color: #000; 
        }
        .welcome-section {
            position: relative;
            padding: 60px 20px; 
            color: #fff;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('gambar/bguser.jpeg') no-repeat center center;
            background-size: cover; 
            border-radius: 15px; 
            box-shadow : 0 4px 20px rgba(0, 0, 0, 0.3); 
            transition: transform 0.3s;
        }
        .welcome-section:hover {
            transform: scale(1.02);
        }
        .booking-info { 
            margin-top: 20px; 
            padding: 20px; 
            background-color: #fff; 
            border-radius: 10px; 
            box-shadow: 0 4px 10px rgba( 0, 0, 0, 0.1); 
        } 
        .bordered-card {
            border: 1px solid #e0e0e0; 
            border-radius: 10px; 
            padding: 15px; 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .bordered-card:hover {
            transform: scale(1.03); 
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); 
        }
        .small-button {
            padding: 8px 12px; 
            border-radius: 5px; 
            font-size: 1.125rem; 
        }
        .bg-black {
            background-color: #000; 
        }
        .bg-gray-800 {
            background-color: #333; 
        }
        .input-field {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            transition: border-color 0.3s;
        }
        .input-field:focus {
            border-color: #007bff;
            outline: none;
        }
        footer {
            background-color: #f1f1f1;
            padding: 10px 0;
            margin-top: 20px;
        }
        #message {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            z-index: 1000;
            display: none; /* Sembunyikan pesan secara default */
        }
    </style>
</head> 
<body> 
    <?php echo renderHeader($headerLogo); ?>
    <section class="mt-20 text-center welcome-section" id="welcomeSection">
        <h2 class="text-4xl font-bold">Terimakasih, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></h2>
        <p class="mt-2 text-lg">Berikut adalah rincian yang anda telah pesan, Selanjutnya lakukan Pembayaran dan Kirim bukti bahwa anda sudah membayar dan Tunggu Admin mengkonvirmasi pesanan anda,</p>
    </section>

    <div id="bookingInfo" class="booking-info">
        <h3 class="text-2xl font-bold text-center mb-6">Rincian Booking dan Pembayaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bordered-card">
                <p class="font-semibold text-lg">Invoice:</p>
                <p id="invoiceCode" class="text-lg text-gray-700"><?php echo uniqid("INV-"); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Lapangan:</p>
                <p id="selectedCourtInfo" class="text-lg text-gray-700"><?php echo htmlspecialchars($court); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Tanggal Rental:</p>
                <p id="rentalDateInfo" class="text-lg text-gray-700"><?php echo htmlspecialchars($date); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Jam Mulai:</p>
                <p class="text-lg text-gray-700"><?php echo htmlspecialchars($startTime); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Jam Selesai:</p>
                <p class="text-lg text-gray-700"><?php echo htmlspecialchars($endTime); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Total:</p>
 <p id="totalPriceInfo" class="text-lg text-gray-700">Rp. <?php echo number_format($price, 0, ',', '.'); ?></p>
            </div>
            <div class="bordered-card">
                <p class="font-semibold text-lg">Atas Nama:</p>
                <input type="text" id="customerNameInput" class="input-field" placeholder="Masukkan nama Anda" oninput="checkFormValidity()" />
            </div>
        </div>

        <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
            <div class="bordered-card bg-red-100 text-red-700 p-4 rounded-md mb-4">
                <p><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>

        <div class="mt-6 border-t border-gray-300 pt-4">
            <h4 class="font-semibold text-xl mb-4 ">Metode Pembayaran</h4>
            <div class="flex items-center mb-2">
                <p class="text-md flex-grow">Silakan transfer ke nomor rekening berikut:</p>
                <button id="copyButton" class="bg-black text-white px-2 py-1 rounded hover:bg-gray-800" onclick="copyToClipboard()">Salin</button>
            </div>
            <div class="bg-gray-100 p-4 rounded-md my-4 flex justify-between items-center">
                <h2 class="text-lg font-bold">Bank Mandiri</h2>
                <p id="bankAccount" class="text-xl font-bold">1380023019743</p>
            </div>
        </div>

        <div class="mt-4">
            <p class="font-semibold text-lg">Unggah Bukti Pembayaran:</p>
            <input type="file" id="paymentReceipt" class="input-field mb-4" onchange="checkFormValidity()" />
        </div>

        <button id="submitButton" class="bg-black text-white w-full mt-6 p-2 rounded hover:bg-gray-800" disabled onclick="submitBooking()">Ajukan Pesanan</button>
    </div>

    <div id="message" class="hidden"></div>

    <footer class="mt-10 text-center text-sm text-gray-500">
        <p>© 2024 Dibuat oleh Afftgrs. All rights reserved.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function copyToClipboard() {
            const accountNumber = document.getElementById('bankAccount').textContent;
            navigator.clipboard.writeText(accountNumber)
                .then(() => {
                    alert("Nomor rekening telah disalin: " + accountNumber);
                })
                .catch(err => {
                    console.error("Gagal menyalin: ", err);
                    alert("Gagal menyalin nomor rekening.");
                });
        }

        function checkFormValidity() {
            const customerName = document.getElementById('customerNameInput').value.trim();
            const paymentReceipt = document.getElementById('paymentReceipt').files.length > 0;
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = !(customerName && paymentReceipt);
        }

        function submitBooking() {
            const customerName = document.getElementById('customerNameInput').value.trim();
            const paymentReceipt = document.getElementById('paymentReceipt').files[0];
            const price = document.getElementById('totalPriceInfo').textContent.replace(/[^0-9]/g, ''); // Extract numeric value from formatted price

            if (!customerName || !paymentReceipt) {
                alert("Silakan lengkapi semua informasi sebelum mengajukan pesanan.");
                return;
            }

            const formData = new FormData();
            formData.append('customerName', customerName);
            formData.append('invoiceCode', document.getElementById('invoiceCode').textContent); 
            formData.append('court', document.getElementById('selectedCourtInfo').textContent);
            formData.append('date', document.getElementById('rentalDateInfo').textContent);
            formData.append('startTime', '<?php echo htmlspecialchars($startTime); ?>');
            formData.append('endTime', '<?php echo htmlspecialchars($endTime); ?>');
            formData.append('paymentReceipt', paymentReceipt);
            formData.append('price', price); // Add price to form data

            fetch('submit_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById ('message');
                if (data.success) {
                    messageDiv.innerText = data.message; // Set pesan
                    messageDiv.style.display = 'block'; // Tampilkan
                    setTimeout(() => {
                        window.location.href = 'user.php'; // Arahkan kembali ke user.php setelah 3 detik
                    }, 3000);
                } else {
                    alert("Gagal mengajukan pesanan: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan saat mengajukan pesanan.");
            });
        }

        window.copyToClipboard = copyToClipboard;
        window.checkFormValidity = checkFormValidity;
        window.submitBooking = submitBooking;
    });
    </script>
</body>
</html>