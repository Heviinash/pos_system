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
$sql = "SELECT service_name, price FROM services WHERE service_name LIKE '%$q%'";
$result = $conn->query($sql);
$services = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
echo json_encode($services);
$conn->close();
?>
