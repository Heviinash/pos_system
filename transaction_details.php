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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$transaction_id = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];
}

if (!empty($transaction_id)) {
    // Fetch data from sales_payments
    $payment_query = "SELECT * FROM sales_payments WHERE transaction_id = ?";
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("s", $transaction_id);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    
    if ($payment_result->num_rows > 0) {
        $payment_data = $payment_result->fetch_assoc();
        
        // Get the IC number
        $icnumber = $payment_data['icnumber'];

        // Fetch customer name based on IC number
        $customer_query = "SELECT name FROM customerregistration WHERE icnumber = ?";
        $customer_stmt = $conn->prepare($customer_query);
        $customer_stmt->bind_param("s", $icnumber);
        $customer_stmt->execute();
        $customer_stmt->bind_result($customer_name);
        $customer_stmt->fetch();
        $customer_stmt->close();
    } else {
        echo "No payment information found for this transaction.";
        exit;
    }

    $business_query = "SELECT * FROM businessregistration LIMIT 1";
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->execute();
    $business_info = $business_stmt->get_result()->fetch_assoc();
    $business_stmt->close();

    // Fetch data from sales_transaction
    $transaction_query = "SELECT barcode, quantity, total_price FROM sales_transaction WHERE transaction_id = ?";
    $transaction_stmt = $conn->prepare($transaction_query);
    $transaction_stmt->bind_param("s", $transaction_id);
    $transaction_stmt->execute();
    $transaction_result = $transaction_stmt->get_result();

    if ($transaction_result->num_rows > 0) {
        // Initialize subtotal
        $subtotal = 0;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Transaction Receipt</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f6f8;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: #fff;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    border-radius: 8px;
                }
                .receipt-header, .receipt-info {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .receipt-header h1 {
                    font-size: 28px;
                    color: #4CAF50;
                    margin: 0;
                }
                .receipt-header p {
                    margin: 4px 0;
                    font-size: 14px;
                }
                .home-button {
                    background-color: #4CAF50;
                    color: white;
                    padding: 10px 20px;
                    text-align: center;
                    border: none;
                    border-radius: 4px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background 0.3s ease;
                    margin-bottom: 20px;
                }
                .home-button:hover {
                    background-color: #45a049;
                }
                .search-form, .receipt-info {
                    margin-bottom: 20px;
                }
                .search-form input[type="text"], .search-form input[type="submit"] {
                    padding: 10px;
                    font-size: 16px;
                    border-radius: 4px;
                    border: 1px solid #ccc;
                    margin-top: 10px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th, td {
                    padding: 12px;
                    border: 1px solid #ddd;
                    text-align: center;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    color: #333;
                }
                .total {
                    font-size: 18px;
                    font-weight: bold;
                    color: #333;
                    text-align: right;
                    margin-top: 10px;
                }
                .receipt-link {
                    font-size: 14px;
                    color: #888;
                    text-align: center;
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <button class="home-button" onclick="window.location.href='mainpage.php'">Home Page</button>
                <div class="receipt-link">
                    <p>Link: http://localhost/DevelopmentProject/transaction_details.php?transaction_id=(Enter Transaction ID)</p>
                </div>
                <div class="search-form">
                    <form method="post" action="">
                        <label for="transaction_id">Enter Transaction ID:</label>
                        <input type="text" name="transaction_id" id="transaction_id" required>
                        <input type="submit" value="Search">
                    </form>
                </div>
                <div class="receipt-header">
                    <h1>Receipt</h1>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
                    <p><strong><?php echo htmlspecialchars($business_info['business_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($business_info['address']); ?></p>
                    <p><?php echo htmlspecialchars($business_info['contact_number']); ?></p>
                    <p><?php echo htmlspecialchars($business_info['email_address']); ?></p>
                </div>
                <div class="receipt-info">
                    <h2>Payment Information for</h2>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                    <p><strong>Customer IC:</strong> <?php echo htmlspecialchars($icnumber); ?></p>
                    <p><strong>Payment Type:</strong> <?php echo htmlspecialchars($payment_data['payment_method']); ?></p>
                    <p><strong>Amount Paid:</strong> RM <?php echo number_format($payment_data['payment_amount'], 2); ?></p>
                    <?php if (!empty($payment_data['additional_payment_method']) && $payment_data['additional_payment_amount'] != 0.00): ?>
                        <p><strong>Additional Payment Type:</strong> <?php echo htmlspecialchars($payment_data['additional_payment_method']); ?></p>
                        <p><strong>Additional Amount Paid:</strong> RM <?php echo number_format($payment_data['additional_payment_amount'], 2); ?></p>
                    <?php endif; ?>
                    <p><strong>Change:</strong> RM <?php echo number_format($payment_data['change_provided'], 2); ?></p>
                </div>
                <h3>Product Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Price (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Loop through each transaction and display product details
                        while ($transaction_data = $transaction_result->fetch_assoc()) {
                            // Fetch product name details from productinventorytable based on barcode
                            $barcode = $transaction_data['barcode'];
                            $product_query = "SELECT brand, producttype, variant FROM productinventorytable WHERE barcode = ?";
                            $product_stmt = $conn->prepare($product_query);
                            $product_stmt->bind_param("s", $barcode);
                            $product_stmt->execute();
                            $product_stmt->bind_result($brand, $producttype, $variant);
                            $product_stmt->fetch();
                            $product_name = htmlspecialchars($brand . ' ' . $producttype . ' ' . $variant);
                            $product_stmt->close();

                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($barcode) . '</td>';
                            echo '<td>' . $product_name . '</td>';
                            echo '<td>' . htmlspecialchars($transaction_data['quantity']) . '</td>';
                            echo '<td>' . number_format($transaction_data['total_price'], 2) . '</td>';
                            echo '</tr>';

                            // Add to subtotal
                            $subtotal += $transaction_data['total_price'];
                        }
                        ?>
                    </tbody>
                </table>
                <p class="total">Subtotal: RM <?php echo number_format($subtotal, 2); ?></p>
                <p class="total">Grand Total: RM <?php echo number_format($payment_data['grand_total'], 2); ?></p>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "No transaction found with this ID.";
        exit;
    }
    $payment_stmt->close();
    $transaction_stmt->close();
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Transaction Receipt</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f6f8;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }
            .search-form input[type="text"], .search-form input[type="submit"] {
                padding: 10px;
                font-size: 16px;
                border-radius: 4px;
                border: 1px solid #ccc;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Transaction Receipt</h1>
            <div class="search-form">
                <form method="post" action="">
                    <label for="transaction_id">Enter Transaction ID:</label>
                    <input type="text" name="transaction_id" id="transaction_id" required>
                    <input type="submit" value="Search">
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}
$conn->close();
?>
