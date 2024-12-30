<?php
session_start();
require 'config.php'; // Pastikan file config.php berisi konfigurasi database

$errors = [];
$success = '';
$full_name = '';
$email = '';
$phone = '';
$username = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (strlen($full_name) < 3) {
        $errors['full_name'] = 'Nama lengkap harus terdiri minimal 3 karakter!';
    }
    
    if (strlen($username) < 4) {
        $errors['username'] = 'Username harus terdiri minimal 4 karakter!';
    }
    
    if (strlen($password) < 6) {
        $errors['password'] = 'Password harus terdiri minimal 6 karakter!';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Confirm Password harus sama dengan Password!';
    }

    // Jika tidak ada kesalahan, lanjutkan ke penyimpanan data
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $pdo->beginTransaction();

        try {
            $role = 'user';
            $stmt = $pdo->prepare("INSERT INTO registrasi (nama, username, password, email, nohp, role, created_at) VALUES (:nama, :username, :password, :email, :nohp, :role, NOW())");
            $stmt->execute([
                'nama' => $full_name,
                'username' => $username,
                'password' => $hashed_password,
                'email' => $email,
                'nohp' => $phone,
                'role' => $role
            ]);

            $pdo->commit();
            $success = 'Registrasi berhasil!'; 
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Registrasi gagal! ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Gaya untuk pop-up */
        #successNotificationContainer {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            text-align: center;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: none; /* Default hidden */
            animation: fadeIn 0.5s forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .icon {
            font-size: 60px; /* Ukuran ikon lebih besar */
            color: #4CAF50; /* Warna hijau */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div id="successNotificationContainer">
        <i class="fas fa-check-circle icon"></i>
        <p class="text-green-500 font-bold text-lg mt-4">Berhasil Registrasi</p>
        <p class="text-gray-700">Username: <strong><?php echo htmlspecialchars($username); ?></strong> berhasil ditambahkan.</p>
    </div>
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden"> <!-- Container -->
        <!-- Header Section -->
        <div class="bg-white text-black p-6 border-b border-gray-300">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-user-plus text-black mr-2 text-xl"></i>
                <h1 class="text-2xl font-bold">Registrasi Akun</h1>
            </div>
            <div class="text-center">
                <p class="text-gray-600">Silakan isi form di bawah untuk mendaftar.</p>
                <p class="text-gray-600">Pastikan semua informasi yang Anda masukkan adalah benar.</p>
                <?php if ($success): ?>
                    <script>
                        document.getElementById('successNotificationContainer').style.display = 'block';
                        setTimeout(() => {
                            window.location.href = 'login.php'; // Redirect ke halaman login setelah 5 detik
                        }, 1000); // Delay sebelum redirect
                    </script>
                <?php endif; ?>
                <?php if (isset($errors['general'])): ?>
                    <p class="text-red-500 text-sm"><?php echo $errors['general']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Form Section -->
        <div class="p-8">
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Nama Lengkap</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="text" name="full_name" placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($full_name); ?>" required>
                    <?php if (isset($errors['full_name'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['full_name']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Email</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Nomor HP</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="text" name="phone" placeholder="Nomor HP" value="<?php echo htmlspecialchars($phone); ?>" required>
                    <?php if (isset($errors['phone'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['phone']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Username</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['username']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Password</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="password" name="password" placeholder="********" required>
                    <?php if (isset($errors['password'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Confirm Password</label>
                    <input class="border border-gray-300 rounded-lg w-full py-3 pl-4 focus:outline-none focus:ring-2 focus:ring-black transition duration-200" type="password" name="confirm_password" placeholder="********" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <p class="text-red-500 text-sm"><?php echo $errors['confirm_password']; ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-black text-white font-semibold rounded-lg w-full py-3 hover:bg-black-800 transition duration-200 shadow-lg transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i> Daftar
                </button>
            </form>
            <div class="text-center mt-4">
                <p class="text-gray-600">Sudah punya akun? <a href="login.php" class="text-black hover:underline">Login Saja</a></p>
                <p class="text-gray-600">Atau <a href="index.php" class="text-black hover:underline">Kembali ke Beranda</a></p>
            </div>
            <footer class="text-center text-gray-500 text-sm mt-4">
                © 2024 Dibuat oleh Afftgrs. All rights reserved.
            </footer>
        </div>
    </div>
</body>
</html>