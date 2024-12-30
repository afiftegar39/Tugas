<?php
session_start();
$pageTitle = "Pesan Lapangan Futsal";
$headerLogo = "gambar/logogirifutsal.png";

function renderHeader($logo) {
    $navItems = [
        ['href' => '#service', 'text' => 'Pelayanan'],
        ['href' => '#maps', 'text' => 'Peta'],
        ['href' => '#contact', 'text' => 'Hubungi'],
    ];

    $loginLogoutItem = isset($_SESSION['user_id']) ?
        ['href' => '../logout.php', 'icon' => 'fas fa-sign-out-alt', 'text' => 'Logout'] :
        ['href' => '../login.php', 'icon' => 'fas fa-sign-in-alt', 'text' => 'Login'];

    $languageSelector = '<div class="flex items-center ml-4 border-l border-gray-500 pl-4">
        <select id="languageSelect" class="bg-transparent text-black p-2">
            <option value="id">ID</option>
            <option value="en">EN</option>
        </select>
    </div>';

    $navLinks = implode("", array_map(function($item) {
        return "<li class='nav-menu'><a href='{$item['href']}' class='text-black hover:text-orange-300 transition duration-300'>{$item['text']}</a></li>";
    }, $navItems));

    return <<<HTML
    <header id="navbar" class="flex justify-between items-center p-4 bg-transparent fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="flex items-center" id="logo-container">
            <img alt='Logo' src='$logo' class="h-10" />
            <span id="logo-text" class="text-2xl font-bold text-black ml-2">Girifutsal</span>
        </div>
        <nav class="flex-grow">
            <ul class="flex justify-center space-x-8">$navLinks</ul>
        </nav>
        <div class="flex items-center">
            <div class="bg-black text-white p-2 rounded-lg shadow-md hover:bg-gray-800 transition duration-300">
                <a href="{$loginLogoutItem['href']}" class="flex items-center small-button">
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
            color: #000; /* Set text color to black */
        }
        h1, h2, h3 {
            font-family: 'Poppins', sans-serif;
            color: #000; /* Ensure headings are also black */
        }
        html {
            scroll-behavior: smooth; 
        }
        #navbar {
            transition: background-color 0.3s;
            padding: 0.5rem 1rem; 
        }
        .navbar-scrolled {
            background-color: white; 
        }
        .bg-landing {
            background: url('gambar/lpage4.jpeg') no-repeat center center; 
            background-size: cover;
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            position: relative; 
            margin-top: 30rem;
        }
        .overlay { 
            width: 100%; 
            height: 100%; 
            position: absolute; 
            display: flex;
            flex-direction : column; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
        }
        footer { 
            background-color: #000; 
            color: #fff ; 
            padding: 2rem 1rem; 
        }
        footer h2 { 
            font-size: 1.5rem;
            color : #fff;  
        }
        .section { 
            margin-top: 3rem; 
            padding: 2rem; 
            background-color: #fff; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
        }
        .court-card {
            border: 1px solid #e0e0e0; 
            border-radius: 10px; 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .court-card:hover {
            transform: scale(1.05); 
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); 
        }
        .fade-in {
            opacity: 0;
            transform: translateY(20px); 
            transition: opacity 1s ease, transform 4s ease; 
        }
        .fade-in.show {
            opacity: 1;
            transform: translateY(0); 
        }
        .fade-out {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 1s ease, transform 2s ease;
        }
        .fade-out.hide {
            opacity: 0;
            transform: translateY(-20px);
        }
        .small-button {
            font-size: 0.65rem; 
            padding: 0.25rem 0.5rem; 
        }
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .scroll-to-top:hover {
            background-color: #444;
        }
        .landing-image {
            width: 100%; 
            max-width: 400px; 
            border-radius: 50px 0 0 50px; /* Left side rounded */
        }
        .nav-menu {
            position: relative;
            display: inline-block;
        }
        .nav-menu a {
            position: relative;
            z-index: 1;
        }
        .nav-menu::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -5px; /* Adjust this value to position the line */
            width: 0;
            height: 2px; /* Thickness of the line */
            background-color: black; /* Color of the line */
            transition: width 0.3s ease, left 0.3s ease;
        }
        .nav-menu:hover::after {
            width: 100%; /* Full width on hover */
            left: 0; /* Move to the left */
        }
        .button-active {
            background-color: black; /* Warna hitam saat aktif */
            color: white; /* Teks berwarna putih */
        }
        .button-inactive {
            background-color: white; /* Warna putih saat tidak aktif */
            color: black; /* Teks berwarna hitam */
            border: 1px solid black; /* Border hitam */
            cursor: not-allowed; /* Menunjukkan bahwa tombol tidak dapat diklik */
        }
    </style>
</head>
<body>
    <?php echo renderHeader($headerLogo); ?>

    <div class="container mx-auto px-4 mt-24">
        <section class="flex flex-col md:flex-row items-center mt-10 fade-in">
            <div class="md:w-1/2 rounded-l-full overflow-hidden"> <!-- Rounded corners on the left -->
                <img alt="Orang sedang bermain futsal di lapangan Paragon" class="landing-image" height="400" src="gambar/landingindex.jpeg" />
            </div>
            <div class="md:w-1/2 flex flex-col justify-center p-6"> <!-- Right side content -->
                <h1 class="text-5xl font-bold leading-tight text-center">Mudah Booking Lapangan Futsal di Girifutsal</h1>
                <p class="text-lg mt-4 text-center">Pesan lapangan futsal dengan mudah dan cepat. Pilih jadwal, bayar online, dan nikmati permainan Anda tanpa repot.</p>
                <button id="nextButton" class="button-inactive text-sm md:text-base lg:text-lg py-2 px-4 rounded mt-4 ml-2" disabled><i class="fas fa-futbol mr-2"></i> Login untuk Mulai Booking</button>
            </div>
        </section>
        
        <section class="mt-20 fade-in mt-24">
            <div class="flex flex-wrap justify-center mt-10">
                <div id="service" class="w-full md:w-1/3 p-4">
                    <div class="court-card p-6">
                        <h3 class="text-2xl font-bold mb-4 text-center">Pemesanan Online</h3>
                        <p class="text-gray-700 text-center">Pesan lapangan futsal dengan mudah melalui platform online kami.</p>
                    </div>
                </div>
                <div class="w-full md:w-1/3 p-4">
                    <div class="court-card p-6">
                        <h3 class="text-2xl font-bold mb-4 text-center">Pembayaran Aman</h3>
                        <p class="text-gray-700 text-center">Nikmati kemudahan pembayaran dengan sistem yang aman dan terpercaya.</p>
                    </div>
                </div>
                <div class="w-full md:w-1/3 p-4">
                    <div class="court-card p-6">
                        <h3 class="text-2xl font-bold mb-4 text-center">Layanan Pelanggan</h3>
                        <p class="text-gray-700 text-center">Tim kami siap membantu Anda dengan layanan pelanggan yang responsif.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center min-h-screen p-4 fade-in">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col items-center">
                    <img alt="Lapangan futsal" class="rounded-lg shadow-lg" height="300" src="gambar/gambar1.jpeg" width="400"/>
                </div>
                <div class="flex flex-col justify-center">
                    <h2 class="text-sm font-semibold text-gray-500 mb-2 text-center">APA ITU GIRIFUTSAL?</h2>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Tentang Girifutsal</h1>
                    <p class="text-lg text-gray-700 mb-4 text-center">
                        Giri Futsal adalah tempat terbaik untuk bermain futsal di kota ini. Dengan fasilitas modern dan lapangan berkualitas tinggi, kami menawarkan pengalaman bermain yang tak terlupakan.
                    </p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center min-h-screen p-4 fade-in">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col justify-center">
                    <h2 class="text-sm font-semibold text-gray-500 mb-2 text-center">KENAPA HARUS GIRIFUTSAL?</h2>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Keunggulan Girifutsal</h1>
                    <p class="text-lg text-gray-700 mb-4 text-center">
                        Dengan sistem pemesanan yang mudah dan cepat, Girifutsal memberikan kemudahan bagi Anda untuk mendapatkan lapangan sesuai kebutuhan. Nikmati juga layanan pelanggan yang siap membantu Anda kapan saja.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <img alt="Lapangan futsal" class="rounded-lg shadow-lg" height="300" src="gambar/gambar2.jpeg" width="400"/>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center min-h-screen p-4 fade-in">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <div ```php
                class="flex flex-col items-center">
                    <img alt="Lapangan futsal" class="rounded-lg shadow-lg" height="300" src="gambar/gambar3.jpeg" width="400"/>
                </div>
                <div class="flex flex-col justify-center">
                    <h2 class="text-sm font-semibold text-gray-500 mb-2 text-center">GASS BOOKING LAPANGAN GIRIFUTSAL!</h2>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Ayo Segera Booking!</h1>
                    <p class="text-lg text-gray-700 mb-4 text-center">
                        Jangan lewatkan kesempatan untuk bermain di lapangan futsal terbaik. Segera lakukan reservasi dan nikmati permainan bersama teman-teman Anda di Girifutsal!
                    </p>
                </div>
            </div>
        </section>

        <section class="py-8 fade-in">
            <div class="container mx-auto px-4">
                <h2 id="maps" class="text-2xl font-semibold text-center mb-6">Peta Lokasi</h2>
                <div class="w-full h-96 md:h-[600px] lg:h-[800px]">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15812.472451777163!2d110.341295!3d-7.777299!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a580d478f30cd%3A0x936332fb1001af32!2sParagon%20Futsal!5e0!3m2!1sen!2sau!4v1731780853006!5m2!1sen!2sau"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>
        </section>
    </div>

    <footer id="contact">
        <div class="text-white py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start text-center md:text-left">
                    <div class="flex flex-col mb-6 md:mb-0">
                        <h2 id="contact" class="text-xl font-semibold mb-2">Hubungi Kami</h2>
                        <p class="text-sm md:text-base mb-1" id="phone">Telepon: <span class="text-gray-400">+62 085803332284</span></p>
                        <p class="text-sm md:text-base" id="email">Email: <span class="text-gray-400">admingirifutsal123@gmail.com</span></p>
                    </div>

                    <div class="flex flex-col mb-6 md:mb-0">
                        <h2 class="text-xl font-semibold mb-2">Ikuti Kami</h2>
                        <div class="flex justify-center md:justify-start space-x-4">
                            <a href="#" class="text-white hover:text-red-400 transition duration-300"><i class="fab fa-instagram fa-lg"></i></a>
                            <a href="#" class="text-white hover:text-green-400 transition duration-300"><i class="fab fa-whatsapp fa-lg"></i></a>
                            <a href="#" class="text-white hover:text-blue-400 transition duration-300"><i class="fab fa-facebook-f fa-lg"></i></a>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4 text-sm">
                    <p> © 2024 Dibuat oleh Afftgrs. All rights reserved.</p>
                </div>
            </div>
        ```html
        </div>
    </footer>
    <button class="scroll-to-top" id="scrollToTopBtn"><i class="fas fa-chevron-up"></i></button>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const navbar = document.getElementById("navbar");
            const scrollToTopBtn = document.getElementById("scrollToTopBtn");
            const nextButton = document.getElementById("nextButton");

            // Fade-in effect for sections
            const fadeInElements = document.querySelectorAll('.fade-in');
            const options = {
                root: null,
                threshold: 0.1,
                rootMargin: '0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        entry.target.classList.remove('hide');
                    } else {
                        entry.target.classList.add('hide');
                        entry.target.classList.remove('show');
                    }
                });
            }, options);

            fadeInElements.forEach(element => {
                observer.observe(element);
            });

            // Scroll event for navbar and scroll-to-top button
            window.addEventListener("scroll", function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > 100) {
                    navbar.classList.add("navbar-scrolled");
                    scrollToTopBtn.style.display = "flex"; // Show button
                } else {
                    navbar.classList.remove("navbar-scrolled");
                    scrollToTopBtn.style.display = "none"; // Hide button
                }
            });

            scrollToTopBtn.addEventListener("click", function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Event listener for booking button
            const bookingButton = document.getElementById("bookingButton");
            bookingButton.addEventListener("click", function() {
                // Simulasi penyimpanan data booking
                const bookingData = {
                    userId: sessionStorage.getItem('user_id'), // Contoh mengambil user_id dari sessionStorage
                    courtId: 'court_1', // Ganti dengan ID lapangan yang dipilih
                    bookingTime: new Date().toISOString() // Waktu booking
                };

                // Simpan data booking ke localStorage
                localStorage.setItem('bookingData', JSON.stringify(bookingData));

                // Arahkan pengguna ke halaman konfirmasi booking
                window.location.href = 'konfirmasi_booking.php'; // Ganti dengan halaman konfirmasi booking yang sesuai

                // Mengubah status tombol "Selanjutnya"
                nextButton.classList.remove('button-inactive');
                nextButton.classList.add('button-active');
                nextButton.disabled = false; // Mengaktifkan tombol
            });

            // Event listener untuk tombol Kembali
            const backButton = document.getElementById("backButton");
            backButton.addEventListener("click", function() {
                // Arahkan pengguna kembali ke halaman pemilihan lapangan
                window.location.href = 'pemilihan_lapangan.php'; // Ganti dengan halaman pemilihan lapangan yang sesuai
            });
        });
    </script>
</body>
</html>