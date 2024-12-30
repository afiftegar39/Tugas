<?php
session_start();
$pageTitle = "Pesan Lapangan Futsal";
$headerLogo = "gambar/logogirifutsal.png";

function renderHeader($logo) {
    $loginLogoutItem = isset($_SESSION['user_id']) 
        ? ['href' => '#', 'icon' => 'fas fa-sign-out-alt', 'text' => 'Logout', 'onclick' => 'showLogoutModal()'] 
        : ['href' => '../login.php', 'icon' => 'fas fa-sign-in-alt', 'text' => 'Login'];

    $languageSelector = <<<HTML
    <div class="flex items-center ml-4 border-l border-gray-500 pl-4">
        <select id="languageSelect" class="bg-transparent text-black p-2">
            <option value="id">ID</option>
            <option value="en">EN</option>
        </select>
    </div>
    HTML;

    return <<<HTML
    <header id="navbar" class="flex justify-between items-center p-4 bg-transparent fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="flex items-center" id="logo-container">
            <img alt='Logo' src='$logo' class="h-10" />
            <span id="logo-text" class="text-2xl font-bold text-black ml-2">Girifutsal</span>
        </div>
        <div class="flex items-center">
            <div class="bg-black text-white p-2 rounded-lg shadow-md hover:bg-gray-800 transition duration-300">
                <a href="{$loginLogoutItem['href']}" class="flex items-center small-button" onclick="{$loginLogoutItem['onclick']}">
                    <i class="{$loginLogoutItem['icon']} mr-1"></i>
                    {$loginLogoutItem['text']}
                </a>
            </div>
            $languageSelector
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
        .court-card {
            position: relative;
            background-color: transparent; 
            border-radius: 10px; 
            overflow: hidden; 
            transition: transform 0.3s, box-shadow 0.3s; 
            height: 200px; 
            color: black; 
            border: 1px solid rgba(200, 200, 200, 0.8); 
            cursor: pointer; 
        }
        .court-card.selected,
        .court-card:hover {
            border-color: rgb(0, 0, 0); 
        }
        .slot-card {
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .slot-card:hover {
            border-color: #333;
        }
        .slot-card.active {
            background-color: #f0f0f0;
            border-color: #333;
        }
        .hidden {
            display: none;
        }
        .transition-container {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        .section {
            flex: 1;
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        }
        .section.active {
            transform: translateX(0);
            opacity: 1;
        }
    </style>
</ html>
<body>
    <?php echo renderHeader($headerLogo); ?>

    <div class="container mx-auto text-center mt-24">
        <?php if (isset($_SESSION['username'])): ?>
            <h2 class="text-2xl font-bold">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p class="mt-2 text-gray-700">Sudah booking lapanganmu belum? Segera lakukan pemesanan untuk mendapatkan waktu terbaik!</p>
        <?php endif; ?>
    </div>

        <div class="section" id="bookingSection">
            <div class="mb-4 text-center"> 
                <h1 class="text-xl font-semibold">Pilih Tanggal Booking untuk <span id="selectedCourtName"></span></h1>
                <input type="date" id="bookingDate" class="custom-date-input mb-2" onchange="setDate()">
                <p class="mt-2 text-gray-600">Tap pada slot waktu di bawah untuk memilih Jam Booking</p>
                <div id="timeSlots" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 hidden">
                    <?php
                    $slots = [
                        "00:00 - 01:00" => 300000, "01:00 - 02:00" => 300000, "02:00 - 03:00" => 300000, "03:00 - 04:00" => 300000,
                        "04:00 - 05:00" => 300000, "05:00 - 06:00" => 300000, "06:00 - 07:00" => 300000, "07:00 - 08:00" => 300000,
                        "08:00 - 09:00" => 300000, "09:00 - 10:00" => 300000, "10:00 - 11:00" => 300000, "11:00 - 12:00" => 300000,
                        "12:00 - 13:00" => 300000, "13:00 - 14:00" => 300000, "14:00 - 15:00" => 300000, "15:00 - 16:00" => 300000,
                        "16:00 - 17:00" => 300000, "17:00 - 18:00" => 300000, "18:00 - 19:00" => 300000, "19:00 - 20:00" => 300000,
                        "20:00 - 21:00" => 300000, "21:00 - 22:00" => 300000, "22:00 - 23:00" => 300000, " 23:00 - 24:00" => 300000
                    ];
                    foreach($slots as $slot => $price) {
                        echo '<div class="slot-card" onclick="toggleActive(this, \'' . $slot . '\')">
                                <p class="text-lg">' . $slot . '</p>
                                <p class="text-black">Rp. ' . number_format($price, 0, ',', '.') . '</p>
                                <p class="text-black">Tersedia</p>
                            </div>';
                    }
                    ?>
                </div>
                <div class="mt-4">
                    <button id="payButton" class="bg-black text-white py-2 w-full rounded-md hidden" onclick="showNotification()">Pesan</button>
                </div>
                <button class="bg-gray-500 text-white py-2 mt-4 rounded-md" onclick="goBack()">Kembali</button>
            </div>
        </div>
    </div>

    <div id="notification" class="notification-background hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
            <h1 class="text-2xl font-bold mb-4 text-center">Informasi Booking</h1>
            <div class="mb-4">
                <div class="flex justify-between">
                    <span class="font-semibold">Tanggal Rental</span>
                    <span id="rentalDate" class="font-medium"></span>
                </div>
            </div>
            <div class="mb-4">
                <span class="font-semibold">Jam Rental</span>
                <div id="rentalTime" class="mt-2 font-medium"></div>
            </div>
            <div class="border-t border-gray-300 my-4"></div>
            <div class="mb-4">
                <div class="flex justify-between">
                    <span class="font-semibold">Total</span>
                    <span id="totalPrice" class="font-medium"></span>
                </div>
            </div>
            <div class="mb-4">
                <div class="flex justify-between">
                    <span class="font-semibold">Atas Nama</span>
                    <input type="text" id="customerName" class="border border-gray-300 p-2 w-full sm:w-1/2 rounded">
                </div>
            </div>
            <div class="flex justify-end">
                <button class="bg-black text-white px-4 py-2 rounded" onclick="closeNotification()">Tutup</button>
                <button class="bg-green-500 text-white px-4 py-2 rounded ml-2" onclick="saveBooking()">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        let selectedCourt = null;
        let selectedTimes = [];

        function selectCourt(card, courtId) {
            const bookingSection = document.getElementById('bookingSection');
            const courtSelection = document.getElementById('courtSelection');
            const cards = document.querySelectorAll('.court-card');

            if (selectedCourt === courtId) {
                card.classList.remove('selected');
                selectedCourt = null;
                bookingSection.classList.remove('active');
                courtSelection.classList.add('active');
            } else {
                cards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                selectedCourt = courtId;
                document.getElementById('selectedCourtName').innerText = 'Lapangan ' + courtId;
                courtSelection.classList.remove('active');
                bookingSection.classList.add('active');
                bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function toggleActive(element, time) {
            const index = selectedTimes.indexOf(time);
            if (index > -1) {
                selectedTimes.splice(index, 1);
                element.classList.remove('active');
            } else {
                selectedTimes.push(time);
                element.classList.add('active');
            }
        }

        function setDate() {
            const date = document.getElementById('bookingDate').value;
            document.getElementById('rentalDate').innerText = date;

            if (date) {
                document.getElementById('timeSlots').classList.remove('hidden');
                document.getElementById('payButton').classList.remove('hidden');
            } else {
                document.getElementById('timeSlots').classList.add('hidden');
                document.getElementById('payButton').classList.add('hidden');
            }
        }

        function showNotification() {
            if (selectedTimes.length > 0) {
                document.getElementById('rentalTime'). innerText = selectedTimes.join(', ');
                const totalPrice = selectedTimes.length * 300000; 
                document.getElementById('totalPrice').innerText = 'Rp. ' + totalPrice.toLocaleString();
                document.getElementById('notification').classList.remove('hidden');
            } else {
                alert('Silakan pilih slot waktu terlebih dahulu.');
            }
        }

        function closeNotification() {
            document.getElementById('notification').classList.add('hidden');
        }

        function saveBooking() {
            const customerName = document.getElementById('customerName').value;
            if (customerName) {
                alert('Booking berhasil disimpan untuk ' + customerName);
                closeNotification();
            } else {
                alert('Silakan masukkan nama.');
            }
        }

        function goBack() {
            const bookingSection = document.getElementById('bookingSection');
            const courtSelection = document.getElementById('courtSelection');
            bookingSection.classList.remove('active');
            const cards = document.querySelectorAll('.court-card');
            cards.forEach(c => c.classList.remove('selected'));
            selectedTimes = [];
            document.getElementById('timeSlots').classList.add('hidden');
            document.getElementById('payButton').classList.add('hidden');
            courtSelection.classList.add('active');
        }

        // Set today's date as the minimum selectable date
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();
            const minDate = yyyy + '-' + mm + '-' + dd;
            document.getElementById('bookingDate').setAttribute('min', minDate);
        });
    </script>
</body>
</html>