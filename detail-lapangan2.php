<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        .carousel {
            height: 500px;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
        }
        .carousel img {
            scroll-snap-align: center;
            width: 100%;
            height: auto;
            object-fit: contain;
        }
        .carousel::-webkit-scrollbar {
            width: 8px;
        }
        .carousel::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }
        .carousel::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }
        .booking-button {
            background-color: white;
            border: 2px solid black;
            color: black;
            padding: 10px 20px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .booking-button:hover {
            background-color: black;
            color: white;
        }
        nav ul li {
            position: relative;
            font-weight: bold; /* Bold untuk teks navbar */
        }
        nav ul li::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -5px;
            height: 2px;
            background-color: black;
            width: 0;
            transition: width 0.3s ease, left 0.3s ease;
        }
        nav ul li:hover::after {
            width: 100%;
            left: 0;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px; /* Jarak antara navbar dan konten */
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="flex justify-between items-center p-4 bg-white shadow-md fixed top-0 left-0 right-0 z-50"> 
        <img alt='Paragon logo' src="gambar/logo.png" class="h-12" /> 
        <nav class="hidden md:flex"> 
            <ul class="flex space-x-8"> 
                <li><a href='index.php' class="text-black hover:text-gray-700 transition duration-300">Home</a></li>
            </ul> 
        </nav> 
        <div class="block md:hidden"> 
            <button id="menu-toggle" class="text-black focus:outline-none"> 
                <i class="fas fa-bars"></i> 
            </button> 
            <ul id="mobile-menu" class="absolute right-0 bg-black shadow-lg mt-2 rounded-lg hidden"> 
                <li><a href='index.php' class="text-white hover:text-red-400 transition duration-300">Home</a></li> 
            </ul> 
        </div> 
    </header>

    <div class="container mx-auto p-4 mt-16">
        <div class="flex">
            <div class="w-2/3">
                <div class="carousel">
                    <img src="https://storage.googleapis.com/a1aa/image/6Qk11bzN3xZYLpZ7GZ3ntrMiIejvJjIXpfOU6KXfr9vhqAonA.jpg" alt="Indoor sports court with wooden flooring" class="w-full mb-4 selected">
                    <img src="https://storage.googleapis.com/a1aa/image/pOUNIds5wk5GGNLueMgfgD9LDuLcZ6OPnXzTAPmsXtUSVA0TA.jpg" alt="Outdoor sports field with goal post" class="w-full mb-4">
                    <img src="https://storage.googleapis.com/a1aa/image/j6ZJ4jWrosalBNC5tHLi0PHzsOhcBPP5YmOmEBhXVezpKA6JA.jpg" alt="Outdoor sports field at sunset" class="w-full mb-4">
                </div>
            </div>
            <div class="w-1/3 pl-4">
                <div class="card">
                    <h2 class="text-xl font-bold">Lapangan C.</h2>
                    <p class="text-green-600 text-lg font-semibold">Rp. 100,000</p>
                    <p class="mt-4">Dilengkapi dengan tempat duduk penonton, toilet, dan beberapa pasang sepatu futsal cadangan.</p>
                    <div class="mt-4">
                        <p><strong>Jenis Lapangan</strong>: Semen</p>
                        <p><strong>Lokasi</strong>: Outdoor</p>
                    </div>
                    <div class="mt-6">
                        <a href="booking.php" class="booking-button">Booking Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menu-toggle').onclick = function() {
            var mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        };
    </script>
</body>
</html>