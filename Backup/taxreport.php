<?php
// Database connection
$servername = "localhost";
$username = "root"; // replace with your DB username
$password = ""; // replace with your DB password
$dbname = "pos"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch sales payments data
$sql = "SELECT transaction_id, subtotal, grand_total, created_at FROM sales_payments";
$result = $conn->query($sql);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Sales Payments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Sales Payments</h2>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Subtotal</th>
                <th>Grand Total</th>
                <th>Created At</th>
                <th>Tax (6%)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Reconnect to the database and fetch the data
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $transaction_id = $row['transaction_id'];
                    $subtotal = $row['subtotal'];
                    $grand_total = $row['grand_total'];
                    $created_at = $row['created_at'];

                    // Calculate 6% tax
                    $tax = $subtotal * 0.06;
                    ?>

                    <tr>
                        <td><?php echo $transaction_id; ?></td>
                        <td><?php echo number_format($subtotal, 2); ?></td>
                        <td><?php echo number_format($grand_total, 2); ?></td>
                        <td><?php echo $created_at; ?></td>
                        <td><?php echo number_format($tax, 2); ?></td>
                    </tr>

                    <?php
                }
            } else {
                echo "<tr><td colspan='5'>No records found</td></tr>";
            }

            // Close the connection
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
