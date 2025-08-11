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
    <title>Expense Tracking Form</title>
 <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background-image: url('darkwallpaper.jpg'); /* Add the path to your wallpaper */
        background-size: cover;
        background-position: center;
        color: #e0e0e0; /* Light text color */
    }

    /* Sidebar Styling */
    #sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        z-index: 1;
        top: 0;
        left: -250px; /* Initially hidden */
        background-color: rgba(44, 62, 80, 0.9); /* Slightly transparent dark background */
        transition: 0.3s;
        padding-top: 60px;
    }
    #sidebar a {
        padding: 15px 20px;
        text-decoration: none;
        font-size: 18px;
        color: #e0e0e0; /* Light text */
        display: block;
        transition: 0.3s;
    }
    #sidebar a:hover {
        background-color: #575757; /* Lighter dark hover effect */
    }

    /* Adjust the main content */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background-color: rgba(0, 0, 0, 0.8); /* Dark translucent background */
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); /* Stronger shadow for dark theme */
        margin-left: 250px; /* Push content to the right to make space for the sidebar */
    }

    #sidebar .sidebar-header {
            font-size: 24px;
            color: #FFF;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }

    #main-content {
        margin-left: 250px; /* Adjust to sidebar width */
    }

    /* Table styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        border-radius: 8px;
        overflow: hidden;
        background-color: rgba(50, 50, 50, 0.9); /* Dark translucent table background */
        color: #e0e0e0; /* Light text */
    }

    table th,
    table td {
        padding: 12px 15px;
        text-align: center;
        font-size: 1rem;
        border-bottom: 1px solid #444; /* Darker border */
    }

    table th {
        background-color: #1e88e5; /* Blue header background */
        color: #fff; /* White text */
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: rgba(70, 70, 70, 0.8); /* Slightly lighter dark background */
    }

    table tr:hover {
        background-color: rgba(90, 90, 90, 0.8); /* Lighter hover effect */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        table {
            font-size: 0.9rem;
        }

        select {
            width: 100%;
            margin-bottom: 10px;
        }

        .container {
            padding: 15px;
            margin-left: 0; /* Remove margin for small screens */
        }

        h1 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        button {
            width: 100%;
        }

        table th,
        table td {
            padding: 8px 10px;
        }

        .total-tax {
            font-size: 1rem;
        }
    }

    /* Select dropdown */
    select {
        width: 200px;
        padding: 10px;
        font-size: 1rem;
        border-radius: 5px;
        border: 1px solid #555; /* Darker border */
        margin-right: 20px;
        background-color: #333; /* Dark background */
        color: #e0e0e0; /* Light text */
        transition: all 0.3s ease;
    }

    select:focus {
        outline: none;
        border-color: #4A90E2;
        background-color: rgba(50, 50, 50, 0.9); /* Slightly darker focus background */
    }

    /* Total tax section */
    .total-tax {
        font-size: 1.2rem;
        margin: 10px 0;
        font-weight: bold;
        color: #e0e0e0; /* Light text */
        text-align: center;
    }

    /* Button */
    button {
        background-color: #4A90E2; /* Blue button background */
        color: white;
        font-size: 1rem;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: block;
        margin: 20px auto;
    }

    button:hover {
        background-color: #357ABD; /* Darker blue on hover */
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

</head>
<body>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <div id="sidebar">
        <div class="sidebar-header"><i class="fa fa-calculator"></i> <i class="fa fa-chart-line"></i>
          <i class="fa fa-money-bill-wave"></i></div>
        <a href="accdashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
        <a href="acccashflowstatement.php"><i class="fa fa-money-bill-wave"></i> Cash Flow Statement</a>
        <a href="accexpensesform.php"><i class="fa fa-edit"></i> Expenses Form</a>
        <a href="accexpensesapproval.php"><i class="fa fa-check-square"></i> Expenses Approval</a>
        <a href="accviewexpenses.php"><i class="fa fa-file-invoice-dollar"></i> View Expenses</a>
        <a href="accledger.php"><i class="fa fa-bookmark"></i> View Ledger</a>
        <a href="acctaxreport.php"><i class="fa fa-calculator"></i> Tax Report Table</a>
        <a href="mainpage.php"><i class="fa fa-sign-out-alt"></i> Exit</a>
    </div>

<div class="container">

<h1>Tax Report</h1>
<button id="exportButton">Export to Excel</button>
    <h2></h2>
    <table id="taxTable">
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

<script>

    // Get sidebar and main content
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");

    // Open the sidebar and push the main content
    sidebar.style.left = "0";
    mainContent.style.marginLeft = "250px"; // Adjust this value to the width of your sidebar

</script>

<script>
    // Export to Excel functionality
    document.getElementById('exportButton').addEventListener('click', function() {
        var table = document.getElementById('taxTable');
        var wb = XLSX.utils.table_to_book(table, { sheet: "SalesData" });
        XLSX.writeFile(wb, 'sales_report.xlsx');
    });
</script>


</body>
</html>
