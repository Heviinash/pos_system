<?php
// Connect to the database
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "pos"; // Database name

// Create the connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
$customer_info = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get search input
    $search_term = $_POST['search_term'];
    // Query to search customer by name or IC number
    $sql_customer = "SELECT * FROM customerregistration WHERE name LIKE '%$search_term%' OR icnumber = '$search_term'";
    $customer_result = $conn->query($sql_customer);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        .customer-info, .logs {
            margin-top: 20px;
        }
        img {
            width: 50px;
            height: 50px;
            margin-right: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>


    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">

    <div class="container">

        <h1>View Customer Logs</h1>
        <form method="POST" action="viewcustomerlogs.php">
            <input type="text" name="search_term" placeholder="Enter Customer Name or IC Number" required>
            <button type="submit">Search</button>
        </form>

        <?php if (isset($customer_result) && $customer_result->num_rows > 0): ?>
            <div class="customer-info">
                <h2>Customer Details</h2>
                <?php while ($customer = $customer_result->fetch_assoc()): ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                    <p><strong>IC Number:</strong> <?php echo htmlspecialchars($customer['icnumber']); ?></p>
                    <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($customer['customerid']); ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($customer['dateofbirth']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($customer['gender']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                    <p><strong>Membership:</strong> <?php echo htmlspecialchars($customer['membership']); ?></p>

                    <!-- Fetch membership details -->
                    <?php
                    $membership_sql = "SELECT * FROM customermemberships WHERE icnumber = '" . $customer['icnumber'] . "'";
                    $membership_result = $conn->query($membership_sql);
                    ?>
                    <?php if ($membership_result && $membership_result->num_rows > 0): ?>
                        <h3>Membership Details</h3>
                        <?php while ($membership = $membership_result->fetch_assoc()): ?>
                            <p><strong>Membership ID:</strong> <?php echo htmlspecialchars($membership['membership_id']); ?></p>
                            <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($membership['membership_expirydate']); ?></p>
                            <p><strong>Points:</strong> <?php echo htmlspecialchars($membership['membership_points']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($membership['membership_type']); ?></p>
                        <?php endwhile; ?>
                    <?php endif; ?>



                    <!-- Fetch logs associated with the customer -->
                    <?php
                    $logs_sql = "SELECT * FROM testcustomer_logs WHERE icnumber = '" . $customer['icnumber'] . "'";
                    $logs_result = $conn->query($logs_sql);
                    ?>
                    <div class="logs">
                        <h2>Customer Logs</h2>
                        <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                            <table>
                                <tr>
                                    <th>Log ID</th>
                                    <th> Date </th>
                                    <th>Problem Description</th>
                                    <th>Follow-up Date</th>
                                    <th>Images</th>
                                    <th>Services</th>
                                    <th>Products</th>
                                </tr>
                                <?php while ($log = $logs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($log['problem_desc']); ?></td>
                                        <td><?php echo htmlspecialchars($log['followupdate']); ?></td>
                                        <td>
                                            <?php
                                            $images = explode(',', $log['image_path']);
                                            foreach ($images as $image) {
                                                echo '<img src="' . htmlspecialchars($image) . '" alt="Image">';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <!-- Service Column -->
                                            <?php
                                                // Fetch associated services
                                                $services_sql = "
                                                    SELECT s.service_needed, srv.price 
                                                    FROM testcustomer_services s
                                                    LEFT JOIN services srv ON s.service_needed = srv.service_name 
                                                    WHERE s.log_id = '" . $log['log_id'] . "'";
                                                $services_result = $conn->query($services_sql);
                                                if ($services_result && $services_result->num_rows > 0) {
                                                    while ($service = $services_result->fetch_assoc()) {
                                                        echo "<strong>Service:</strong> " . htmlspecialchars($service['service_needed']) . "<br>";
                                                        echo "<strong>Price:</strong> RM " . htmlspecialchars($service['price']) . "<br><br>";
                                                    }
                                                } else {
                                                    echo "<strong>Service:</strong> No services found.<br>";
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <!-- Product Column -->
                                            <?php
                                                // Fetch associated products
                                                $products_sql = "SELECT product_needed, quantity, price FROM testcustomer_products WHERE log_id = '" . $log['log_id'] . "'";
                                                $products_result = $conn->query($products_sql);
                                                if ($products_result && $products_result->num_rows > 0) {
                                                    while ($product = $products_result->fetch_assoc()) {
                                                        echo "<strong>Product:</strong> " . htmlspecialchars($product['product_needed']) . "<br>";
                                                        echo "<strong>Quantity:</strong> " . htmlspecialchars($product['quantity']) . "<br>";
                                                        echo "<strong>Price:</strong> RM " . htmlspecialchars($product['price']) . "<br><br>";
                                                    }
                                                } else {
                                                    echo "<strong>Product:</strong> No products found.";
                                                }
                                            ?>
                                        </td>

                                        
                                    </tr>
                                <?php endwhile; ?>
                            </table>
                        <?php else: ?>
                            <p>No logs found for this customer.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No customer found with that name or IC number.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
