<?php
session_start();
require 'config.php'; // Connect to the database

$login_error = ""; // Variable to store error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare query to fetch user data based on username
    $stmt = $pdo->prepare("SELECT * FROM registrasi WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Check if user exists and password matches
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // Store user ID in session
        $_SESSION['role'] = $user['role']; // Store user role in session
        $_SESSION['username'] = $user['username']; // Store username in session
        $_SESSION['login_success'] = "Berhasil Login, Selamat datang " . $user['username'] . "!"; // Store success message

        // Redirect to the same page to handle the success message
        header("Location: login.php");
        exit();
    } else {
        $login_error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Notification box style */
        #successNotificationContainer {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background-color: white; /* White background */
            color: black; /* Text color */
            padding: 30px 40px; /* Increased padding */
            border-radius: 12px; /* More rounded corners */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            display: none; /* Initially hidden */
            text-align: center; /* Center text */
            animation: fadeIn 0.5s; /* Animation for showing */
            width: 300px; /* Fixed width for consistency */
        }
        .visible {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .icon {
            font-size: 60px; /* Larger icon */
            color: #4CAF50; /* Green color */
        }
    </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">
    <div id="successNotificationContainer" class="hidden">
        <i class="fas fa-check-circle icon"></i>
        <p id="welcomeMessage" class="font-bold text-lg"></p>
    </div>

    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden"> <!-- Container -->
        <!-- Header Section -->
        <div class="bg-white text-black p-6 border-b border-gray-300">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-sign-in-alt text-black mr-2 text-xl"></i>
                <h1 class="text-2xl font-bold">Login untuk Masuk</h1>
            </div>
            <div class="text-center">
                <p class="text-gray-600">Masuk untuk booking lapangan anda</p>
            </div>
        </div>
        <!-- Form Section -->
        <div class="p-8">
            <!-- Display error message if exists -->
            <div class="text-red-500 text-sm text-center mb-4 <?php echo $login_error ? 'block' : 'hidden'; ?>">
                <?php echo $login_error; ?>
            </div>
            <form id="loginForm" action="login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-semibold">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" id="username" placeholder="Username" required class="border border-gray-300 rounded-lg w-full py-3 pl-10 focus:outline-none focus:ring-2 focus:ring-black transition duration-200">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-semibold">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" id="password" placeholder="Password" required class="border border-gray-300 rounded-lg w-full py-3 pl-10 focus:outline-none focus:ring-2 focus:ring-black transition duration-200">
                    </div>
                </div>
                <button type="submit" class="bg-black text-white font-semibold rounded-lg w-full py-3 hover:bg-black-800 transition duration-200 shadow-lg transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                </button>
            </form>
            <div class="text-center mt-4">
                <p class="text-gray-600">Belum punya akun? <a href="register.php" class="text-black hover:underline">Daftar Sekarang</a></p>
            </div>
            <div class="text-center mt-4">
                <a href="index.php" class="text-black hover:underline">Kembali ke Beranda</a>
            </div>
            <footer class="text-center text-gray-500 text-sm mt-4">
                © 2024 Dibuat oleh Afftgrs. All rights reserved.
            </footer>
        </div>
    </div>

    <?php
    // Display success message if exists
    if (isset($_SESSION['login_success'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const welcomeMessage = document.getElementById('welcomeMessage');
                const notificationContainer = document.getElementById('successNotificationContainer');

                // Set welcome message
                welcomeMessage.innerText = '" . $_SESSION['login_success'] . "';
                notificationContainer.classList.add('visible');

                // Show the notification
                notificationContainer.style.display = 'block';

                // Auto-hide after 2 seconds and redirect
                setTimeout(() => {
                    notificationContainer.style.display = 'none';
                    // Redirect based on user role after showing the notification
                    window.location.href = '" . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'user.php') . "';
                }, 1000); // Delay for redirect
            });
        </script>";
        unset($_SESSION['login_success']); // Clear message after displaying
    }
    ?>
</body>
</html>