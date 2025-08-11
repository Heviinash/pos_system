<?php
    // Database connection (replace with your actual database credentials)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pos";  // Replace with your actual database name

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch sales data from the sales_payments table, ordered by created_at (including time)
    $sales_sql = "SELECT grand_total, created_at FROM sales_payments ORDER BY created_at ASC";
    $sales_result = $conn->query($sales_sql);

    // Fetch expense data from the expenses table, only approved expenses, ordered by updated_at
    $expenses_sql = "SELECT expense_type, amount, updated_at FROM expenses WHERE status = 'Approved' ORDER BY updated_at ASC";
    $expenses_result = $conn->query($expenses_sql);

    $inflows_total = 0;
    $outflows_total = 0;

    // Calculate total sales (inflows)
    if ($sales_result->num_rows > 0) {
        while ($row = $sales_result->fetch_assoc()) {
            $grand_total = $row['grand_total'];
            $inflows_total += $grand_total;  // Add sales total to inflows
        }
    }

    // Calculate total expenses (outflows)
    if ($expenses_result->num_rows > 0) {
        while ($row = $expenses_result->fetch_assoc()) {
            $amount = $row['amount'];
            $outflows_total += $amount;  // Add expense amount to outflows
        }
    }

    // Calculate Net Cash Flow
    $net_cash_flow = $inflows_total - $outflows_total;

    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Ledger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #E0E0E0;
        }
        .container {
            margin-left: 260px; /* Account for sidebar width */
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            max-width: 90%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #333;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #444;
            font-size: 17px;
        }
        th {
            background-color: #555;
            color: #FFF;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            color: #ddd;
        }
        tr:nth-child(even) {
            background-color: #444;
        }
        tr:nth-child(odd) {
            background-color: #333;
        }
        tr:hover {
            background-color: #666;
        }
        tr:nth-child(odd):nth-child(n+3), tr:nth-child(even):nth-child(n+4) {
            border-bottom: 2px solid #666;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .ledger-title {
            text-align: center;
            font-family: 'Arial', sans-serif;
            font-size: 36px;
            font-weight: bold;
            color: #FFF;
            text-transform: uppercase;
            margin-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        #sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: rgba(44, 62, 80, 0.95);
            overflow-x: hidden;
            padding-top: 20px;
            transition: 0.3s;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        #sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: #e0e0e0;
            display: block;
            transition: 0.3s;
        }
        #sidebar a:hover {
            background-color: #575757;
        }
        #sidebar .sidebar-header {
            font-size: 24px;
            color: #FFF;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        @media screen and (max-width: 768px) {
            #sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .container {
                margin-left: 0;
                max-width: 100%;
            }
        }
    </style>
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
        <h2>Cash Flow Statement</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Cash Inflows (Sales)</strong></td>
                    <td>RM <?php echo number_format($inflows_total, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Cash Outflows (Expenses)</strong></td>
                    <td>RM <?php echo number_format($outflows_total, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Net Cash Flow</strong></td>
                    <td>RM <?php echo number_format($net_cash_flow, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
