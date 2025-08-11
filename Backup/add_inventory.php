<?php

// USER CONTROL CODE

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
    // User is not logged in, redirect to login page
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
    // User doesn't have permission, redirect to unauthorized page
    header("Location: unauthorized.php");  // Replace with your unauthorized access page
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();

// Handle form submission for adding inventory items
$conn = new mysqli('localhost', 'root', '', 'pos');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_inventory'])) {
    $item_name = $_POST['item_name'];
    $item_type = $_POST['item_type'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $reorder_level = $_POST['reorder_level'];

    $sql = "INSERT INTO serviceinventory (item_name, item_type, quantity, unit, reorder_level) 
            VALUES ('$item_name', '$item_type', '$quantity', '$unit', '$reorder_level')";

    if ($conn->query($sql) === TRUE) {

        echo '<div style="color: #28a745; font-weight: bold; font-size: 16px; background-color: #e9f7ef; padding: 10px; border: 1px solid #28a745; border-radius: 5px; text-align: center; width: auto; display: inline-block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;" id="successMessage">
        New item added successfully
      </div>
      <script>
        setTimeout(function() {
          document.getElementById("successMessage").style.display = "none";
        }, 2000); // 2000 milliseconds = 2 seconds
      </script>';

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle deletion of inventory items
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    $conn->query("DELETE FROM serviceinventory WHERE item_id = $item_id");
}

// Fetch all inventory items
$result = $conn->query("SELECT * FROM serviceinventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .form-container, .table-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        h1, h2 {
            text-align: center;
            color: #00bfff;
        }

        /* Form styling */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        form input, form select {
            padding: 10px;
            margin: 0;
            border-radius: 4px;
            border: none;
            width: 100%;
        }

        .full-width {
            grid-column: 1 / 3;
        }

        button {
            background-color: #00bfff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #008fb3;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #fff;
        }

        th {
            background-color: #00bfff;
        }

        a {
            color: #00bfff;
            text-decoration: none;
        }

        a:hover {
            color: #008fb3;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <div class="container">
        <div class="form-container">
            <h1>Create Service Item</h1>
            <form method="POST">
                <input type="text" name="item_name" placeholder="Item Name" required>
                <select name="item_type" required>
                    <option value="raw items">Raw Items</option>
                    <option value="product">Service Product</option>
                </select>
                <input type="number" step="0.01" name="quantity" placeholder="Quantity" required>
                <input type="text" name="unit" placeholder="Unit (e.g., kg, ml)" required>
                <input type="number" name="reorder_level" placeholder="Reorder Level" required>
                <button type="submit" name="add_inventory">Add Item</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Inventory Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Item Type</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['item_name']; ?></td>
                            <td><?php echo $row['item_type']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['unit']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['item_id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
