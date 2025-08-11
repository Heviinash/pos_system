<?php
session_start();
header('Content-Type: application/json'); // Set the header for JSON response

$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if barcode is provided
if (isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];

    // Fetch product details
    $product_query = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($product_query);

    if (!$stmt) {
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No barcode provided']);
}

$conn->close();
?>
