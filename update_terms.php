<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "pos"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terms'])) {
    $terms = $_POST['terms'];

    // Update terms in the session or database
    $_SESSION['terms'] = $terms;

    // Optionally, update in the database (if you have a table for this)
    // $sql = "UPDATE settings SET terms_conditions = ?"; // Example SQL
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("s", $terms);
    // $stmt->execute();
    
    // Close statement
    // $stmt->close();
    
    // Redirect back to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Close the connection
$conn->close();
?>
