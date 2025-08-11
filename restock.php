<?php
// Session control
session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);  // Get current file name

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!$employee_id) {
    header("Location: login.php");
    exit();
}

// Check if the user role has permission to access the current page
$query = "SELECT * FROM roles_permission WHERE role_name = ? AND permission_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $role_name, $current_page);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: unauthorized.php");  // Replace with your unauthorized access page
    exit();
}
$stmt->close(); // Close the statement used for permission check

// Variable to hold error message for barcode not found
$barcode_error = "";

// Handle restock request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = $_POST['barcode'];
    $restock_quantity = $_POST['quantity'];

    // Check if the barcode exists in the database
    $check_query = "SELECT * FROM productinventorytable WHERE Barcode = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $barcode);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Barcode exists, proceed with the stock update
        if ($restock_quantity > 0) {
            $update_query = "UPDATE productinventorytable SET stock = stock + ?, last_restocked_by = ? WHERE Barcode = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("iss", $restock_quantity, $employee_id, $barcode);
            
            if ($update_stmt->execute()) {
                echo "<script>alert('Stock updated successfully!');</script>";
            } else {
                echo "<script>alert('Error updating stock.');</script>";
            }
            $update_stmt->close();  // Close the statement after execution
        } else {
            echo "<script>alert('Quantity must be greater than zero.');</script>";
        }
    } else {
        // Set error message if barcode not found
        $barcode_error = "Error: Barcode not found in inventory.";
    }

    $check_stmt->close();  // Close the statement used for the barcode check
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #007bff;
            margin-bottom: 20px;
        }

        button {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            background-color: #00f;
            color: #fff;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

<div class="container">
    <h1>Restock Products</h1>
    <form method="POST" action="">
        <label for="barcode">Product Barcode:</label>
        <input type="text" name="barcode" id="barcode" required>
        <!-- Display barcode error message -->
        <?php if ($barcode_error): ?>
            <div class="error-message"><?php echo $barcode_error; ?></div>
        <?php endif; ?>

        <label for="quantity">Restock Quantity:</label>
        <input type="number" name="quantity" id="quantity" required min="1">

        <button type="submit">Restock</button>
    </form>
</div>

</body>
</html>
