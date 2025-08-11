<?php
// Database connection
$servername = "localhost"; // Change as needed
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "pos"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

 session_start();
    $employee_id = $_SESSION['employeeid'] ?? null;
    $role_name = $_SESSION['role_name'] ?? null;
    $current_page = basename($_SERVER['PHP_SELF']);  // Get current file name

// Check if user is logged in
    if (!$employee_id) {
        // User is not logged in, redirect to login page
        header("Location: login.php");
        exit();
    }

    // Check if the user role has permission to access the current page
    $query = "SELECT * FROM roles_permission WHERE role_name = ? AND permission_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $role_name, $current_page);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // User doesn't have permission, redirect to unauthorized page
        header("Location: unauthorized.php");  // Replace with your unauthorized access page
        exit();
    }




// Calculate commissions and transaction count
$commission_rate = 0.05; // 5% commission
$selected_month = date('m'); // Default to current month
$selected_year = date('Y'); // Default to current year

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_month = $_POST['month'];
    $selected_year = $_POST['year'];
}

// Prepare SQL query with filtering by month and year
$sql = "
    SELECT 
        e.employeeid,
        e.fullname,
        COUNT(sp.transaction_id) AS transaction_count,
        SUM(sp.grand_total * $commission_rate) AS total_commission
    FROM 
        employeedetails e
    LEFT JOIN 
        sales_payments sp ON e.employeeid = sp.employeeid
    WHERE 
        MONTH(sp.created_at) = ? AND YEAR(sp.created_at) = ?
    GROUP BY 
        e.employeeid
    ORDER BY 
        total_commission DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Commissions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            background-color: rgba(44, 62, 80, 0.8); /* Semi-transparent background */
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #34495e;
        }
        th {
            background-color: rgba(52, 73, 94, 0.9); /* Slightly transparent */
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: rgba(59, 89, 152, 0.7); /* Semi-transparent */
        }
        tr:hover {
            background-color: rgba(41, 98, 121, 0.8); /* Hover effect */
        }
        tr:first-child {
            background-color: #2c3e50; /* Highlight header row */
        }
        .total-commission {
            font-weight: bold;
        }
    </style>

</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <h1>Employee Commissions</h1>

    <form method="post" style="margin-bottom: 20px;">
        <label for="month" style="color: white; font-weight: bold; margin-right: 10px;">Select Month:</label>
        <select name="month" id="month" style="padding: 10px; border: 1px solid #34495e; border-radius: 4px; background-color: #2c3e50; color: white;">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="year" style="color: white; font-weight: bold; margin-left: 20px; margin-right: 10px;">Select Year:</label>
        <select name="year" id="year" style="padding: 10px; border: 1px solid #34495e; border-radius: 4px; background-color: #2c3e50; color: white;">
            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo ($y == $selected_year) ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit" style="padding: 10px 15px; background-color: #2980b9; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background-color 0.3s;">
            Filter
        </button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Transaction Perform</th>
                <th>Total Commission (Default 5%)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['employeeid']); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['transaction_count']); ?></td>
                        <td class="total-commission"> RM <?php echo number_format($row['total_commission'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // You can add any JavaScript functionality here if needed
    </script>
</body>
</html>

<?php


$stmt = null;  // Free up the statement resources
$conn->close();

?>
