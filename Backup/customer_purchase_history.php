<?php

session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);

// Initialize a variable for holding results
$results = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Database connection (replace with your database credentials)
    $conn = new mysqli('localhost', 'root', '', 'pos');

    if ($conn->connect_error) 
    {
        die('Connection failed: ' . $conn->connect_error);
    }

    $icnumber = $_POST['icnumber'];

        // Fetch the customer name based on IC number
    $customerNameQuery = "SELECT name FROM customerregistration WHERE icnumber = ?";
    $stmt1 = $conn->prepare($customerNameQuery);
    $stmt1->bind_param('s', $icnumber);
    $stmt1->execute();
    $customerResult = $stmt1->get_result();

    $customerName = '';
    if ($customerResult->num_rows > 0) 
    {
        $customerRow = $customerResult->fetch_assoc();
        $customerName = $customerRow['name']; // Get the customer's name
    } else 
    {
        $customerName = 'Customer not found';
    }




    // Prepare to fetch transaction details from sales_payments table based on IC number
    $salesPaymentsQuery = "SELECT * FROM sales_payments WHERE icnumber = ?";
    $stmt = $conn->prepare($salesPaymentsQuery);
    $stmt->bind_param('s', $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = ''; // Initialize results variable

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) 
        {

            $results .= '<div class="transaction-container">';
            $results .= '<div class="transaction-details">';
            
            // Display the date and time of purchase
            $created_at = date('Y-m-d H:i:s', strtotime($row['created_at'])); // Format as desired
            $results .= '<strong>Date and Time of Purchase:</strong> ' . $created_at . '<br><br>';
            
            $results .= '<strong>Customer Name:</strong> ' . htmlspecialchars($customerName) . '<br>';
            $results .= '<strong>IC Number:</strong> ' . $row['icnumber'] . '<br>';
            $results .= '<strong>Customer Number:</strong> ' . $row['customer_number'] . '<br>';
            $results .= '<strong>Customer Email:</strong> ' . $row['customer_email'] . '<br>';
            $results .= '<strong>Subtotal:</strong> ' . $row['subtotal'] . '<br>';
            $results .= '<strong>Grand Total:</strong> ' . $row['grand_total'] . '<br>';
            $results .= '<strong>Payment Method:</strong> ' . $row['payment_method'] . '<br>';
            $results .= '<strong>Payment Amount:</strong> ' . $row['payment_amount'] . '<br>';
            $results .= '<strong>Additional Payment Method:</strong> ' . $row['additional_payment_method'] . '<br>';
            $results .= '<strong>Additional Payment Amount:</strong> ' . $row['additional_payment_amount'] . '<br>';
            
            $results .= '</div>';

            // Use created_at from sales_payments to fetch matching records in sales_transaction
            $salesTransactionQuery = "
                SELECT st.*, 
                       CONCAT(pr.brand, ' - ', pr.producttype, ' - ', pr.variant) AS product_name 
                FROM sales_transaction st
                JOIN productinventorytable pr ON st.barcode = pr.barcode
                WHERE st.sale_date = ?
            ";
            $stmt2 = $conn->prepare($salesTransactionQuery);
            $stmt2->bind_param('s', $created_at);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            $results .= '<table>';
            $results .= '<tr><th>Barcode</th><th>Product Name</th><th>Quantity</th><th>Total Price</th></tr>';
            while ($item = $result2->fetch_assoc()) {
                $results .= '<tr>';
                $results .= '<td>' . $item['barcode'] . '</td>';
                $results .= '<td>' . htmlspecialchars($item['product_name']) . '</td>'; // Display product name
                $results .= '<td>' . $item['quantity'] . '</td>';
                $results .= '<td>' . $item['total_price'] . '</td>';
                $results .= '</tr>';
            }
            $results .= '</table>';
            $results .= '</div>'; // Close transaction-container
        }
    } 
    else 
    {
        $results = 'No transactions found for this IC number.';
    }

    $stmt->close();
    $conn->close();

    // Return only the results, not the entire HTML
    echo $results;
    exit; // Ensure no further code is executed
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Purchase History</title>
    <style>
    /* Importing Poppins font from Google Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
        font-family: 'Poppins', sans-serif; /* Using Poppins font */
        background: url('darkwallpaper.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f4f4f4; /* Light text color */
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #2a2a2a; /* Dark container */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Subtle shadow for depth */
    }

    .search-bar {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .search-bar input {
        padding: 10px;
        width: 300px;
        border: 1px solid #444; /* Darker border */
        border-radius: 4px;
        margin-right: 10px;
        background-color: #3a3a3a; /* Dark input background */
        color: #f4f4f4; /* Light text color in input */
        font-family: 'Poppins', sans-serif; /* Consistent font */
        transition: border-color 0.3s; /* Smooth border color transition */
    }

    .search-bar input:focus {
        border-color: #007BFF; /* Change border color on focus */
        outline: none; /* Remove default outline */
    }

    .search-bar button {
        padding: 10px;
        background-color: #007BFF; /* Blue button */
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600; /* Make button text bold */
        transition: background-color 0.3s; /* Smooth background color transition */
    }

    .search-bar button:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .transaction-container {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #444; /* Darker border */
        border-radius: 5px;
        background-color: #2a2a2a; /* Dark background for transactions */
        display: flex; /* Use flexbox for layout */
        flex-direction: column; /* Stack items vertically */
    }

    .transaction-details {
        margin-bottom: 15px; /* More space between transaction details */
        line-height: 1.5; /* Increased line height for readability */
    }

    .transaction-details h4 {
        margin: 0 0 10px 0; /* Add margin for heading */
        font-weight: 600; /* Bold heading */
        color: #007BFF; /* Optional: Change color for visibility */
    }

    .transaction-details p {
        margin: 5px 0; /* Spacing between paragraphs */
        color: #f4f4f4; /* Light text color */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table, th, td {
        border: 1px solid #444; /* Darker border for table */
    }

    th, td {
        padding: 10px; /* Slightly increased padding for better spacing */
        text-align: left;
    }

    th {
        background-color: #3a3a3a; /* Dark background for headers */
        color: #f4f4f4; /* Light text color for headers */
        font-weight: 600; /* Make header text bold */
    }
</style>

</head>

<body>
        
<div class="container">
    <h2>Customer Purchase History</h2>
    <div class="search-bar" style="display: flex; align-items: center; gap: 10px;">
        <input type="text" id="icnumber" placeholder="Enter IC Number" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <button onclick="fetchHistory()" 
            style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;">
            Search
        </button>
        <button 
            onclick="window.location.href='mainpage.php'" 
            style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;">
            Go to Main Page
        </button>
    </div>
    <div id="history-container"></div>
</div>

<script>
    function fetchHistory() {
        var icnumber = document.getElementById("icnumber").value;

        if (icnumber === "") {
            alert("Please enter IC Number.");
            return;
        }

        // Make an AJAX request to fetch the data
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("history-container").innerHTML = xhr.responseText;
            }
        };
        xhr.send("icnumber=" + encodeURIComponent(icnumber));
    }
</script>

</body>
</html>
