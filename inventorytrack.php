<?php
session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);


// Sample database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "pos"; // Change as needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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





// Query to fetch all inventory details
$sql = "SELECT 
            CONCAT(brand, ' ', producttype, ' ', variant) AS product_name, 
            last_restocked_by, 
            product_added_by,
            stock, 
            created_at, 
            updated_at 
        FROM productinventorytable";
$result = $conn->query($sql);

// Query to fetch the latest product added
$latestAddedSql = "SELECT 
                        CONCAT(brand, ' ', producttype, ' ', variant) AS product_name, 
                        product_added_by, 
                        created_at 
                    FROM productinventorytable 
                    ORDER BY created_at DESC LIMIT 1";
$latestAddedResult = $conn->query($latestAddedSql);
$latestAdded = $latestAddedResult->fetch_assoc();

// Query to fetch the latest product restocked
$latestRestockedSql = "SELECT 
                            CONCAT(brand, ' ', producttype, ' ', variant) AS product_name, 
                            last_restocked_by, 
                            updated_at 
                        FROM productinventorytable 
                        WHERE last_restocked_by IS NOT NULL 
                        ORDER BY updated_at DESC LIMIT 1";
$latestRestockedResult = $conn->query($latestRestockedSql);
$latestRestocked = $latestRestockedResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            border-radius: 10px; /* Optional: rounded corners for the table */
        }
        th, td {
            border: 1px solid #4CAF50;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }

        .latest-section {
            margin: 20px auto; /* Center align */
            padding: 20px; /* Add more padding for spacing */
            border: 1px solid #4CAF50; /* Green border */
            background-color: #2c3e50; /* Darker background for contrast */
            color: #fff; /* White text color */
            border-radius: 15px; /* Rounded corners */
            width: 80%; /* Adjust the width */
            max-width: 600px; /* Max width for larger screens */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); /* Subtle shadow for depth */
            display: none; /* Initially hidden */
            position: fixed; /* Fixed position for better view */
            right: 20px; /* Position from the right */
            top: 20px; /* Position from the top */
            z-index: 100; /* Above other elements */
            transition: transform 0.3s ease, opacity 0.3s ease; /* Smooth transition for showing/hiding */
            opacity: 0; /* Initially hidden */
        }

        .latest-section.open {
            display: block; /* Show when open */
            opacity: 1; /* Fade-in effect */
            transform: translateX(0); /* Reset transform when open */
        }

        .toggle-button {
            cursor: pointer;
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 20px auto;
            display: block;
            width: 200px; /* Fixed width for button */
            text-align: center;
        }

        .toggle-button:hover {
            background-color: #45a049; /* Slightly darker green on hover */
        }

        .toggle-button:active {
            background-color: #388e3c; /* Darker green when pressed */
        }

        /* Optional: Add a smooth close animation for the section */
        .latest-section .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5em;
            color: #fff;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .latest-section .close-btn:hover {
            color: #e74c3c; /* Red color on hover */
        }

    </style>
</head>
<body>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>


    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <h1>Inventory Management</h1>

    
    <button class="toggle-button" id="toggleLatest">Toggle Latest Products</button>

    <button style="background-color: darkcyan; /* Green */
            border: none;
            color: white;
            padding: 12px 24px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;" onclick="downloadExcel()">Download Excel Report
    </button>


    <div class="latest-section" id="latestProducts">
        <h2>Latest Product Added</h2>
        <?php if ($latestAdded): ?>
            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($latestAdded['product_name']); ?></p>
            <p><strong>Added By:</strong> <?php echo htmlspecialchars($latestAdded['product_added_by']); ?></p>
            <p><strong>Added At:</strong> <?php echo htmlspecialchars($latestAdded['created_at']); ?></p>
        <?php else: ?>
            <p>No products added yet.</p>
        <?php endif; ?>

        <h2>Latest Product Restocked</h2>
        <?php if ($latestRestocked): ?>
            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($latestRestocked['product_name']); ?></p>
            <p><strong>Restocked By:</strong> <?php echo htmlspecialchars($latestRestocked['last_restocked_by']); ?></p>
            <p><strong>Restocked At:</strong> <?php echo htmlspecialchars($latestRestocked['updated_at']); ?></p>
        <?php else: ?>
            <p>No products have been restocked yet.</p>
        <?php endif; ?>
    </div>

    <table id="inventoryTable">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Current Stock</th>
                <th>Product Restocked By</th>
                <th>Product Added by</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['stock']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_restocked_by']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_added_by']); ?></td>
                        <td>
                            <?php 
                                $createdDate = new DateTime($row['created_at']);
                                echo htmlspecialchars($createdDate->format('jS M y h:i a'));
                            ?>
                        </td>
                        <td>
                            <?php 
                                $updatedDate = new DateTime($row['updated_at']);
                                echo htmlspecialchars($updatedDate->format('jS M y h:i a'));
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No inventory records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        const toggleButton = document.getElementById('toggleLatest');
        const latestProducts = document.getElementById('latestProducts');

        toggleButton.addEventListener('click', function() {
            latestProducts.classList.toggle('open'); // Toggle the open class
        });
    </script>

    <script>
        function downloadExcel() {
            // Get the HTML table by ID
            var table = document.getElementById("inventoryTable");  // Replace with your table ID
            
            // Use SheetJS to convert the table to a workbook
            var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});

            // Create and download the Excel file
            XLSX.writeFile(wb, "inventory_report.xlsx");
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
