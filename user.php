<?php
session_start();
include 'config.php'; // Include your database connection file

$pageTitle = "Pesan Lapangan Futsal";
$headerLogo = "gambar/logogirifutsal.png";

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Pengguna"; // Default username if not set
}

// Harga per lapangan
$courtPrices = [
    'A' => 150000,
    'B' => 120000,
    'C' => 110000,
];

// Jenis lapangan
$courtTypes = [
    'A' => 'Vinyl (Indoor)',
    'B' => 'Vinyl (Indoor)',
    'C' => 'Rumput Sintetis (Outdoor)',
];

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
            <div class="bg-black text-white p-2 rounded-lg shadow-md hover:bg-gray-800 transition duration-300">
                <a href="{$loginLogoutItem['href']}" class="flex items-center small-button">
                    <i class="{$loginLogoutItem['icon']} mr-1"></i>
                    {$loginLogoutItem['text']}
                </a>
            </div>
        </div>
    </header>
    HTML;
}

// Handle AJAX request to fetch booked slots
if (isset($_GET['action']) && $_GET['action'] === 'fetchBookedSlots') {
    $court_id = $_GET['court'];
    $rental_date = $_GET['date'];

    // Prepare the SQL statement to prevent SQL injection
    $query = "SELECT start_time, end_time, customer_name FROM rental WHERE court_id = ? AND rental_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $court_id, $rental_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedSlots = [];
    while ($row = $result->fetch_assoc()) {
        $bookedSlots[$row['start_time'] . ' - ' . $row['end_time']] = $row['customer_name'];
    }

    echo json_encode($bookedSlots);
    exit; // Stop further execution
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
            color: #333; 
        }
        h1, h2, h3 {
            color: #1a1a1a; 
        }
        .court-card, .slot-card {
            border: 1px solid #e0e0e0; 
            border-radius: 10px; 
            background-color: #ffffff; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .court-card:hover, .slot-card:hover {
            transform : translateY(-5px); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .court-card.selected, .slot-card.active {
            border: 3px solid rgb(0, 0, 0); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .court-card img, .slot-card img {
            border-radius: 10px 10px 0 0;
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .court-card p, .slot-card p {
            margin: 0;
            padding: 0.5rem 0;
        }
        .hidden { 
            display: none; 
        } 
        .date-input { 
            width: 400px; 
        } 
        .pesan-button {
            background-color: rgb(0 , 0, 0);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            font-weight: bold;
            cursor: pointer;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            transition: background-color 0.3s;
        }
        .pesan-button:hover {
            background-color: rgb(14, 14, 14);
        }
        .status-section {
            margin-top: 20px;
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .welcome-section {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('gambar/bguser.jpeg') no-repeat center center;
            background-size: cover; 
            padding: 60px 20px; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); 
            color: #fff;
        }
        .welcome-section h2 {
            font-size: 2.5rem; 
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); 
        }
        .welcome-section p {
            font-size: 1.25rem; 
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); 
        }
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.5); 
        }
        .modal-content {
            background-color: #fefefe; 
            margin: 15% auto; 
            padding: 20px; 
            border: 1px solid #888; 
            width: 80%; 
            max-width: 500px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .close {
            color: #aaa; 
            float: right; 
            font-size: 28px; 
            font-weight: bold; 
        }
        .close:hover,
        .close:focus {
            color: black; 
            text-decoration: none; 
            cursor: pointer; 
        }
    </style> 
</head> 
<body> 
    <?php echo renderHeader($headerLogo); ?>
    <section class="mt-10 text-center welcome-section">
        <h2 class="font-bold text-white">Hai, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></h2>
        <p class="mt-2">Selamat datang di platform pemesanan lapangan futsal terbaik. Kami siap membantu Anda menemukan pengalaman bermain yang menyenangkan!</p>
    </section>

    <section class="mt-10" id="courtSelection">
        <div class="container mx-auto px-4">
            <div class="court-card p-6">
                <h3 class="text-2xl font-bold mb-4 text-center">Pilih lapangan anda</h3>
                <p class="text-gray-700 text-center">Silahkan pilih dimana lapangan mu</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"> 
                    <div class="court-card" onclick="selectCourt(this, 'A')"> 
                        <img src="gambar/lapanganA.jpg" alt="Lapangan A"> 
                        <div class="p-4"> 
                            <h4 class="text-xl font-semibold">Lapangan A</h4> 
                            <p class="text-gray-700">Rp. 150,000</p>
                            <p class="mt-2">Dilengkapi dengan tempat duduk, Ruang Ganti Baju, dan beberapa sepasang sepatu ganti tambahan.</p>
                            <div class="mt-4 ">
                                <p><strong>Jenis Lapangan:</strong> Vinyl (Indoor)</p>
                            </div>
                        </div> 
                    </div> 
                    <div class="court-card" onclick="selectCourt(this, 'B')"> 
                        <img src="gambar/lapanganB.jpg" alt="Lapangan B"> 
                        <div class="p-4"> 
                            <h4 class="text-xl font-semibold">Lapangan B</h4> 
                            <p class="text-gray-700">Rp. 120,000</p>
                            <p class="mt-2">Dilengkapi dengan fasilitas yang nyaman dan ruang ganti.</p>
                            <div class="mt-4">
                                <p><strong>Jenis Lapangan:</strong> Vinyl (Indoor)</p>
                            </div>
                        </div> 
                    </div> 
                    <div class="court-card" onclick="selectCourt(this, 'C')"> 
                        <img src="gambar/lapanganC.jpg" alt="Lapangan C"> 
                        <div class="p-4"> 
                            <h4 class="text-xl font-semibold">Lapangan C</h4> 
                            <p class="text-gray-700">Rp. 110,000</p>
                            <p class="mt-2">Lapangan berbahan rumput sintetis dengan fasilitas lengkap.</p>
                            <div class="mt-4">
                                <p><strong>Jenis Lapangan:</strong> Rumput Sintetis (Outdoor)</p>
                            </div>
                        </div> 
                    </div> 
                </div>
            </div>
        </div> 
    </section>

    <div class="mt-10" id="bookingSection">
        <div class="container mx-auto px-4">
            <div class="court-card p-6">
                <h3 class="text-2xl font-bold mb-4 text-center">Jadwalkan Waktu futsal anda</h3>
                <p class="text-gray-700 text-center">Pastikan anda sudah mengisi Jadwal sesuai hari bermain anda</p>
                <div class="flex justify-center items-center mb-4">
                    <input type="date" id="bookingDate" class="border border-gray-300 p-2 rounded date-input" onchange="setDate()"> 
                </div> 
                <div id="timeSlots" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <!-- Slot time will be generated dynamically based on selected court -->
                </div>
            </div>
        </div> 
    </div> 

    <button id="payButton" class="pesan-button hidden" onclick="confirmBooking()">Pesan</button>

    <!-- Modal for alerts -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modalMessage"></p>
        </div>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-500">
        <p>© 2024 Dibuat oleh Afftgrs. All rights reserved.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedCourt = null;
        let selectedSlots = []; 
        let courtPrice = 0;

        function selectCourt(card, courtId) {
            const cards = document.querySelectorAll('.court-card');

            if (selectedCourt === courtId) {
                card.classList.remove('selected');
                selectedCourt = null;
                selectedSlots = []; // Reset selected slots if court is deselected
                courtPrice = 0; // Reset price
            } else {
                cards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                selectedCourt = courtId;
                courtPrice = <?php echo json_encode($courtPrices); ?>[courtId]; // Set price based on court
                updateTimeSlots(); // Update time slots when a court is selected
            }
            checkBookingButtonVisibility();
        }

        function setDate() {
            const bookingDate = document.getElementById('bookingDate').value;
            if (selectedCourt && bookingDate) {
                updateTimeSlots(); // Show time slots if both court and date are selected
            }
        }

        function updateTimeSlots() {
            const timeSlotsContainer = document.getElementById('timeSlots');
            timeSlotsContainer.innerHTML = '';
            const slots = [
                "07:00 - 08:00",
                "08:00 - 09:00", "09:00 - 10:00", "10:00 - 11:00", "11:00 - 12:00",
                "12:00 - 13:00", "13:00 - 14:00", "14:00 - 15:00", "15:00 - 16:00",
                "16:00 - 17:00", "17:00 - 18:00", "18:00 - 19:00", "19:00 - 20:00",
                "20:00 - 21:00", "21:00 - 22:00", "22:00 - 23:00", "23:00 - 24:00"
            ];
            
            slots.forEach(slot => {
                const slotCard = document.createElement('div');
                slotCard.className = 'slot-card';
                slotCard.dataset.slot = slot; 
                slotCard.innerHTML = `
                    <p class="text-lg font-semibold">${slot}</p>
                    <p class="text-black">Rp. ${courtPrice.toLocaleString()}</p>
                    <p class="text-black">Tersedia</p>
                `;
                slotCard.onclick = function() {
                    selectSlot(slotCard);
                };
                timeSlotsContainer.appendChild(slotCard);
            });

            timeSlotsContainer.classList.remove('hidden'); // Ensure time slots are always visible
        }

        function selectSlot(slotCard) {
            if (selectedSlots.includes(slotCard.dataset.slot)) {
                slotCard.classList.remove('active');
                selectedSlots = selectedSlots.filter(slot => slot !== slotCard.dataset.slot);
            } else if (selectedSlots.length < 20) { // Allow up to 2 slots
                slotCard.classList.add('active');
                selectedSlots.push(slotCard.dataset.slot);
            } else {
                showModal("Anda hanya dapat memilih hingga 2 slot waktu.");
            }
            checkBookingButtonVisibility();
        }

        function checkBookingButtonVisibility() {
            const payButton = document.getElementById('payButton');
            const bookingDate = document.getElementById('bookingDate').value;
            if (selectedCourt && bookingDate && selectedSlots.length > 0) {
                payButton.classList.remove('hidden');
            } else {
                payButton.classList.add('hidden');
            }
        }
        
        function confirmBooking() {
            if (!selectedCourt || selectedSlots.length === 0) {
                showModal("Silakan pilih lapangan dan waktu terlebih dahulu.");
                return;
            }

            // Sort selected slots
            selectedSlots.sort((a, b) => {
                const startA = a.split(' - ')[0];
                const startB = b.split(' - ')[0];
                return startA.localeCompare(startB);
            });

            // Check for consecutiveness
            for (let i = 0; i < selectedSlots.length - 1; i++) {
                const endCurrent = selectedSlots[i].split(' - ')[1];
                const startNext = selectedSlots[i + 1].split(' - ')[0];

                // If the end time of the current slot is not equal to the start time of the next slot
                if (endCurrent !== startNext) {
                    showModal("Slot waktu yang dipilih harus jam yang bersandingan (misal 01.00-02.00 dan 02.00-01.00). Silakan pilih slot waktu yang berurutan.");
                    return;
                }
            }

            const bookingDetails = {
                court: selectedCourt,
                date: document.getElementById('bookingDate').value,
                slots: JSON.stringify(selectedSlots), // Convert slots to JSON string
                price: selectedSlots.length * courtPrice // Calculate total price based on selected slots
            };

            // Calculate start and end time based on selected slots
            const startTime = selectedSlots[0].split(' - ')[0];
            const endTime = selectedSlots[selectedSlots.length - 1].split(' - ')[1];

            bookingDetails.startTime = startTime;
            bookingDetails.endTime = endTime;

            const queryString = new URLSearchParams(bookingDetails).toString();
            window.location.href = `rincianBooking.php?${queryString}`;
        }

        function showModal(message) {
            document.getElementById('modalMessage').innerText = message;
            document.getElementById('alertModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('alertModal').style.display = 'none';
        }

        window.selectCourt = selectCourt;
        window.setDate = setDate;
        window.confirmBooking = confirmBooking;
        window.closeModal = closeModal;
    });
    </script>
</body>
</html>