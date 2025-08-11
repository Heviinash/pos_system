<?php
// Database connection (adjust credentials as needed)
$conn = new mysqli("localhost", "root", "", "pos");
session_start();

// Check if the 'file' parameter is passed for download
if (isset($_GET['file'])) {
    $file = $_GET['file'];  // Get the file name
    $filePath = 'uploads/' . basename($file);  // Ensure the file is from 'uploads' folder

    if (file_exists($filePath)) {
        // Set headers to force the file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);  // Output the file content
        exit;
    } else {
        echo "File not found!";
    }
}

// Process form submission if any
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id']; // Use 'id' instead of 'expense_id'
    $action = $_POST['action'];
    $admin_id = $_SESSION['employeeid']; // Using employeeid from session

    // Fetch expense details
    $query = "SELECT * FROM expenses WHERE id = $id";
    $result = $conn->query($query);
    $expense = $result->fetch_assoc();

    if ($expense) {
        if ($action === 'approve') {
            // Update status to Approved
            $query = "UPDATE expenses 
                      SET status = 'Approved', approved_by = $admin_id, approved_at = NOW() 
                      WHERE id = $id"; // Use 'id' instead of 'expense_id'
            $conn->query($query);

            // Deduct from running balance
            $amount = $expense['amount'];
            $balance_update = "UPDATE running_balance 
                               SET balance = balance - $amount 
                               WHERE id = 1"; // Assuming single balance account
            $conn->query($balance_update);
        } elseif ($action === 'acknowledge') {
            // Update status to Rejected
            $query = "UPDATE expenses 
                      SET status = 'Acknowledge', approved_by = $admin_id, approved_at = NOW() 
                      WHERE id = $id"; // Use 'id' instead of 'expense_id'
            $conn->query($query);
        }
    }
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all pending expenses
$query = "SELECT * FROM expenses WHERE status = 'Pending'";
$pending_expenses = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Approval Dashboard</title>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-image: url('darkwallpaper.jpg'); /* Add the path to your wallpaper */
        background-size: cover;
        background-position: center;
        margin: 0;
        padding: 0;
        display: flex;
        color: #e0e0e0; /* Light text color for readability */
    }

    #sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: rgba(44, 62, 80, 0.95); /* Semi-transparent dark background */
        transition: 0.3s;
        padding-top: 60px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
    }

    #sidebar a {
        padding: 15px 20px;
        text-decoration: none;
        font-size: 18px;
        color: #e0e0e0; /* Light text color */
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

    #main-content {
        margin-left: 250px; /* Ensure content isn't hidden behind sidebar */
        padding: 20px;
        width: calc(100% - 250px); /* Adjust width to avoid overlapping */
        box-sizing: border-box;
        background: rgba(0, 0, 0, 0.7); /* Semi-transparent dark overlay */
        color: #e0e0e0; /* Light text color */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    h1 {
        text-align: center;
        background-color: #007BFF;
        color: white;
        padding: 20px;
        margin: 0;
        border-radius: 5px;
    }

    table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
        background-color: rgba(50, 50, 50, 0.9); /* Dark table background */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        color: #e0e0e0;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border: 1px solid rgba(255, 255, 255, 0.1); /* Subtle borders */
    }

    th {
        background-color: #007BFF; /* Header with blue background */
        color: white;
    }

    tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.05); /* Subtle alternating row background */
    }

    tr:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    button {
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
        border: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    .approve-btn {
        background-color: #28a745;
        color: white;
    }

    .approve-btn:hover {
        background-color: #218838;
    }

    .reject-btn {
        background-color: #dc3545;
        color: white;
    }

    .reject-btn:hover {
        background-color: #c82333;
    }

    .download-btn {
        display: inline-block;
        background-color: #17a2b8;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
    }

    .download-btn:hover {
        background-color: #138496;
    }
    .acknowledge-btn {
        background-color: #ffc107;
        color: white;
    }

    .acknowledge-btn:hover {
        background-color: #e0a800;
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

    <div id="main-content">
        <h1>Expense Approval Dashboard</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Expense Type</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Notes</th>
                    <th>Attachments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $pending_expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['expense_type']) ?></td>
                        <td><?= htmlspecialchars(number_format($row['amount'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['payment']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['vendor']) ?></td>
                        <td><?= htmlspecialchars($row['notes']) ?></td>
                        <td>
                            <a href="?file=<?= urlencode($row['attachments']) ?>" class="download-btn">Download Attachments</a>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                                <br>
                                <br>
                                <button type="submit" name="action" value="acknowledge" class="reject-btn">Acknowledge</button>
                                <br>
                                <br>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
