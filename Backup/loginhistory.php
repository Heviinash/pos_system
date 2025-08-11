<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize employee filter variable
$employee_filter = '';

// Check if filters are applied
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_filter = isset($_POST['employee']) ? $_POST['employee'] : '';
}

// Build the WHERE clause for the query
$where_sql = '';
if ($employee_filter) {
    $where_sql = "WHERE ed.fullname LIKE '%" . $conn->real_escape_string($employee_filter) . "%'";
}

// Fetch employee names for the dropdown
$employee_query = "SELECT DISTINCT fullname FROM employeedetails";
$employee_result = $conn->query($employee_query);

// Fetch login history with employee name and filter applied
$query = "
    SELECT 
        lh.employeeid, 
        lh.login_time, 
        lh.logout_time, 
        ed.fullname
    FROM login_history lh
    JOIN employeedetails ed ON lh.employeeid = ed.employeeid
    $where_sql
    ORDER BY lh.login_time DESC
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #f4f4f4; /* Light text color */
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #333;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        table th {
            background-color: #2c3e50;
            color: #fff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }

        .filters {
            margin-bottom: 20px;
            text-align: center;
        }

        .filters label {
            margin-right: 10px;
            color: #fff;
        }

        .filters select {
            padding: 8px;
            margin: 5px;
            border: 1px solid #444;
            background-color: #333;
            color: #f4f4f4;
        }

        .filters button {
            padding: 8px 15px;
            background-color: #2c3e50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: #34495e;
        }

    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <br>
    <br>
    <br>

    <div class="container">
        <h1>Login History</h1>

        <form method="POST" class="filters">
            <label for="employee">Employee:</label>
            <select name="employee" id="employee">
                <option value="">All Employees</option>
                <?php while ($row = $employee_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['fullname']); ?>" <?php echo ($row['fullname'] === $employee_filter) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['fullname']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['employeeid']); ?></td>
                        <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                        <td><?php echo $row['logout_time'] ? htmlspecialchars($row['logout_time']) : 'Not logged out yet'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
$conn->close();
?>
