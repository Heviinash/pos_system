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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if we have a transaction id session
    if (!isset($_SESSION['transaction_id'])) {
        $_SESSION['transaction_id'] = uniqid('trans_'); // Generate a unique transaction ID
    }

    $barcodes = $_POST['barcode'];
    $quantities = $_POST['quantity'];
    $icnumber = $_POST['icnumber'];
    $transaction_id = $_SESSION['transaction_id']; // Get transaction ID from session

    foreach ($barcodes as $index => $barcode) {
        $quantity = $quantities[$index];

        // Fetch product details
        $product_query = "SELECT price, wholesaleprice, stock FROM productinventorytable WHERE barcode = ?";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product && $product['stock'] >= $quantity) {
            $total_price = $product['price'] * $quantity;

            // Insert into sales_transaction table
            $sales_query = "INSERT INTO newsales_transaction (transaction_id, employeeid, barcode, quantity, icnumber, total_price) VALUES (?, ?, ?, ?, ?, ?)";
            $sales_stmt = $conn->prepare($sales_query);
            $sales_stmt->bind_param("sssisd", $transaction_id, $_SESSION['employeeid'], $barcode, $quantity, $icnumber, $total_price);
            
            if ($sales_stmt->execute()) {
                // Update stock in productinventorytable
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

        // Close statements
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
    echo "<p class='success'>Transaction recorded successfully!</p>";
}
?>

<!-- HTML form for submitting transaction -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Handler</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file if needed -->
</head>
<body>
    <h1>Sales Transaction</h1>
    <form id="salesForm" method="POST">
        <label for="icnumber">IC Number:</label>
        <input type="text" name="icnumber" required>

        <table id="productRows">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Product</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="barcode[]" required></td>
                    <td><input type="text" name="product[]" readonly></td>
                    <td><input type="number" name="quantity[]" required></td>
                </tr>
            </tbody>
        </table>

        <button type="button" id="addProduct">Add Product</button>
        <button type="submit">Complete Sales</button>
    </form>

    <script>
        document.getElementById('addProduct').addEventListener('click', function() {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="text" name="barcode[]" required></td>
                <td><input type="text" name="product[]" readonly></td>
                <td><input type="number" name="quantity[]" required></td>
            `;
            document.querySelector('#productRows tbody').appendChild(newRow);
        });
    </script>
</body>
</html>
