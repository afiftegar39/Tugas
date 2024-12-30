<?php 
include 'config.php';
session_start();

// Get user role from session
$userRole = $_SESSION['role'] ?? 'guest';

// Function to fetch all records
function fetchAll($pdo, $query, $params = []) { 
    $stmt = $pdo->prepare($query); 
    $stmt->execute($params); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC); 
}

// Function to set notification
function setNotification($message, $type = 'success') { 
    $_SESSION['notification'] = $message; 
    $_SESSION['notification_type'] = $type; 
}

// Function to check if time slot is available
function isTimeSlotAvailable($pdo, $courtId, $startTime, $endTime, $rentalDate) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rental WHERE court_id = ? AND rental_date = ? AND 
                            ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
    $stmt->execute([$courtId, $rentalDate, $endTime, $startTime, $startTime, $endTime]);
    return $stmt->fetchColumn() == 0; // Return true if no overlapping bookings
}

try {
    $admins = $users = $rentals = []; 
    $bookingRequests = []; 

    // Fetch admins only if user is admin
    if ($userRole === 'admin') { 
        $admins = fetchAll($pdo, "SELECT * FROM registrasi WHERE role = 'admin'"); 
    }

    // Fetch all users and rentals
    $users = fetchAll($pdo, "SELECT * FROM registrasi WHERE role = 'user'"); 
    $rentals = fetchAll($pdo, "SELECT * FROM rental"); 
    $bookingRequests = fetchAll($pdo, "SELECT * FROM booking_requests"); 

    // Revenue calculations
    $today = date('Y-m-d'); 
    $startOfMonth = date('Y-m-01'); 
    $startOfYear = date('Y-01-01'); 

    // Calculate today's, this month's, and this year's revenue
    $todayRevenue = fetchAll($pdo, "SELECT SUM(total_price) AS total FROM rental WHERE rental_date = ?", [$today])[0]['total'] ?: 0; 
    $monthRevenue = fetchAll($pdo, "SELECT SUM(total_price) AS total FROM rental WHERE rental_date >= ?", [$startOfMonth])[0]['total'] ?: 0; 
    $yearRevenue = fetchAll($pdo, "SELECT SUM(total_price) AS total FROM rental WHERE rental_date >= ?", [$startOfYear])[0]['total'] ?: 0; 

    // Confirm booking request
    if (isset($_POST['confirm_request'])) { 
        // Fetch the username based on the customer name
        $customerName = fetchAll($pdo, "SELECT username FROM registrasi WHERE nama = ?", [$_POST['customer_name']]); 
        $username = $customerName[0]['username'] ?? null; 

        if ($username) { 
            // Ambil detail booking request
            $bookingRequest = fetchAll($pdo, "SELECT * FROM booking_requests WHERE id = ?", [$_POST['request_id']]);
            
            if (!empty($bookingRequest)) {
                // Ambil nilai dari booking request
                $courtId = $bookingRequest[0]['court_id'] ?? null; // Gunakan null coalescing operator
                $startTime = $bookingRequest[0]['start_time'] ?? null;
                $endTime = $bookingRequest[0]['end_time'] ?? null;
                $rentalDate = $bookingRequest[0]['request_date'] ?? null; // Pastikan ini sesuai dengan kolom yang ada
                $invoiceCode = $bookingRequest[0]['invoice_code'] ?? null; // Ambil invoice_code
                $customerName = $bookingRequest[0]['customer_name'] ?? null; // Ambil customer_name
                $totalPrice = $bookingRequest[0]['price'] ?? null; // Ambil total price
                
                // Debugging: Log the values being inserted
                error_log("Inserting into rental: username = $username, request_id = " . $_POST['request_id']);
                
                // Prepare the SQL statement for insertion
                $stmt = $pdo->prepare("INSERT INTO rental (invoice_code, customer_name, rental_date, total_price, court_id, username, status) 
                                        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')"); 
                if ($stmt->execute([$invoiceCode, $customerName, $rentalDate, $totalPrice, $courtId, $username])) { 
                    // Debugging: Log success
                    error_log("Successfully inserted into rental.");
                    
                    // Delete booking request after confirmation
                    $stmt = $pdo->prepare("DELETE FROM booking_requests WHERE id = ?"); 
                    if ($stmt->execute([$_POST['request_id']])) { 
                        setNotification('Booking request confirmed!', 'success'); 
                    } else { 
                        setNotification('Failed to delete booking request.', 'error'); 
                    } 
                } else { 
                    // Debugging: Log failure
                    error_log("Failed to insert into rental: " . implode(", ", $stmt->errorInfo()));
                    setNotification('Failed to confirm booking request.', 'error'); 
                } 
            } else {
                setNotification('Booking request not found.', 'error');
            }
        } else { 
            setNotification('Username not found for the customer.', 'error'); 
        } 
        header("Location: admin.php"); 
        exit; 
    }

    // Reject booking request
    if (isset($_POST['reject_request'])) { 
        $stmt = $pdo->prepare("DELETE FROM booking_requests WHERE id = ?"); 
        if ($stmt->execute([$_POST['request_id']])) { 
            setNotification('Booking request rejected!', 'success'); 
        } else { 
            setNotification('Failed to reject booking request.', 'error'); 
        } 
        header("Location: admin.php"); 
        exit; 
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
        // Add admin
        if (isset($_POST['tambah_admin'])) { 
            if (empty($_POST['nama']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['nohp']) || $_POST['password'] !== $_POST['confirm_password']) { 
                setNotification('Please fill all fields correctly.', 'error'); 
            } else { 
                $stmt = $pdo->prepare("INSERT INTO registrasi (nama, username, password, email , nohp, role) VALUES (?, ?, ?, ?, ?, 'admin')"); 
                $stmt->execute([$_POST['nama'], $_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['email'], $_POST['nohp']]); 
                setNotification('Admin berhasil ditambahkan!', 'success'); 
            } 
            header("Location: admin.php"); 
            exit; 
        }

        // Add user
        if (isset($_POST['tambah_user'])) { 
            if (empty($_POST['user_nama']) || empty($_POST['user_username']) || empty($_POST['user_password']) || empty($_POST['user_email']) || empty($_POST['user_nohp']) || $_POST['user_password'] !== $_POST['user_confirm_password']) { 
                setNotification('Please fill all fields correctly.', 'error'); 
            } else { 
                $stmt = $pdo->prepare("INSERT INTO registrasi (nama, username, password, email, nohp, role) VALUES (?, ?, ?, ?, ?, 'user')"); 
                $stmt->execute([$_POST['user_nama'], $_POST['user_username'], password_hash($_POST['user_password'], PASSWORD_DEFAULT), $_POST['user_email'], $_POST['user_nohp']]); 
                setNotification('User  berhasil ditambahkan!', 'success'); 
            } 
            header("Location: admin.php"); 
            exit; 
        }

        // Edit user
        if (isset($_POST['edit_user'])) { 
            $stmt = $pdo->prepare("UPDATE registrasi SET nama = ?, username = ?, email = ?, nohp = ? WHERE id = ?"); 
            $stmt->execute([$_POST['edit_user_nama'], $_POST['edit_user_username'], $_POST['edit_user_email'], $_POST['edit_user_nohp'], $_POST['id']]); 
            setNotification('Data pengguna berhasil diperbarui!', 'success'); 
            header("Location: admin.php"); 
            exit; 
        }

        // Delete user
        if (isset($_POST['delete_user'])) { 
            $stmt = $pdo->prepare("DELETE FROM registrasi WHERE id = ?"); 
            $stmt->execute([$_POST['id']]); 
            setNotification('Data berhasil dihapus!', 'success'); 
            header("Location: admin.php"); 
            exit; 
        }

        // Make user admin
        if (isset($_POST['make_admin'])) { 
            $stmt = $pdo->prepare("UPDATE registrasi SET role = 'admin' WHERE id = ?"); 
            $stmt->execute([$_POST['id']]); 
            setNotification('Pengguna berhasil dijadikan admin!', 'success'); 
            header("Location: admin.php"); 
            exit; 
        }

        // Delete rental
        if (isset($_POST['delete_rental'])) {
            $stmt = $pdo->prepare("DELETE FROM rental WHERE id = ?");
            if ($stmt->execute([$_POST['id']])) {
                setNotification('Rental data successfully deleted!', 'success');
            } else {
                setNotification('Failed to delete rental data.', 'error');
            }
            header("Location: admin.php");
            exit;
        }

        // Edit rental
        if (isset($_POST['edit_rental'])) {
            $stmt = $pdo->prepare("UPDATE rental SET customer_name = ?, rental_date = ?, total_price = ? WHERE id = ?");
            $stmt->execute([$_POST['edit_customer_name'], $_POST['edit_rental_date'], $_POST['edit_total_price'], $_POST['id']]);
            setNotification('Rental data successfully updated!', 'success');
            header("Location: admin.php");
            exit;
        }
    }

    // Display notification if exists
    $notificationMessage = $_SESSION['notification'] ?? ''; 
    $notificationType = $_SESSION['notification_type'] ?? ''; 
    unset($_SESSION['notification'], $_SESSION['notification_type']); // Clear notification after displaying 

} catch (PDOException $e) { 
    echo "Error: " . $e->getMessage(); 
} 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="utf-8" /> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> 
    <title>Admin Panel</title> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> 
    <style> 
        body { 
            background-color: #ffffff;
            font-family: 'Arial', sans-serif; 
            color: #333; 
        } 
        .sidebar { 
            width: 250px; 
            background-color: rgb(0, 0, 0); 
            height: 100vh; 
            position: fixed; 
            transition: width 0.3s; 
        } 
        .sidebar img { 
            width: 50px; 
            height: auto; 
            object-fit: contain; 
        } 
        .sidebar a { 
            color: white; 
            padding: 15px; 
            display: block; 
            text-decoration: none; 
            transition: background-color 0.3s, transform 0.2s; 
        } 
        .sidebar a:hover { 
            background-color: rgba(255, 255, 255, 0.8); 
            transform: scale(1.05); 
            color: black; 
        } 
        .content { 
            margin-left: 250px; 
            padding: 20px; 
        } 
        .card { 
            background: white; 
            border-radius: 8px; 
            padding: 20px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            margin-bottom: 20px; 
            transition: transform 0.2s; 
        } 
        .stat-card { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 20px; 
            border-radius: 8px; 
            background: #f7fafc; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        } 
        .recent-activity { 
            background: white; 
            border-radius: 8px; 
            padding: 20px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        } 
        .search-bar { 
            margin-bottom: 20px; 
        } 
        .search-bar input { 
            padding: 10px; 
            border-radius: 5px; 
            border: 1px solid #ccc; 
            width: 100%; 
        } 
        .table-header { 
            cursor: pointer; 
        } 
        .table-header:hover { 
            background-color: #edf2f7; 
        } 
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        } 
        .form-button { 
            background-color: rgb(0, 0, 0); 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s, transform 0.2s; 
        } 
        .form-button:hover { 
            background-color: rgba(0, 0, 0, 0.8); 
            color: white; 
        } 
        .form-button.text-red-500:hover { 
            background-color: rgba(255, 0, 0, 0.8); 
        } 
        .form-button.text-hijau-500:hover { 
            background-color: rgba(4, 169, 7, 0.8); 
        } 
        .form-button.text-blue-500:hover { 
            background-color: rgba(20, 5, 183, 0.8); 
        } 
        .form-button.text-orange-500:hover { 
            background-color: rgba(255, 213, 0, 0.8); 
        } 
        .form-button:disabled { 
            background-color: #a0aec0; 
            cursor: not-allowed; 
        } 
        .fade-in { 
            animation: fadeIn 0.5s ease-in-out; 
        } 
        @keyframes fadeIn { 
            from { 
                opacity: 0; 
            } 
            to { 
                opacity: 1; 
            } 
        } 
        .form-input { 
            border: 1px solid #cbd5e0; 
            border-radius: 8px; 
            padding: 10px 15px; 
            width: 100%; 
            transition: border-color 0.3s, box-shadow 0.3s; 
            font-size: 14px; 
            outline: none; 
        } 
        .form-input:focus { 
            border-color: rgb(0, 0, 0); 
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); 
        } 
        .form-input::placeholder { 
            color: #a0aec0; 
            opacity: 1; 
        } 
        .alert { 
            background-color: #d4edda; 
            color: #155724; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        } 
    </style> 
</head> 
<body> 
    <div class="sidebar"> 
        <div class="flex items-center justify-center py-4"> 
            <img src="gambar/logogirifutsal.png" alt="Logo" class="mr-2"> 
            <h2 class="text-white text-xl">Girifutsal</h2> 
        </div> 
        <div class="sidebar"> 
            <a href="#" onclick="showContent('dashboard')"> 
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard 
            </a> 
            <a href="#" onclick="showContent('admins')"> 
                <i class="fas fa-user-shield mr-2"></i> Admin 
            </a> 
            <a href="#" onclick="showContent('users')"> 
                <i class="fas fa-users mr-2"></i> User 
            </a> 
            <a href="#" onclick="showContent('dataRental')"> 
                <i class="fas fa-store mr-2"></i> Data Rental 
            </a> 
            <a href="logout.php" class="text-red-500"> 
                <i class="fas fa-sign-out-alt mr-2"></i> Logout 
            </a> 
        </div> 
    </div>

    <div class="content">
        <div id="dashboard" class="content-section content-active fade-in">
            <h2 class="text-xl font-bold mb-4"> Dashboard</h2>
            <p class="mb-4">Welcome to the admin dashboard!</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat-card">
                    <div>
                        <p class="text-gray-700 font-bold">Total Users</p>
                        <p class="text-gray-600"><?= count($users) ?></p>
                    </div>
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card">
                    <div>
                        <p class="text-gray-700 font-bold">Total Rentals</p>
                        <p class="text-gray-600"><?= count($rentals) ?></p>
                    </div>
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-card">
                    <div>
                        <p class="text-gray-700 font-bold">Total Revenue</p>
                        <p class="text-gray-600">Rp <?= number_format($todayRevenue + $monthRevenue + $yearRevenue, 2) ?></p>
                    </div>
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>

            <div class="card">
                <h2 class="text-xl font-bold">Booking Requests</h2>
                <p class="mb-4">Manage booking requests below:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($bookingRequests as $request): ?>
                        <div class="bg-white border rounded-lg shadow-md p-4">
                            <h3 class="font-bold text-lg"><?= htmlspecialchars($request['customer_name']) ?></h3>
                            <p><strong>Invoice:</strong> <?= htmlspecialchars($request['invoice_code']) ?></p>
                            <p><strong>Request Date:</strong> <?= htmlspecialchars($request['request_date']) ?></p>
                            <p><strong>Lapangan:</strong> <?= htmlspecialchars($request['field']) ?></p>
                            <p><strong>Harga:</strong> Rp <?= number_format($request['price'], 2) ?></p>
                            <p><strong>Waktu:</strong> <?= htmlspecialchars($request['start_time']) ?> - <?= htmlspecialchars($request['end_time']) ?></p>
                            <div class="mt-4 flex justify-between">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="customer_name" value="<?= htmlspecialchars($request['customer_name']) ?>">
                                    <button type="submit" name="confirm_request" class="form-button small-button text-xs px-2 py-1 text-hijau-500">Konfirmasi</button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <button type="submit" name="reject_request" class="form-button small-button text-xs px-2 py-1 text-red-500">Tolak</button>
                                </form>
                                <button type="button" onclick="viewPaymentProof(<?= $request['id'] ?>)" class="form-button small-button text-xs px-2 py-1 text-blue-500">Lihat Bukti Bayar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="admins" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Data Admin Section</h2>
                <div class="flex justify-between items-center mb-4">
                    <button class="form-button small-button text-xs px-2 py-1 text-blue-500" onclick="showAddAdminForm()">Tambah Admin</button>
                    <div class="search-bar">
                        <input type="text" id="adminSearch" placeholder="Search Admins..." onkeyup="filterTable('adminSearch', 'adminTable')" class="form-input" />
                    </div>
                </div>
                <table id="adminTable" class="min-w-full bg-white border mt-4">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Nama</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Username</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">No Handphone</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Email</th>
                            <th class="py-2 px-4 border-b text-xs text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($admin['nama']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($admin['username']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($admin['nohp']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($admin['email']) ?></td>
                                <td class="border-b py-2 px-4 text-center">
                                    <form method="POST" class="inline">
                                        <button type="button" class="form-button small-button text-xs px-1 py-1 text-orange-500" 
                                                onclick="showEditAdminForm(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['nama']) ?>', '<?= htmlspecialchars($admin['username']) ?>', '<?= htmlspecialchars($admin['nohp']) ?>', '<?= htmlspecialchars($admin['email']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                        <button type="submit" name="delete_user" class="form-button small-button text-xs px-1 py-1 text-red-500" onclick="return confirm('Are you sure you want to delete this admin?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="users" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Data User Section</h2>
                <div class="flex justify-between items-center mb-4">
                    <button class="form-button small-button text-xs px-2 py-1 text-blue-500" onclick="showAddUserForm()">Tambah User</button>
                    <div class="search-bar">
                        <input type="text" id="userSearch" placeholder="Search Users..." onkeyup="filterTable('userSearch', 'userTable')" class="form-input" />
                    </div>
                </div>
                <table id="userTable" class="min-w-full bg-white border mt-4">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Nama</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Username</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">No Handphone</th>
                            <th class="py-2 px-4 border-b text-xs text-left table-header">Email</th>
                            <th class="py-2 px-4 border-b text-xs text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($user['nama']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($user['username']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($user['nohp']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="border-b py-2 px-4 text-center">
                                    <form method="POST" class="inline">
                                        <button type="button" class="form-button small-button text-xs px-1 py-1 text-orange-500" 
                                                onclick="showEditUserForm(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nama']) ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['nohp']) ?>', '<?= htmlspecialchars($user['email']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="form-button small-button text-xs px-1 py-1 text-red-500" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="addAdminForm" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Tambah Admin</h2>
                <form method="POST" class="mt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="nama" class="block text-gray-700 small-text">Nama Lengkap</label>
                            <input type="text" name="nama" id="nama" class="form-input" placeholder="Mas ukkan nama lengkap" required />
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block text-gray-700 small-text">Username</label>
                            <input type="text" name="username" id="username" class="form-input" placeholder="Masukkan username" required />
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 small-text">Email</label>
                            <input type="email" name="email" id="email" class="form-input" placeholder="Masukkan email" required />
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-gray-700 small-text">Password</label>
                            <input type="password" name="password" id="password" class="form-input" placeholder="Masukkan password" required />
                        </div>
                        <div class="mb-4">
                            <label for="nohp" class="block text-gray-700 small-text">No Handphone</label>
                            <input type="text" name="nohp" id="nohp" class="form-input" placeholder="Masukkan nomor handphone" required />
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="block text-gray-700 small-text">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Konfirmasi password" required />
                        </div>
                    </div>
                    <button type="submit" name="tambah_admin" class="form-button small-button text-xs px-2 py-1 text-blue-500">Tambah Admin</button>
                    <button type="button" onclick="cancelAddAdmin()" class="form-button small-button text-xs px-2 py-1 ml-2 text-red-500">Batal</button>
                </form>
            </div>
        </div>

        <div id="editAdminForm" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Edit Admin</h2>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="id" id="editAdminId" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="editNama" class="block text-gray-700 small-text">Nama</label>
                            <input type="text" name="edit_user_nama" id="editNama" class="form-input" placeholder="Masukkan nama lengkap" required />
                        </div>
                        <div class="mb-4">
                            <label for="editUsername" class="block text-gray-700 small-text">Username</label>
                            <input type="text" name="edit_user_username" id="editUsername" class="form-input" placeholder="Masukkan username" required />
                        </div>
                        <div class="mb-4">
                            <label for="editNohp" class="block text-gray-700 small-text">No Handphone</label>
                            <input type="text" name="edit_user_nohp" id="editNohp" class="form-input" placeholder="Masukkan nomor handphone" required />
                        </div>
                        <div class="mb-4">
                            <label for="editEmail" class="block text-gray-700 small-text">Email</label>
                            <input type="email" name="edit_user_email" id="editEmail" class="form-input" placeholder="Masukkan email" required />
                        </div>
                    </div>
                    <button type="submit" name="edit_user" class="form-button small-button text-xs px-2 py-1 text-orange-500">Simpan Perubahan</button>
                    <button type="button" onclick="cancelEditAdmin()" class="form-button small-button text-xs px-2 py-1 ml-2 text-red-500">Batal</button>
                </form>
            </div>
        </div>

        <div id="addUserForm" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Tambah User</h2>
                <form method="POST" class="mt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="userNama" class="block text-gray-700 small-text">Nama Lengkap</label>
                            <input type="text" name="user_nama" id="userNama" class="form-input" placeholder="Masukkan nama lengkap" required />
                        </div>
                        <div class="mb-4">
                            <label for="userUsername" class="block text-gray-700 small-text">Username</label>
                            <input type="text" name="user_username" id="userUsername" class="form-input" placeholder="Masukkan username" required />
                        </div>
                        <div class="mb-4">
                            <label for="userEmail" class="block text-gray-700 small-text">Email</label>
                            <input type="email" name="user_email" id="userEmail" class="form-input" placeholder="Masukkan email" required />
                        </div>
                        <div class="mb-4">
                            <label for="userPassword" class="block text-gray-700 small-text">Password</label>
                            <input type="password" name="user_password" id="userPassword" class="form-input" placeholder="Masukkan password" required />
                        </div>
                        <div class="mb-4">
                            <label for="userNohp" class="block text-gray-700 small-text">No Handphone</label>
                            <input type="text" name="user_nohp" id="userNohp" class="form-input" placeholder="Masukkan nomor handphone" required />
                        </div>
                        <div class="mb-4">
                            <label for="userConfirmPassword" class="block text-gray-700 small-text">Konfirmasi Password</label>
                            <input type="password" name="user_confirm_password" id="userConfirmPassword" class="form-input" placeholder="Konfirmasi password" required />
                        </div>
                    </div>
                    <button type="submit" name="tambah_user" class="form-button small-button text-xs px-2 py-1 text-blue-500">Tambah User</button>
                    <button type="button" onclick="cancelAddUser()" class="form-button small-button text-xs px-2 py-1 ml-2 text-red-500">Batal</button>
                </form>
            </div>
        </div>

        <div id="editUserForm" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Edit User</h2>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="id" id="editUserId" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="editUserNama" class="block text-gray-700 small-text">Nama</label>
                            <input type="text" name="edit_user_nama" id="editUserNama" class="form-input" placeholder="Masukkan nama lengkap" required />
                        </div>
                        <div class="mb-4">
                            <label for="editUserUsername" class="block text-gray-700 small-text">Username</label>
                            <input type="text" name="edit_user_username" id="editUserUsername" class="form-input" placeholder="Masukkan username" required />
                        </div>
                        <div class="mb-4">
                            <label for="editUserNohp" class="block text-gray-700 small-text">No Handphone</label>
                            <input type="text" name="edit_user_nohp" id="editUserNohp" class="form-input" placeholder="Masukkan nomor handphone" required />
                        </div>
                        <div class="mb-4">
                            <label for="editUserEmail" class="block text-gray-700 small-text">Email</label>
                            <input type="email" name="edit_user_email" id="editUserEmail" class="form-input" placeholder="Masukkan email" required />
                        </div>
                    </div>
                    <button type="submit" name="edit_user" class="form-button small-button text-xs px-2 py-1 text-orange-500">Simpan Perubahan</button>
                    <button type="button" onclick="cancelEditUser()" class="form-button small-button text-xs px-2 py-1 ml-2 text-red-500">Batal</button>
                </form>
            </div>
        </div>

        <div id="dataRental" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Data Rental Section</h2>
                <div class="search-bar">
                    <input type="text" id="rentalSearch" placeholder="Search Rentals..." onkeyup="filterTable('rentalSearch', 'rentalTable')" class="form-input">
                </div>
                <table id=" rentalTable" class="min-w-full bg-white border mt-4">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4 border-b text-xs">Invoice</th>
                            <th class="py-2 px-4 border-b text-xs">Nama Pelanggan</th>
                            <th class="py-2 px-4 border-b text-xs">Tanggal Transaksi</th>
                            <th class="py-2 px-4 border-b text-xs">Total</th>
                            <th class="py-2 px-4 border-b text-xs text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rental): ?>
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($rental['invoice_code']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($rental['customer_name']) ?></td>
                                <td class="border-b py-2 px-4 text-xs"><?= htmlspecialchars($rental['rental_date']) ?></td>
                                <td class="border-b py-2 px-4 text-xs">Rp <?= number_format($rental['total_price'], 2) ?></td>
                                <td class="border-b py-2 px-4 text-center">
                                    <button class="form-button small-button text-xs px-1 py-1 text-blue-500" onclick="showRentalDetail(<?= $rental['id'] ?>)">Detail</button>
                                    <button class="form-button small-button text-xs px-1 py-1 text-orange-500" onclick="showEditRentalForm(<?= $rental['id'] ?>, '<?= htmlspecialchars($rental['customer_name']) ?>', '<?= htmlspecialchars($rental['rental_date']) ?>', <?= $rental['total_price'] ?>)">Edit</button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?= $rental['id'] ?>">
                                        <button type="submit" name="delete_rental" class="form-button small-button text-xs px-1 py-1 text-red-500" onclick="return confirm('Are you sure you want to delete this rental?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="editRentalForm" class="content-section hidden">
            <div class="card">
                <h2 class="text-xl font-bold">Edit Rental</h2>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="id" id="editRentalId" />
                    <div class="mb-4">
                        <label for="editCustomerName" class="block text-gray-700 small-text">Nama Pelanggan</label>
                        <input type="text" name="edit_customer_name" id="editCustomerName" class="form-input" required />
                    </div>
                    <div class="mb-4">
                        <label for="editRentalDate" class="block text-gray-700 small-text">Tanggal Transaksi</label>
                        <input type="date" name="edit_rental_date" id="editRentalDate" class="form-input" required />
                    </div>
                    <div class="mb-4">
                        <label for="editTotalPrice" class="block text-gray-700 small-text">Total</label>
                        <input type="number" name="edit_total_price" id="editTotalPrice" class="form-input" required />
                    </div>
                    <button type="submit" name="edit_rental" class="form-button small-button text-xs px-2 py-1 text-orange-500">Simpan Perubahan</button>
                    <button type="button" onclick="cancelEditRental()" class="form-button small-button text-xs px-2 py-1 ml-2 text-red-500">Batal</button>
                </form>
            </div>
        </div>

        <div id="payment-proof-modal" class="modal hidden">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="payment-proof"></div>
            </div>
        </div>

        <script>
            function showContent(sectionId) {
                const sections = document.querySelectorAll('.content-section');
                sections.forEach(section => {
                    section.classList.add('hidden');
                    section.classList.remove('fade-in');
                });
                const activeSection = document.getElementById(sectionId);
                activeSection.classList.remove('hidden');
                activeSection.classList.add('fade-in');
            }

            function viewPaymentProof(id) {
                $.ajax({
                    type: 'POST',
                    url: 'view_payment_proof.php',
                    data: {id: id},
                    success: function(data) {
                        $('#payment-proof').html(data);
                        $('#payment-proof-modal').removeClass('hidden'); // Show modal
                    },
                    error: function() {
                        $('#payment-proof').html('Terjadi kesalahan saat mengambil bukti bayar.');
                        $('#payment-proof-modal').removeClass('hidden'); // Show modal
                    }
                });
            }

            function closeModal() {
                $('#payment-proof-modal').addClass('hidden');
            }

            function showAddAdminForm() {
                document.getElementById('addAdminForm').classList.remove('hidden');
                document.getElementById('admins').classList.add('hidden');
            }

            function cancelAddAdmin() {
                document.getElementById('addAdminForm').classList.add('hidden');
                document.getElementById('admins').classList.remove('hidden');
            }

            function showAddUserForm() {
                document.getElementById('addUserForm').classList.remove('hidden');
                document.getElementById('users').classList.add('hidden');
            }

            function cancelAddUser() {
                document.getElementById('addUserForm').classList.add('hidden');
                document.getElementById('users').classList.remove('hidden');
            }

            function showEditAdminForm(id, name, username, nohp, email) {
                document.getElementById('editAdminId').value = id;
                document.getElementById('editNama').value = name;
                document.getElementById('editUsername').value = username;
                document.getElementById('editNohp').value = nohp;
                document.getElementById('editEmail').value = email;
                document.getElementById('editAdminForm').classList.remove('hidden');
                document.getElementById('admins').classList.add('hidden');
            }

            function cancelEditAdmin() {
                document.getElementById('editAdminForm').classList.add('hidden');
                document.getElementById('admins').classList.remove('hidden');
            }

            function showEditUserForm(id, name, username, nohp, email) {
                document.getElementById('editUserId').value = id;
                document.getElementById('editUserNama').value = name;
                document.getElementById('editUserUsername').value = username;
                document.getElementById('editUserNohp').value = nohp;
                document.getElementById('editUserEmail').value = email;
                document.getElementById('editUserForm').classList.remove('hidden');
                document.getElementById('users').classList.add('hidden');
            }

            function cancelEditUser() {
                document.getElementById('editUserForm').classList.add('hidden');
                document.getElementById('users').classList.remove('hidden');
            }

            function showEditRentalForm(id, customerName, rentalDate, totalPrice) {
                document.getElementById('editRentalId').value = id;
                document.getElementById('editCustomerName').value = customerName;
                document.getElementById('editRentalDate').value = rentalDate;
                document.getElementById('editTotalPrice').value = totalPrice;
                document.getElementById('editRentalForm').classList.remove('hidden');
                document.getElementById('dataRental').classList.add('hidden');
            }

            function cancelEditRental() {
                document.getElementById('editRentalForm').classList.add('hidden');
                document.getElementById('dataRental').classList.remove('hidden');
            }

            function filterTable(searchId, tableId) {
                const input = document.getElementById(searchId);
                const filter = input.value.toLowerCase();
                const table = document.getElementById(tableId);
                const tr = table.getElementsByTagName("tr");

                for (let i = 1; i < tr.length; i++) {
                    const td = tr[i].getElementsByTagName("td");
                    let found = false;
                    for (let j = 0; j < td.length; j++) {
                        if (td[j]) {
                            const txtValue = td[j].textContent || td[j].innerText;
                            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                                found = true;
                                break;
                            }
                        }
                    }
                    tr[i].style.display = found ? "" : "none";
                }
            }
        </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </body>
</html>