<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos";

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







// Fetch customer logs with follow-up dates after the current date wont show
$sql = "SELECT log_id, name, icnumber, contact_info, problem_desc, followupdate, DATE(created_at) AS created_at FROM testcustomer_logs WHERE followupdate >= CURDATE()";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Follow-Up Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background for table */
            color: #fff;
        }
        th, td {
            padding: 12px;
            border: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #222; /* Darker background for table header */
            color: #ddd; /* Lighter text for better readability */
        }
        td {
            background-color: rgba(255, 255, 255, 0.1); /* Subtle transparency for table rows */
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Slight hover effect for rows */
        }
        button {
            background-color: #4CAF50; /* Green button */
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s ease; /* Smooth hover transition */
        }
        button:hover {
            background-color: #45a049; /* Slightly darker green on hover */
        }
    </style>

</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

<h1>Customer Follow-Up Logs</h1>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>IC Number</th>
            <th>Contact Info</th>
            <th>Problem Description</th>
            <th>Follow-Up Date</th>
            <th>Visited Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['icnumber']; ?></td>
                    <td><?php echo $row['contact_info']; ?></td>
                    <td><?php echo $row['problem_desc']; ?></td>
                    <td><?php echo $row['followupdate']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <button onclick="WhatsappCustomer('<?php echo $row['contact_info']; ?>')">Message</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No follow-up logs available.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function WhatsappCustomer(contactInfo) {
    const message = "Hello! This is a follow-up regarding your recent visit.";
    
    // Check if the contactInfo starts with a country code
    let phoneNumber = contactInfo;

    // Assuming the Malaysian country code is 60 and local numbers start with 0
    if (!phoneNumber.startsWith('60')) {
        // Remove leading 0 if present and add country code
        if (phoneNumber.startsWith('0')) {
            phoneNumber = '60' + phoneNumber.substring(1);
        } else {
            phoneNumber = '60' + phoneNumber; // Add country code directly if no leading zero
        }
    }
    
    const whatsappURL = `https://api.whatsapp.com/send?phone=${phoneNumber}&text=${encodeURIComponent(message)}`;
    
    // Open WhatsApp in a new tab/window
    window.open(whatsappURL, '_blank');
}


</script>

</body>
</html>

<?php
$conn->close();
?>
