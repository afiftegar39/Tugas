<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';

// Database connection
$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $customerName = $_POST['customerName'];
    $invoiceCode = $_POST['invoiceCode'];
    $court = $_POST['court'];
    $date = $_POST['date'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $price = $_POST['price'];

    // Handle file upload
    $paymentReceipt = $_FILES['paymentReceipt'];
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($paymentReceipt['name']);
    
    // Check if the uploads directory exists, if not create it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move the uploaded file to the designated directory
    if (move_uploaded_file($paymentReceipt['tmp_name'], $uploadFile)) {
        // Prepare and bind the SQL statement
        $stmt = $mysqli->prepare("INSERT INTO booking_requests (customer_name, invoice_code, request_date, field, price, start_time, end_time, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Bind parameters
        $stmt->bind_param("ssssssss", $customerName, $invoiceCode, $date, $court, $price, $startTime, $endTime, $uploadFile);
        
        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Pesanan berhasil diajukan."]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "File upload error."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

// Close the database connection
$mysqli->close();
?>