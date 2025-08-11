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
    // Get form data

    $transaction_id = $_SESSION['transaction_id'] ?? null; // Assuming these are set in the session
    $employeeid = $_SESSION['employeeid'] ?? null;
    $icnumber = $_POST['icnumber'] ?? '';
    $customer_name = $_POST['customer-name'] ?? '';
    $customer_number = $_POST['customer-number'] ?? '';
    $customer_email = $_POST['customer-email'] ?? '';
    $membership_info = $_POST['membership-info'] ?? '';
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