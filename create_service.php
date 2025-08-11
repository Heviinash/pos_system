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
    header("Location: index.php");
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









// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'pos';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'];
    $service_description = $_POST['service_description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $product_data = isset($_POST['products']) ? $_POST['products'] : []; // Ensure it's an array

    // Insert the service into the database
    $insert_service_query = "INSERT INTO service (service_name, service_description, price, duration) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_service_query);
    $stmt->bind_param("ssis", $service_name, $service_description, $price, $duration);

    if ($stmt->execute()) {
        $service_id = $stmt->insert_id; // Get the ID of the newly created service

        // Check if there are products selected
        if (!empty($product_data)) {
            foreach ($product_data as $product_id => $product_details) {
                // Retrieve and sanitize variant and quantity
                $variant_name = isset($product_details['variant']) ? htmlspecialchars($product_details['variant']) : '';
                $quantity_deducted = isset($product_details['quantity']) ? (int)htmlspecialchars($product_details['quantity']) : 0;

                // Validate inputs
                if (empty($product_id) || empty($variant_name) || $quantity_deducted <= 0) {
                    echo "Invalid data for Product ID $product_id.";
                    continue;
                }

                // Check if the product exists
                $check_product_query = "SELECT product_id FROM variants WHERE product_id = ? AND variant_name = ?";
                $stmt_check = $conn->prepare($check_product_query);
                $stmt_check->bind_param("ss", $product_id, $variant_name);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows === 0) {
                    echo "Product ID $product_id with variant $variant_name does not exist.";
                    continue;
                }

                // Insert product-service link
                $insert_link_query = "INSERT INTO service_product (service_id, product_id, variant_name, quantity_deducted) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_link_query);
                $stmt_insert->bind_param("issi", $service_id, $product_id, $variant_name, $quantity_deducted);

                if (!$stmt_insert->execute()) {
                    echo "Error linking products: " . $conn->error;
                }

                // Deduct quantity from the product inventory (variants table)
                $update_inventory_query = "UPDATE variants SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND variant_name = ?";
                $stmt_update = $conn->prepare($update_inventory_query);
                $stmt_update->bind_param("iss", $quantity_deducted, $product_id, $variant_name);

                if (!$stmt_update->execute()) {
                    echo "Error updating inventory: " . $conn->error;
                }
            }
        }

        echo "Service created and inventory updated successfully!";
    } else {
        echo "Error creating service: " . $conn->error;
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service</title>
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
            max-width: 1000px;
            margin: auto;
            padding: 20px;
        }
        .form-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 8px;
        }
        .form-container label {
            display: block;
            margin-bottom: 10px;
        }
        .form-container input, 
        .form-container textarea,
        .form-container select, 
        .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #444;
            color: white;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #555;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
        }
        .checkbox-group .card {
            background: #333;
            color: #fff;
            border-radius: 8px;
            margin: 5px;
            padding: 10px;
            width: 200px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .checkbox-group .card img {
            max-width: 100%;
            border-radius: 5px;
        }
        .checkbox-group label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Create New Service</h1>
    <div class="form-container">
        <form action="create_service.php" method="POST">
            <label for="service_name">Service Name:</label>
            <input type="text" id="service_name" name="service_name" required>

            <label for="service_description">Service Description:</label>
            <textarea id="service_description" name="service_description" rows="4" required></textarea>

            <label for="price">Price:</label>
            <input type="text" id="price" name="price" required>

            <label for="duration">Duration (in minutes):</label>
            <input type="number" id="duration" name="duration" required>

            <label>Products:</label>
            <div class="checkbox-group">
                <?php
                    // Fetch products with variants
                    $conn = new mysqli($host, $user, $password, $dbname);
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $query = "SELECT p.product_id, p.product_name, p.product_description, v.variant_name, v.sku, v.price, v.stock_quantity, p.image_url
                              FROM productsinventory p
                              JOIN variants v ON p.product_id = v.product_id
                              WHERE v.stock_quantity > 0";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='card'>
                                    <p>Product Name: " . htmlspecialchars($row['product_name']) . "</p>
                                    <p>ID: " . htmlspecialchars($row['product_id']) . "</p>
                                    <p>Variant: " . htmlspecialchars($row['variant_name']) . "</p>
                                    <p>Price: $" . htmlspecialchars($row['price']) . "</p>
                                    <label>
                                        <input type='checkbox' name='products[" . htmlspecialchars($row['product_id']) . "][variant]' value='" . htmlspecialchars($row['variant_name']) . "'> 
                                        " . htmlspecialchars($row['product_name']) . " - " . htmlspecialchars($row['variant_name']) . "
                                    </label>
                                    <label for='quantity_" . htmlspecialchars($row['product_id']) . "'>Quantity:</label>
                                    <input type='number' name='products[" . htmlspecialchars($row['product_id']) . "][quantity]' min='0' max='" . htmlspecialchars($row['stock_quantity']) . "' value='1'>
                                  </div>";
                        }
                    } else {
                        echo "<p>No products available</p>";
                    }

                    $conn->close();
                ?>
            </div>

            <button type="submit">Create Service</button>
        </form>
    </div>
</div>

</body>
</html>