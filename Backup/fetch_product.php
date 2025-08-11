<?php
session_start();
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$barcode = $data['barcode'];

// Fetch product details
$query = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

$product_name = '';
$product_price = 0; // Default price value
if ($row = $result->fetch_assoc()) {
    $product_name = $row['brand'] . ' ' . $row['producttype'] . ' ' . $row['variant']; // Combine fields to create product name
    $product_price = $row['price']; // Get product price
}

// Return the product name and price as JSON
echo json_encode(['product_name' => $product_name, 'price' => $product_price]);

// Close database connection
$stmt->close();
$conn->close();
?>
