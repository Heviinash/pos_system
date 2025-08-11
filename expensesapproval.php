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
        } elseif ($action === 'reject') {
            // Update status to Rejected
            $query = "UPDATE expenses 
                      SET status = 'Rejected', approved_by = $admin_id, approved_at = NOW() 
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
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #333;
            padding: 20px 0;
            background-color: #007BFF;
            color: white;
            margin: 0;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
            transition: background-color 0.3s ease;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
            transition: background-color 0.3s ease;
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
            font-size: 14px;
        }

        .download-btn:hover {
            background-color: #138496;
        }

        form {
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Expense Approval Dashboard</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Expense Type</th>
                <th>Amount</th>
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
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['vendor']) ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                    <td>
                        <!-- This links directly to the same file with the 'file' parameter -->
                        <a href="?file=<?= urlencode($row['attachments']) ?>" class="download-btn">Download Attachments</a>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>"> <!-- Use 'id' instead of 'expense_id' -->
                            <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                            <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
