<?php
// Start session and check if employeeid is set
session_start();
if (!isset($_SESSION['employeeid'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Database connection (replace with your actual database details)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the employeeid from the session
$employee_id = $_SESSION['employeeid'];

// Fetch the username based on the employeeid from the usersystem table
$sql = "SELECT username FROM usersystem WHERE employeeid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->bind_result($employee_name);
$stmt->fetch();
$stmt->close();

// Fetch products with stock less than 10
$stock_sql = "SELECT brand, producttype, variant, stock FROM productinventorytable WHERE stock < 10";
$stock_result = $conn->query($stock_sql);

$low_stock_items = [];
if ($stock_result->num_rows > 0) {
    // Fetch all products with stock less than 10
    while ($row = $stock_result->fetch_assoc()) {
        $product_name = $row['brand'] . " " . $row['producttype'] . " " . $row['variant'];
        $low_stock_items[] = ['name' => $product_name, 'stock' => $row['stock']];
    }
}

// Fetch promotions from the promotions table
// Fetch promotions that have not yet expired (where end_date is today or in the future)
$promotion_sql = "SELECT promotion_name, description, start_date, end_date 
                  FROM promotions 
                  WHERE end_date >= CURDATE()";
$promotion_result = $conn->query($promotion_sql);

$promotions = [];
if ($promotion_result->num_rows > 0) {
    while ($row = $promotion_result->fetch_assoc()) {
        $promotions[] = [
            'promotion_name' => $row['promotion_name'],
            'description' => $row['description'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
}




$sql_company_name = "SELECT business_name from businessregistration LIMIT 1";
$result_company_name = $conn->query($sql_company_name);

$company_name = "";
if ($result_company_name->num_rows > 0) {
    // Output data of the first row
    $row_company_name = $result_company_name->fetch_assoc();
    $company_name = $row_company_name["business_name"];
} else {
    $company_name = "Company Name Not Found"; // Fallback if no data is found
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>POS System - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #f4f4f4; /* Light text color */
        }

        h1 {
            color: #f4f4f4; /* Light color for heading */
            margin-top: 50px; /* Space from top */
            text-align: center; /* Centered heading */
        }

        .company-name {
            font-size: 1.5rem; /* Slightly larger font for company name */
            color: #f4f4f4; /* Light color for company name */
            margin-bottom: 20px; /* Space below company name */
            text-align: center; /* Centered company name */
        }

        .welcome-message {
            font-size: 1.2rem;
            color: #ccc; /* Slightly lighter gray for the welcome message */
            text-align: center; /* Centered welcome message */
            margin-bottom: 30px; /* Space below welcome message */
        }

        .button-container {
            display: flex;
            justify-content: center; /* Center buttons */
            margin-bottom: 20px; /* Space below buttons */

            margin-left: 30px; /* Move the container to the right */
        }

        .button-container a {
            display: inline-block;
            padding: 15px 30px;
            margin: 5px;
            background-color: #007BFF; /* Primary button color */
            color: white;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .button-container a:hover {
            background-color: #0056b3; /* Darker shade for hover effect */
        }

        /* Floating stock widget on the left */
        .stock-widget {
            position: absolute;
            left: 20px;
            top: 100px; /* Move it down a bit */
            width: 300px;
            background-color: rgba(50, 50, 50, 0.9); /* Dark semi-transparent background */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            padding: 20px;
            text-align: left;
            height: 200px; /* Fixed height */
            overflow-y: auto; /* Scrollable */
        }

        .stock-widget h2 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #f4f4f4; /* Light color for widget heading */
        }

        .stock-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8d7da; /* Light background for stock item */
            border-left: 5px solid #dc3545; /* Red border */
            color: #721c24; /* Darker text color for stock item */
            font-size: 0.9rem;
        }

        .stock-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .promotion-container {
            position: absolute;
            right: 20px;
            top: 100px; /* Move it down a bit */
            display: flex;
            justify-content: center; /* Center the promotions */
            height: 200px; /* Match the height of the card */
            overflow: hidden; /* Hide overflow for smooth transitions */
        }

        .promotion {
            display: none; /* Hide all promotions by default */
            background-color: rgba(50, 50, 50, 0.9);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            padding: 20px;
            width: 300px; /* Fixed width */
            height: 200px; /* Fixed height */
            transition: opacity 0.5s ease; /* Smooth transition */
        }

        .active {
            display: block; /* Show the active promotion */
            opacity: 1; /* Fully opaque */
        }

        .promotion h3 {
            margin: 0;
            color: #f4f4f4; /* Light color for heading */
        }

        .promotion p {
            color: #ccc; /* Light color for description */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <div class="stock-widget">
        <h2>Stock Monitor</h2>
        <?php if (!empty($low_stock_items)): ?>
            <?php foreach ($low_stock_items as $item): ?>
                <div class="stock-item">
                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                    Stock left: <?php echo $item['stock']; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>All objects are in stock!</p>
        <?php endif; ?>
    </div>

    <div class="container">
        <br>
        <h1><div class="company-name"><?php echo htmlspecialchars($company_name); ?></div></h1>
        <p class="welcome-message">Hello User, <?php echo htmlspecialchars($employee_name); ?>. Welcome to the POS System.</p>

        <div class="button-container">
            <a href="barcodetestpayment.php"><i class="fa fa-shopping-cart"></i>Start Sale</a>
            <a href="viewinventory.php"><i class="fa fa-warehouse"></i>Inventory</a>
            <a href="customer_log.php"><i class="fa fa-clipboard-list"></i>Open Log</a>
            <a href="quickinvoice.php"><i class="fa fa-receipt"></i>Quick Invoice</a>
        </div>
    </div>

    <div class="promotion-container" style="display: flex; justify-content: flex-end; align-items: center; height: 300px; overflow: hidden; position: relative; top:-20px;">
        <?php if (count($promotions) > 0): ?>
            <?php foreach ($promotions as $index => $promotion): ?>
                <div class="promotion <?php echo $index === 0 ? 'active' : ''; ?>"
                     style="
                         display: <?php echo $index === 0 ? 'block' : 'none'; ?>; /* Show only the active promotion */
                         background-color: rgba(255, 255, 255, 0.9); 
                         border-radius: 12px; 
                         box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); 
                         padding: 20px; 
                         width: 320px;
                         height: 250px; 
                         transition: transform 0.3s ease, opacity 0.5s ease; 
                         position: absolute; 
                         text-align: center;
                         opacity: 0.9;

                     "
                     onmouseover="this.style.boxShadow='0 12px 24px rgba(0, 0, 0, 0.5)'; this.style.transform='scale(1.05';"
                     onmouseout="this.style.boxShadow='0 8px 16px rgba(0, 0, 0, 0.3)'; this.style.transform='scale(1)';">
                     <h2 style="color: black;">Promotional Ads</h2>
                    <h3 style="margin: 0; color: #333; font-size: 1.5em; font-family: 'Arial', sans-serif;"><?php echo htmlspecialchars($promotion['promotion_name']); ?></h3>
                    <p style="color: #666; font-size: 1em; font-family: 'Arial', sans-serif; margin: 10px 0;"><?php echo htmlspecialchars($promotion['description']); ?></p>
                    <p style="color: #007BFF; font-weight: bold; font-size: 0.9em;">Start Date: <span style="color: #333;"><?php echo htmlspecialchars($promotion['start_date']); ?></span></p>
                    <p style="color: #FF5733; font-weight: bold; font-size: 0.9em;">End Date: <span style="color: #333;"><?php echo htmlspecialchars($promotion['end_date']); ?></span></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666; font-size: 1em; font-family: 'Arial', sans-serif;">No current promotions.</p>
        <?php endif; ?>
    </div>

    <script>
        // Get all promotion elements
        const promotions = document.querySelectorAll('.promotion');
        let currentIndex = 0;

        // Function to show the next promotion
        function showNextPromotion() {
            // Hide the current promotion
            promotions[currentIndex].style.display = 'none'; // Hide current promotion
            
            // Move to the next index
            currentIndex = (currentIndex + 1) % promotions.length; // Loop back to start

            // Show the next promotion
            promotions[currentIndex].style.display = 'block'; // Show next promotion
        }

        // Set an interval to change promotions every 5 seconds
        setInterval(showNextPromotion, 5000); // 5000 milliseconds = 5 seconds
    </script>


    <script>
        // Get all promotion elements
        const promotions = document.querySelectorAll('.promotion');
        let currentIndex = 0;

        function showNextPromotion() {
            // Hide the current promotion
            promotions[currentIndex].classList.remove('active');
            
            // Move to the next index
            currentIndex = (currentIndex + 1) % promotions.length; // Loop back to start

            // Show the next promotion
            promotions[currentIndex].classList.add('active');
        }

        // Set an interval to change promotions every 5 seconds
        setInterval(showNextPromotion, 5000); // 5000 milliseconds = 5 seconds
    </script>


    <script>
        const promotions = document.querySelectorAll('.promotion');
        let currentIndex = 0;

        function showNextPromotion() {
            promotions[currentIndex].classList.remove('active'); // Hide current promotion
            currentIndex = (currentIndex + 1) % promotions.length; // Move to the next promotion
            promotions[currentIndex].classList.add('active'); // Show next promotion
        }

        if (promotions.length > 0) {
            setInterval(showNextPromotion, 5000); // Change promotion every 5 seconds
        }
    </script>
</body>
</html>
