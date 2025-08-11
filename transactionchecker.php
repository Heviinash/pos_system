<?php
// Database connection parameters
$host = 'localhost'; // Change if necessary
$db = 'pos'; // Replace with your database name
$user = 'root'; // Replace with your database username
$pass = ''; // Replace with your database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$transaction_id = '';
$result_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];

    // Prepare SQL queries
    $query_transaction = $conn->prepare("SELECT * FROM sales_transaction WHERE transaction_id = ?");
    $query_payments = $conn->prepare("SELECT * FROM sales_payments WHERE transaction_id = ?");

    // Bind parameters and execute
    $query_transaction->bind_param("s", $transaction_id);
    $query_transaction->execute();
    $transaction_result = $query_transaction->get_result();

    $query_payments->bind_param("s", $transaction_id);
    $query_payments->execute();
    $payments_result = $query_payments->get_result();

    // Check results
    if ($transaction_result->num_rows > 0 && $payments_result->num_rows > 0) {
        $result_message = "Transaction ID '$transaction_id' exists in both tables.";
    } else {
        $result_message = "Transaction ID '$transaction_id' does not exist in both tables.";
    }

    // Close statements
    $query_transaction->close();
    $query_payments->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Checker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="submit"] {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        input[type="submit"]:hover {
            background: #4cae4c;
        }
        .result {
            margin-top: 20px;
            font-weight: bold;
            color: #d9534f;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Check Transaction ID</h1>
        <form method="post" action="">
            <input type="text" name="transaction_id" placeholder="Enter Transaction ID" required>
            <input type="submit" value="Check">
        </form>
        <div class="result"><?php echo $result_message; ?></div>
    </div>
</body>
</html>
