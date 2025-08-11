<?php
session_start();
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];

    // Fetch product details
    $product_query = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(null);
    }

    $stmt->close();
}

$conn->close();
?>
