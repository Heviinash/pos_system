<?php
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

// Database connection settings
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If the user is logged in, update the logout time in the login_history table
if (isset($_SESSION['employeeid'])) {
    $employeeid = $_SESSION['employeeid'];
    $logout_time = date("Y-m-d H:i:s");

    // Update logout time in the login_history table
    $update_query = "UPDATE login_history SET logout_time = ? WHERE employeeid = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ss", $logout_time, $employeeid);
    $update_stmt->execute();
    $update_stmt->close();
}

// Unset all session variables
$_SESSION = array();

// If you want to destroy the session completely, including the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to the login page or home page
header("Location: login.php"); // Change to your login or home page
exit();
?>
