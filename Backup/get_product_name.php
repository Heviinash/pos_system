<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    $product_query = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}

$conn->close();
?>
