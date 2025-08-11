<?php

session_start();

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
    <title>View Customer Details/Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed; /* Background image */
            background-size: cover; /* Cover the entire body */
            color: #fff; /* White text for better contrast on dark background */
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px; /* Set a maximum width for the container */
            margin: 30px auto; /* Margin to separate from the navbar */
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background */
            backdrop-filter: blur(10px); /* Blur effect behind the container */
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); /* Dark shadow for depth */
            border-radius: 8px; /* Rounded corners for a softer look */
        }

        h1, h2 {
            color: #fff; /* White color for headings */
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.5); /* Light border for inputs */
            border-radius: 4px; /* Rounded corners for inputs */
            background-color: rgba(255, 255, 255, 0.2); /* Slightly transparent input background */
            color: #fff; /* White text in inputs */
        }

        button {
            padding: 8px 12px; /* Adjust padding for button */
            background-color: #28a745; /* Green button */
            color: white; /* White text on button */
            border: none;
            border-radius: 4px; /* Rounded corners for button */
            cursor: pointer;
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        button:hover {
            background-color: #218838; /* Darker green on hover */
        }

         .customer-info, .logs {
            margin-top: 20px;
            padding: 15px; /* Padding inside the container for better spacing */
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for better visibility */
            border-radius: 8px; /* Rounded corners for a softer look */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Subtle shadow for depth */
        }

        /* Title styles for customer-info and logs */
        .customer-info h2, .logs h2 {
            margin-bottom: 10px; /* Space below headings */
            color: #28a745; /* A contrasting color for headings */
            border-bottom: 2px solid rgba(40, 167, 69, 0.5); /* Underline for headings */
            padding-bottom: 5px; /* Padding below headings */
        }

        /* Style for individual customer entries */
        .customer-info .entry, .logs .entry {
            border: 1px solid rgba(255, 255, 255, 0.3); /* Light border for individual entries */
            margin: 10px 0; /* Space between entries */
            padding: 10px; /* Padding inside each entry */
            border-radius: 4px; /* Rounded corners for individual entries */
            background-color: rgba(255, 255, 255, 0.2); /* Slightly transparent background */
            display: flex; /* Flexbox for layout */
            justify-content: space-between; /* Space between items */
            align-items: center; /* Center items vertically */
        }

        /* Style for labels */
        .entry label {
            font-weight: bold; /* Bold labels */
            color: #fff; /* White color for labels */
        }

        /* Style for values */
        .entry .value {
            color: #f1f1f1; /* Lighter text for values */
        }

        img {
            width: 50px; /* Adjust image size */
            height: 50px;
            margin-right: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid rgba(255, 255, 255, 0.3); /* Light border for table */
            padding: 8px;
            color: #fff; /* White text for table */
        }

        th {
            background-color: rgba(255, 255, 255, 0.1); /* Darker background for header */
        }
    </style>

</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

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
                    <div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
                        <p style="margin: 10px 0; font-weight: bold;">Name: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['name']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">IC Number: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['icnumber']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Customer ID: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['customerid']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Date of Birth: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['dateofbirth']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Gender: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['gender']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Email: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['email']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Address: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['address']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Phone: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['phone']); ?></span></p>
                        <p style="margin: 10px 0; font-weight: bold;">Membership: <span style="font-weight: normal;"><?php echo htmlspecialchars($customer['membership']); ?></span></p>
                    </div>


                    <!-- Fetch membership details -->
                    <?php
                    $membership_sql = "SELECT * FROM customermemberships WHERE icnumber = '" . $customer['icnumber'] . "'";
                    $membership_result = $conn->query($membership_sql);
                    ?>
                    <?php if ($membership_result && $membership_result->num_rows > 0): ?>
                        <h2>Membership Details</h2>
                        <?php while ($membership = $membership_result->fetch_assoc()): ?>
                            <div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); margin-top: 20px;">
                                <p style="margin: 10px 0; font-weight: bold;">Membership ID: <span style="font-weight: normal;"><?php echo htmlspecialchars($membership['membership_id']); ?></span></p>
                                <p style="margin: 10px 0; font-weight: bold;">Expiry Date: <span style="font-weight: normal;"><?php echo htmlspecialchars($membership['membership_expirydate']); ?></span></p>
                                <p style="margin: 10px 0; font-weight: bold;">Points: <span style="font-weight: normal;"><?php echo htmlspecialchars($membership['membership_points']); ?></span></p>
                                <p style="margin: 10px 0; font-weight: bold;">Type: <span style="font-weight: normal;"><?php echo htmlspecialchars($membership['membership_type']); ?></span></p>
                            </div>
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
