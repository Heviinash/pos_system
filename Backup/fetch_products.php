<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare query based on input
$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE 
         CONCAT(brand, ' ', producttype, ' ', variant) LIKE '%$q%'";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
echo json_encode($products);
$conn->close();
?>
