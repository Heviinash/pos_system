<?php
// Database connection
$host = 'localhost';  // Your database host
$dbname = 'pos';  // Your database name
$username = 'root';  // Your database username
$password = '';  // Your database password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if transaction_id is provided
if (isset($_GET['transaction_id']) && !empty($_GET['transaction_id'])) {
    $transaction_id = $_GET['transaction_id'];

    // Query for sales_transaction table
    $sql_transaction = "SELECT employeeid, icnumber, barcode, quantity, total_price, sale_date 
                        FROM sales_transaction WHERE transaction_id = ?";
    $stmt_transaction = $conn->prepare($sql_transaction);
    $stmt_transaction->bind_param("i", $transaction_id);
    $stmt_transaction->execute();
    $result_transaction = $stmt_transaction->get_result();

    // Query for sales_payment table
    $sql_payment = "SELECT subtotal, discount, grand_total, payment_method, payment_amount, 
                          additional_payment_method, additional_payment_amount, change_provided
                    FROM sales_payments WHERE transaction_id = ?";
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->bind_param("i", $transaction_id);
    $stmt_payment->execute();
    $result_payment = $stmt_payment->get_result();

    // Check if results are found
    if ($result_transaction->num_rows > 0 && $result_payment->num_rows > 0) {
        // Fetch data from sales_transaction table
        $transaction_data = $result_transaction->fetch_assoc();
        // Fetch data from sales_payment table
        $payment_data = $result_payment->fetch_assoc();

        // Display results
        echo "<h2>Transaction Details</h2>";
        echo "<h3>Sales Transaction</h3>";
        echo "<p><strong>Employee ID:</strong> " . $transaction_data['employeeid'] . "</p>";
        echo "<p><strong>IC Number:</strong> " . $transaction_data['icnumber'] . "</p>";
        echo "<p><strong>Barcode:</strong> " . $transaction_data['barcode'] . "</p>";
        echo "<p><strong>Quantity:</strong> " . $transaction_data['quantity'] . "</p>";
        echo "<p><strong>Total Price:</strong> " . $transaction_data['total_price'] . "</p>";
        echo "<p><strong>Sale Date:</strong> " . $transaction_data['sale_date'] . "</p>";

        echo "<h3>Sales Payment</h3>";
        echo "<p><strong>Subtotal:</strong> " . $payment_data['subtotal'] . "</p>";
        echo "<p><strong>Discount:</strong> " . $payment_data['discount'] . "</p>";
        echo "<p><strong>Grand Total:</strong> " . $payment_data['grand_total'] . "</p>";
        echo "<p><strong>Payment Method:</strong> " . $payment_data['payment_method'] . "</p>";
        echo "<p><strong>Payment Amount:</strong> " . $payment_data['payment_amount'] . "</p>";
        echo "<p><strong>Additional Payment Method:</strong> " . $payment_data['additional_payment_method'] . "</p>";
        echo "<p><strong>Additional Payment Amount:</strong> " . $payment_data['additional_payment_amount'] . "</p>";
        echo "<p><strong>Change Provided:</strong> " . $payment_data['change_provided'] . "</p>";

    } else {
        echo "<p>No records found for Transaction ID: " . htmlspecialchars($transaction_id) . "</p>";
    }

    // Close prepared statements
    $stmt_transaction->close();
    $stmt_payment->close();
} else {
    echo "<p>Please enter a valid Transaction ID.</p>";
}

// Close connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Transaction</title>
</head>
<body>
    <h2>Search Transaction by Transaction ID</h2>
    <form action="testtransaction.php" method="get">
        <label for="transaction_id">Transaction ID:</label>
        <input type="text" id="transaction_id" name="transaction_id" required>
        <button type="submit">Search</button>
    </form>
</body>
</html>
