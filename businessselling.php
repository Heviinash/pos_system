<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate unique transaction ID
function generateTransactionID($conn) {
    $isUnique = false;
    $transaction_id = '';

    // Keep generating until a unique ID is found
    while (!$isUnique) {
        // Generate a random alphanumeric string of 29 characters and prefix with "TID"
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 29);
        $transaction_id = 'TID' . $randomString;

        // Check if this transaction ID already exists in the database
        $query = "SELECT COUNT(*) AS count FROM sales_transaction WHERE transaction_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] == 0) {
            $isUnique = true;  // Found a unique transaction ID
        }

        // Close statement
        $stmt->close();
    }

    return $transaction_id;  
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcodes = $_POST['barcode'];
    $quantities = $_POST['quantity'];
    $icnumber = $_POST['icnumber'];

    $total_transaction_price = 0;
    $total_profit = 0; // Initialize total profit variable

    // Generate a unique transaction ID
    $transaction_id = generateTransactionID($conn);
    $_SESSION['transaction_id'] = $transaction_id; // Store transaction_id in session

    foreach ($barcodes as $index => $barcode) {
        $quantity = $quantities[$index];

        // Step 1: Fetch product details
        $product_query = "SELECT brand, producttype, variant, price, wholesaleprice, stock FROM productinventorytable WHERE barcode = ?";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        // Step 2: Check if the product exists and has enough stock
        if ($product && $product['stock'] >= $quantity) {
            // Calculate total price
            $total_price = $product['price'] * $quantity;
            $total_transaction_price += $total_price;

            // Calculate profit
            $wholesale_price = $product['wholesaleprice'];
            $profit = ($product['price'] - $wholesale_price) * $quantity;
            $total_profit += $profit; // Accumulate total profit

            // Step 3: Insert into sales_transaction table
            $sales_query = "INSERT INTO sales_transaction (transaction_id, employeeid, barcode, quantity, icnumber, total_price) VALUES (?, ?, ?, ?, ?, ?)";
            $sales_stmt = $conn->prepare($sales_query);
            $sales_stmt->bind_param("sssisd", $transaction_id, $_SESSION['employeeid'], $barcode, $quantity, $icnumber, $total_price);

            if ($sales_stmt->execute()) {
                // Step 4: Update stock in productinventorytable
                $new_stock = $product['stock'] - $quantity;
                $update_query = "UPDATE productinventorytable SET stock = ? WHERE barcode = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("is", $new_stock, $barcode);
                $update_stmt->execute();
            } else {
                echo "<p class='error'>Error recording the sale for product $barcode. Please try again.</p>";
            }
        } else {
            echo "<p class='error'>Insufficient stock or product not found for barcode $barcode.</p>";
        }

        // Close the first statement
        $stmt->close();
    }

    // After processing all products, insert total profit into profit table
    $profit_query = "INSERT INTO profit (transaction_id, profit, sale_datetime) VALUES (?, ?, NOW())";
    $profit_stmt = $conn->prepare($profit_query);
    $profit_stmt->bind_param("sd", $transaction_id, $total_profit);
    $profit_stmt->execute();

    echo "<p class='success'>Sale completed successfully! Total Transaction Price: RM$total_transaction_price</p>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $transaction_id = $_SESSION['transaction_id'] ?? ''; // Assuming these are set in the session
    $employeeid = $_SESSION['employeeid'] ?? NULL;
    $icnumber = $_POST['icnumber'] ?? '';
    $customer_name = $_POST['customer-name'] ?? '';
    $customer_number = $_POST['customer-number'] ?? '';
    $customer_email = $_POST['customer-email'] ?? '';
    $membership_info = $_POST['membership-info'] ?? ''; // This will be 'yes' or 'no'
    $voucher = $_POST['voucher'] ?? '';
    $subtotal = $_POST['subtotal'] ?? ''; // Leave empty if not set
    $discount = $_POST['discount'] ?? ''; // Leave empty if not set
    $grand_total = $_POST['grand-total'] ?? ''; // Leave empty if not set
    $payment_method = $_POST['payment-method'] ?? '';
    $payment_amount = $_POST['payment-amount'] ?? ''; // Leave empty if not set
    $additional_payment_method = $_POST['additional-payment-method'] ?? '';
    $additional_payment_amount = $_POST['additional-payment-amount'] ?? ''; // Leave empty if not set
    $change_provided = $_POST['change-provided'] ?? ''; // Leave empty if not set


    // Now proceed with your database operations...


    
    // Prepare SQL statement
    $sql = "INSERT INTO sales_payments (transaction_id, employeeid, icnumber, customer_name, customer_number, customer_email, membership_info, voucher, subtotal, discount, grand_total, payment_method, payment_amount, additional_payment_method, additional_payment_amount, change_provided) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssdddsdsdd", $transaction_id, $employeeid, $icnumber, $customer_name, $customer_number, $customer_email, $membership_info, $voucher, $subtotal, $discount, $grand_total, $payment_method, $payment_amount, $additional_payment_method, $additional_payment_amount, $change_provided);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Transaction completed successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT promotion_name, discount_value FROM promotions WHERE CURDATE() BETWEEN start_date AND end_date";
$result = mysqli_query($conn, $query);

// Handle AJAX requests for product details
if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    $sql = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle AJAX requests for customer names
if (isset($_GET['customer_names'])) {
    $sql = "SELECT icnumber, name FROM customerregistration";
    $result = $conn->query($sql);
    $customers = [];

    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    echo json_encode($customers);
    $conn->close();
    exit;
}

// Handle AJAX requests for customer details
if (isset($_GET['icnumber'])) {
    $icnumber = $_GET['icnumber'];
    $sql = "SELECT icnumber, email, phone FROM customerregistration WHERE icnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
        echo json_encode($details);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle AJAX requests for membership details
if (isset($_GET['membership_details'])) {
    $icnumber = $_GET['membership_details'];
    $sql = "SELECT membership_id, membership_expirydate, membership_points, membership_type FROM customermemberships WHERE icnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $membership = $result->fetch_assoc();
        echo json_encode($membership);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Transaction</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <style>
        /* General body settings */
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        /* Container styling */
        .container {
            max-width: 1200px;
            margin: auto; /* Center the container horizontally */
            padding: 20px;
            border-radius: 10px;
            display: flex; /* Added to handle panels */
            flex-wrap: wrap;
            justify-content: center; /* Center items horizontally */
            align-items: center; /* Center items vertically */
            height: 100vh; /* Optional: Set a height to ensure vertical centering */
        }


        /* Heading styling */
        h1, h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
            background-color: #333;
            padding: 10px;
            border-radius: 5px;
        }

        /* Form field styling */
        label {
            display: block;
            margin: 10px 0 5px;
            color: #ddd;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #222;
            color: #fff;
            box-sizing: border-box;
        }

        /* General button styling */
        button {
            background-color: #007bff; /* Medium blue color */
            color: white;
            padding: 14px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 20%;
            margin-bottom: 15px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        /* Hover effect for buttons */
        button:hover {
            background-color: #0056b3; /* Darker shade of blue on hover */
            transform: scale(1.03);
        }

        /* Individual button styling */
        #addProduct {
            background-color: cyan;
        }

        #addProduct:hover {
            background-color: darkcyan;
        }

        #startScanner {
            background-color: skyblue;
        }

        #startScanner:hover {
            background-color: lightblue;
        }

        button[type="submit"] {
            background-color: maroon;
        }

        button[type="submit"]:hover {
            background-color: indianred;
        }

        /* Success message styling */
        .success {
            color: #28a745;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Error message styling */
        .error {
            color: #dc3545;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Scanner area */
        #scanner {
            display: none;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #333;
        }

        /* Panel styling */
        .left-panel,
        .right-panel {
            flex: 1;
            background-color: rgba(0, 0, 0, 0.8); /* Dark background */
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin: 10px;
        }

        /* Panel heading */
        .left-panel h2,
        .right-panel h2 {
            background-color: #444; /* Darker heading background */
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        /* Hide membership details by default */
        #membership-details {
            display: none;
        }


    </style>
    <script src="https://cdn.rawgit.com/zxing/zxing/master/javascript/examples/legacy/zxing.js"></script>

</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
<div class="container">
    <form method="POST" action="trialpaymenttest.php" id="salesForm">
        <div class="left-panel">
            <?php
            if (isset($_SESSION['employeeid'])) {
                echo "<p style='font-size: 18px; color: #333; font-weight: bold; background-color: rgba(255, 255, 255, 0.8); padding: 10px; border-radius: 5px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);'>
                    Employee InCharge: <strong>" . htmlspecialchars($_SESSION['employeeid']) . "</strong>
                </p>";
            } else {
                echo "<p>Employee ID not set.</p>";
            }
            ?>

            <h1>Sales Transaction</h1>

            <label for="icnumber" style="display: block; font-weight: bold; margin-bottom: 10px;">IC Number:</label>
            <input type="text" name="icnumber" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;">

            <table id="productTable" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; background-color: #31a3c8;">
                <thead>
                    <tr style="background-color: #4CAF50; color: white; text-align: left; font-weight: bold;">
                        <th style="padding: 12px; border: 1px solid #ddd;">Barcode</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Product Name</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Quantity</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Price</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Total Price</th>
                    </tr>
                </thead>
                <tbody id="productRows">
                    <tr style="border-bottom: 1px solid #ddd;">
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #4CAF50;">
                        <td colspan="4" style="padding: 12px; text-align: right; font-weight: bold;">Total:</td>
                        <td id="totalAmount" style="padding: 12px; text-align: right; font-weight: bold;">0.00</td>
                    </tr>
                </tfoot>
            </table>

            <div id="scanner" style="margin-bottom: 20px;">
                <label for="barcode" style="display: block; font-weight: bold; margin-bottom: 5px;">Scan Product Barcode:</label>
                <input type="text" id="barcode" placeholder="Scan barcode here..." autofocus style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <div style="text-align: right;">
                    <button type="button" id="addProduct" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">Add Product</button>
                    <button type="button" id="startScanner" style="padding: 10px 15px; background-color: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">Start Barcode Scanner</button>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <h2>Customer Details</h2>
            

            <label for="membership-checkbox">Member:</label>
            <input type="checkbox" id="membership-checkbox">

            <label for="icnumber">IC Number:</label>
            <input type="text" id="icnumber" name="icnumber">
            <label for="customer-name">Customer Name:</label>
            <select id="customer-name" name="customer-name">
                <!-- Customer names will be populated here -->
            </select>

            <label for="customer-number">Customer Number:</label>
            <input type="text" id="customer-number" name="customer-number">

            <label for="customer-email">Customer Email:</label>
            <input type="email" id="customer-email" name="customer-email">

            <div id="membership-details">
                <h3>Membership Details</h3>
                <input type="text" id="membership-info" readonly>
            </div>

            <label for="voucher">Voucher:</label>
            <input type="text" id="voucher" name="voucher">
            <?php
                // Fetch services from the services table
                $services_query = "SELECT service_id, service_name, price FROM services";
                $result_services = $conn->query($services_query); // Change $connection to $conn

                // Check if the query returned results
                if ($result_services->num_rows > 0) {
                ?>
                    <label for="services">Select Service:</label>
                    <select id="services" name="services">
                        <option value="">-- Select Service --</option>
                        <?php
                        while ($row = $result_services->fetch_assoc()) {
                            echo '<option value="' . $row['service_id'] . '" data-service-name="' . $row['service_name'] . '" data-price="' . $row['price'] . '">' . $row['service_name'] . ' - ' . $row['price'] . '</option>';
                        }
                        ?>
                    </select>
                <?php
                } else {
                    echo '<option>No services available</option>';
                }
            ?>

            <h3>Totals</h3>
            <label for="subtotal">Subtotal:</label>
            <input type="number" id="subtotal" name="subtotal" readonly>

            <label for="discount">Discount (%):</label>
            <select id="discount">
                <option value="0">0</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
            </select>
            <label for="promotion">Promotion:</label>
            <select id="promotion" name="promotion">
                <option value="">-- Select Promotion --</option>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="' . $row['discount_value'] . '">' . $row['promotion_name'] . '</option>';
                }
                ?>
            </select>


            <label for="grand-total">Grand Total:</label>
            <input type="number" id="grand-total" name="grand-total" readonly>

            <h3>Payment Details</h3>
            <label for="payment-method">Select Payment Method:</label>
            <select id="payment-method" name="payment-method" onchange="togglePaymentFields()">
                <option value="">--Select--</option>
                <option value="cash">Cash</option>
                <option value="card">Debit Card</option>
                <option value="mobile">Mobile Wallet</option>
            </select>

            <div id="payment-fields">
                <!-- Dynamic payment fields will be added here -->
            </div>

            <h3>Additional Payment Option</h3>
            <label for="additional-payment-method">Select Additional Payment Method:</label>
            <select id="additional-payment-method" name="additional-payment-method" onchange="toggleAdditionalPaymentFields()">
                <option value="">--Select--</option>
                <option value="cash">Cash</option>
                <option value="card">Debit Card</option>
                <option value="mobile">Mobile Wallet</option>
            </select>

            <div id="additional-payment-fields">
                <!-- Dynamic additional payment fields will be added here -->
            </div>

            <label for="change-provided">Change Provided:</label>
            <input type="number" id="change-provided" name="change-provided">

            <input type="hidden" id="membership-info" name="membership_info">


            <button id="submit-button" type="submit" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Complete Sale</button>
        </div>
    </form>



    <div style="background-color: rgba(0, 0, 0, 0.7); padding: 20px; border-radius: 10px; box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.5); color: white;">
        <label for="log_id">Enter Log ID:</label>
        <input type="text" id="log_id" name="log_id" required style="width: calc(100% - 20px); padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <button type="button" id="importQuotation" style="padding: 10px 15px; background-color: #FFA500; color: white; border: none; border-radius: 5px; cursor: pointer;">Import Quotation</button>
    </div>




</div>
    
    <script>


        //Service javascript
        document.getElementById('services').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            // Get selected service details
            const serviceId = selectedOption.value;
            const serviceName = selectedOption.getAttribute('data-service-name');
            const servicePrice = parseFloat(selectedOption.getAttribute('data-price'));

            if (serviceId) {
                // Create a new row in the product table
                const table = document.getElementById('productRows');
                const newRow = document.createElement('tr');

                // Create cells for the row
                newRow.innerHTML = `
                    <td style="padding: 12px; border: 1px solid #ddd;">${serviceId}</td>
                    <td style="padding: 12px; border: 1px solid #ddd;">${serviceName}</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">1</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">${servicePrice.toFixed(2)}</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">${servicePrice.toFixed(2)}</td>
                `;

                table.appendChild(newRow);

                // Update the grand total
                calculateTotals()
            }
        });



        //Quotation Javascript

        document.getElementById('importQuotation').addEventListener('click', function() {
            var log_id = document.getElementById('log_id').value;
            if (log_id) {
                fetchQuotation(log_id);
            } else {
                alert('Please enter a valid Log ID.');
            }
        });

        function fetchQuotation(log_id) {
            fetch('fetch_quotation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'log_id=' + encodeURIComponent(log_id),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQuotation(data.products, data.services);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error fetching quotation:', error));
        }

        function displayQuotation(products, services) {
            var productTable = document.getElementById('productRows');
            productTable.innerHTML = ''; // Clear any existing rows

            // Display products
            products.forEach(function(product) {
                var row = `
                <tr>
                    <td></td> <!-- Empty for barcode -->
                    <td>${product.product_needed}</td>
                    <td>${product.quantity}</td>
                    <td>${product.price}</td>
                    <td>${(product.quantity * product.price).toFixed(2)}</td>
                </tr>`;
                productTable.innerHTML += row;
            });

            // Display services (optional: adjust for service-specific handling)
            services.forEach(function(service) {
                var row = `
                <tr>
                    <td></td> <!-- Empty for barcode -->
                    <td>${service.service_needed}</td>
                    <td>1</td> <!-- Quantity for service -->
                    <td>${service.price}</td>
                    <td>${service.price}</td>
                </tr>`;
                productTable.innerHTML += row;
            });

            // Call calculateTotals to update subtotal and grand total
            calculateTotals();
        }



        function togglePaymentFields() {
            const paymentMethod = document.getElementById('payment-method').value;
            const paymentFields = document.getElementById('payment-fields');

            paymentFields.innerHTML = ''; // Clear existing fields

            if (paymentMethod === 'cash') {
                paymentFields.innerHTML = `
                    <label for="cash-amount">Cash Amount:</label>
                    <input type="number" id="payment-amount" name="payment-amount" placeholder="Enter Cash Amount">
                `;
            } else if (paymentMethod === 'card') {
                paymentFields.innerHTML = `
                    <label for="card-amount">Card Amount:</label>
                    <input type="number" id="payment-amount" name="payment-amount" placeholder="Enter Card Amount">
                `;
            } else if (paymentMethod === 'mobile') {
                paymentFields.innerHTML = `
                    <label for="mobile-amount">Mobile Wallet Amount:</label>
                    <input type="number" id="payment-amount" name="payment-amount" placeholder="Enter Mobile Wallet Amount">
                `;
            }
        }

        function toggleAdditionalPaymentFields() {
            const additionalPaymentMethod = document.getElementById('additional-payment-method').value;
            const additionalPaymentFields = document.getElementById('additional-payment-fields');

            additionalPaymentFields.innerHTML = ''; // Clear existing fields

            if (additionalPaymentMethod === 'cash') {
                additionalPaymentFields.innerHTML = `
                    <label for="additional-cash-amount">Additional Cash Amount:</label>
                    <input type="number" id="additional-payment-amount" name="additional-payment-amount" placeholder="Enter Additional Cash Amount">
                `;
            } else if (additionalPaymentMethod === 'card') {
                additionalPaymentFields.innerHTML = `
                    <label for="additional-card-amount">Additional Card Amount:</label>
                    <input type="number" id="additional-payment-amount" name="additional-payment-amount" placeholder="Enter Additional Card Amount">
                `;
            } else if (additionalPaymentMethod === 'mobile') {
                additionalPaymentFields.innerHTML = `
                    <label for="additional-mobile-amount">Additional Mobile Wallet Amount:</label>
                    <input type="number" id="additional-payment-amount" name="additional-payment-amount" placeholder="Enter Additional Mobile Wallet Amount">
                `;
            }
        }
        // Handle add product button click
        document.getElementById('addProduct').addEventListener('click', async function() {
            const barcode = document.getElementById('barcode').value;
            const quantity = prompt("Enter quantity for barcode " + barcode + ":"); // Prompt for quantity
            if (quantity && !isNaN(quantity) && quantity > 0) {
                try {
                    // Fetch product name and price from the database using AJAX
                    const response = await fetch('fetch_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ barcode: barcode })
                    });
                    const data = await response.json();

                    const productName = data.product_name ? data.product_name : "Not Found"; // Default if not found
                    const productPrice = data.price ? parseFloat(data.price).toFixed(2) : "N/A"; // Convert price to number and format

                    // Calculate Total
                    const totalPrice = (productPrice !== "N/A") ? (productPrice * quantity).toFixed(2) : "N/A"; 

                    const productTable = document.getElementById('productRows');
                    const newRow = document.createElement('tr');

                    // Create cells for barcode, product name, quantity, and price
                    newRow.innerHTML = `
                        <td>${barcode}</td>
                        <td>${productName}</td>
                        <td>${quantity}</td>
                        <td>${productPrice}</td> 
                        <td>${totalPrice}</td>
                        <!-- Add price cell -->
                    `;
                    productTable.appendChild(newRow);

                    // Create hidden inputs for barcode and quantity
                    const hiddenBarcodeInput = document.createElement('input');
                    hiddenBarcodeInput.type = 'hidden';
                    hiddenBarcodeInput.name = 'barcode[]';
                    hiddenBarcodeInput.value = barcode;

                    const hiddenQuantityInput = document.createElement('input');
                    hiddenQuantityInput.type = 'hidden';
                    hiddenQuantityInput.name = 'quantity[]';
                    hiddenQuantityInput.value = quantity;

                    // Append hidden inputs to the form
                    document.getElementById('salesForm').appendChild(hiddenBarcodeInput);
                    document.getElementById('salesForm').appendChild(hiddenQuantityInput);

                    // Clear the barcode input
                    document.getElementById('barcode').value = '';

                    // Update totals after adding the product
                    calculateTotals();
                } catch (error) {
                    alert("Error fetching product details: " + error.message);
                }
            } else {
                alert("Please enter a valid quantity.");
            }
        });

        // Add this function for calculating totals
        function calculateTotals() {
            const productRows = document.querySelectorAll('#productRows tr');
            let subtotal = 0;

            if (productRows.length === 0) {
                // If there are no rows, set subtotal to 0 and return early
                document.getElementById('subtotal').value = '0.00';
                document.getElementById('grand-total').value = '0.00';
                return;
            }

            productRows.forEach(row => {
                const totalPriceCell = row.cells[4]; // Total Price column
                // Ensure the cell is defined and has a valid value
                const totalPrice = (totalPriceCell && totalPriceCell.textContent) ? parseFloat(totalPriceCell.textContent) || 0 : 0;
                subtotal += totalPrice;
            });

            document.getElementById('subtotal').value = subtotal.toFixed(2);
            updateGrandTotal(subtotal);
        }

        // Update grand total after discount
        function updateGrandTotal(subtotal) {
            const discount = parseFloat(document.getElementById('promotion').value) || 0;
            const grandTotal = subtotal - (subtotal * (discount / 100));
            document.getElementById('grand-total').value = grandTotal.toFixed(2);
        }

        document.getElementById('promotion').addEventListener('change', function() {
            const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
            updateGrandTotal(subtotal);
        });

        // Update grand total when discount changes
        document.getElementById('discount').addEventListener('change', function() {
            const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
            updateGrandTotal(subtotal);
        });

        // Handle start scanner button click
        document.getElementById('startScanner').addEventListener('click', function() {
            const scanner = document.getElementById('scanner');
            scanner.style.display = scanner.style.display === 'none' ? 'block' : 'none';
        });

        // Initialize Quagga for barcode scanning
        function startQuagga() {
            Quagga.init({
                inputStream: {
                    type: "LiveStream",
                    target: document.querySelector('#scanner'), // target element
                },
                decoder: {
                    readers: ["code_128_reader"] // Adjust as per your barcode type
                },
            }, function(err) {
                if (err) {
                    console.log(err);
                    return;
                }
                Quagga.start();
            });

            let lastDetectedBarcode = ''; // Store the last detected barcode
            let isProcessing = false; // Flag to prevent multiple processing

            // On detected barcode
            Quagga.onDetected(function(data) {
                const barcode = data.codeResult.code;

                if (barcode !== lastDetectedBarcode && !isProcessing) {
                    isProcessing = true; // Set processing flag
                    lastDetectedBarcode = barcode; // Update the last detected barcode

                    document.getElementById('barcode').value = barcode; // Set barcode input
                    document.getElementById('addProduct').click(); // Trigger add product

                    // Reset after a short delay
                    setTimeout(() => {
                        isProcessing = false; // Reset processing flag
                        lastDetectedBarcode = ''; // Reset last detected barcode
                    }, 1000); // Adjust the delay as needed
                }
            });
        }

        // Start the barcode scanner on page load
        window.onload = startQuagga;
    </script>


    <script src="quagga.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const membershipCheckbox = document.getElementById('membership-checkbox');
            const customerNameSelect = document.getElementById('customer-name');
            const membershipDetailsDiv = document.getElementById('membership-details');
            const icNumberInput = document.getElementById('icnumber');

            // Start in non-member mode
            membershipDetailsDiv.style.display = 'none';
            customerNameSelect.style.display = 'none';

            // Create a text input for the customer name (for non-members)
            let customerNameInput = document.createElement('input');
            customerNameInput.type = 'text';
            customerNameInput.id = 'customer-name-input';
            customerNameInput.name = 'customer-name';
            customerNameInput.placeholder = 'Enter customer name';
            const label = document.querySelector('label[for="customer-name"]');
            label.after(customerNameInput);

            // Populate customer names from the database on checkbox change
            membershipCheckbox.addEventListener('change', () => {
                if (membershipCheckbox.checked) {
                    // Show membership-related fields
                    membershipDetailsDiv.style.display = 'block';
                    customerNameSelect.style.display = 'block';
                    customerNameInput.style.display = 'none'; // Hide text input for customer name

                    // Clear previous customer name options
                    customerNameSelect.innerHTML = '';

                    // Fetch customer names for members
                    fetch('sales_interface.php?customer_names=1')
                        .then(response => response.json())
                        .then(customers => {
                            customers.forEach(customer => {
                                const option = document.createElement('option');
                                option.value = customer.icnumber;
                                option.textContent = customer.name;
                                customerNameSelect.appendChild(option);
                            });
                        });
                } else {
                    // Show non-member text input for customer name
                    membershipDetailsDiv.style.display = 'none';
                    customerNameSelect.style.display = 'none';
                    customerNameInput.style.display = 'block'; // Show text input for customer name
                    customerNameSelect.innerHTML = ''; // Clear any dropdown options
                }
            });

            // Handle customer name selection (for members)
            customerNameSelect.addEventListener('change', () => {
                const selectedIcNumber = customerNameSelect.value;

                if (selectedIcNumber) {
                    fetch(`sales_interface.php?icnumber=${selectedIcNumber}`)
                        .then(response => response.json())
                        .then(details => {
                            if (details.icnumber) {
                                document.getElementById('icnumber').value = details.icnumber;
                                document.getElementById('customer-email').value = details.email || '';
                                document.getElementById('customer-number').value = details.phone || '';

                                if (membershipCheckbox.checked) {
                                    fetch(`sales_interface.php?membership_details=${selectedIcNumber}`)
                                        .then(response => response.json())
                                        .then(membership => {
                                            if (membership.membership_id) {
                                                document.getElementById('membership-info').value =
                                                    `ID: ${membership.membership_id}, Type: ${membership.membership_type}, Expiry: ${membership.membership_expirydate}`;
                                            } else {
                                                document.getElementById('membership-info').value = 'No membership found';
                                            }
                                        });
                                }
                            } else {
                                alert('Customer not found!');
                            }
                        });
                } else {
                    document.getElementById('icnumber').value = '';
                    document.getElementById('customer-email').value = '';
                    document.getElementById('customer-number').value = '';
                }
            });

            // Handle IC number input change
            icNumberInput.addEventListener('change', () => {
                const icnumber = icNumberInput.value;
                fetch(`sales_interface.php?icnumber=${icnumber}`)
                    .then(response => response.json())
                    .then(details => {
                        if (details.icnumber) {
                            document.getElementById('customer-email').value = details.email || '';
                            document.getElementById('customer-number').value = details.phone || '';
                        } else {
                            alert('Customer not found!');
                        }
                    });
            });
        });
    </script>


</body>
</html>
