<?php

    
    // USER CONTROL CODE

    session_start();
    $employee_id = $_SESSION['employeeid'] ?? null;
    $role_name = $_SESSION['role_name'] ?? null;
    $current_page = basename($_SERVER['PHP_SELF']);  // Get current file name

    // Database connection
    $host = 'localhost';
    $db = 'pos';
    $user = 'root';
    $pass = '';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

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

    // Close statement and connection
    $stmt->close();
    $conn->close();

    









    // Database configuration
    $servername = "localhost";  // Change this to your server
    $username = "root";         // Change this to your database username
    $password = "";  // Change this to your database password
    $dbname = "POS";           // Change this to your database name

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $icnumber = $_POST['icnumber'];
        $customerid = $_POST['customerid'];
        $dateofbirth = $_POST['dateofbirth'];
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $membership = $_POST['membership'];

        // Insert customer details into the customerregistration table
        $stmt = $conn->prepare("INSERT INTO customerregistration (customerid, name, icnumber, dateofbirth, gender, email, phone, address, membership) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $customerid, $name, $icnumber, $dateofbirth, $gender, $email, $phone, $address,    $membership);
        
        if ($stmt->execute()) {
            // Check if the customer wants to become a member
            if ($membership === 'yes') {
                $membership_id = $_POST['membership_id'];
                $membership_expirydate = $_POST['membership_expirydate'];
                $membership_points = $_POST['membership_points'];
                $membership_type = $_POST['membership_type'];

                // Insert membership details into the customermemberships table
                $stmt = $conn->prepare("INSERT INTO customermemberships (icnumber, membership_id, membership_expirydate, membership_points, membership_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $icnumber, $membership_id, $membership_expirydate, $membership_points, $membership_type);
                $stmt->execute();
            }

            echo "Customer registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Generate ID functionality
    if (isset($_GET['generate'])) {
        $type = $_GET['generate'];

        // Fetch ID settings from the database
        $customerid_settings_query = "SELECT * FROM customerid_settings WHERE id_type = '$type' LIMIT 1";
        $customerid_settings_result = $conn->query($customerid_settings_query);

        if ($customerid_settings_result->num_rows > 0) {
            $id_settings = $customerid_settings_result->fetch_assoc();
        } else {
            die("Error: Could not fetch ID settings.");
        }

        // Function to generate random unique number
        function getRandomUniqueNumber($conn, $table, $column, $length = 4) {
            do {
                // Generate a random unique number
                $unique_number = generateRandomNumber($length);

                // Check if the number already exists in the table
                $query = "SELECT COUNT(*) AS count FROM $table WHERE $column = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $unique_number);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $exists = $row['count'] > 0;

                // If the number does not exist, break the loop
            } while ($exists);

            return $unique_number;
        }

        // Helper function to generate a random number
        function generateRandomNumber($length) {
            // Generate a random number with the specified length
            return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
        }

        if ($type == 'customer') {
            $prefix = $id_settings['prefix'];
            $custom_code = $id_settings['custom_code'];
            $unique_number = getRandomUniqueNumber($conn, 'customerregistration', 'customerid', 4); // 4 characters for random part
            echo generateCustomId($prefix, $custom_code, $unique_number);
        } elseif ($type == 'membership') {
            $prefix = $id_settings['prefix'];
            $custom_code = $id_settings['custom_code'];
            $unique_number = getRandomUniqueNumber($conn, 'customermemberships', 'membership_id', 4); // 4 characters for random part
            echo generateCustomId($prefix, $custom_code, $unique_number);
        } else {
            echo "Invalid type.";
        }

        // Close the database connection
        $conn->close();
        exit;
    }

    // Function to generate custom ID
// Function to generate custom ID
function generateCustomId($prefix, $custom_code, $unique_number, $length = 12) {
    // Ensure total length is 12 characters
    $remaining_length = $length - strlen($prefix) - strlen($custom_code);
    return str_pad($prefix, strlen($prefix), '0', STR_PAD_LEFT) 
         . str_pad($custom_code, $remaining_length - strlen($unique_number), '0', STR_PAD_LEFT) 
         . str_pad($unique_number, strlen($unique_number), '0', STR_PAD_LEFT);
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: margin-left 0.3s;
            position: relative;
            margin: 0 auto;
            width: 100%;
            max-width: 1200px;
        }

        .side-nav {
            width: 250px;
            background-color: rgba(44, 44, 44, 0.8);
            padding: 20px;
            position: fixed;
            top: 30px;
            bottom: 0;
            left: 0;
            color: #fff;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s;
        }

        .side-nav.open {
            transform: translateX(0);
        }

        .hover-trigger {
            position: fixed;
            top: 0;
            left: 0;
            width: 20px;
            height: 100%;
            background: transparent;
            cursor: pointer;
            z-index: 1000;
        }

        .main-content {
            margin-left: 100px;
            margin-top: 50px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s;
            position:relative;
        }

        .main-content.active {
            margin-left: 250px; /* Change from 0 to 250px to keep the content fixed */
            width: calc(100% - 250px);
        }

        header.header {
            padding: 2px 10px;
            height: 30px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        header .logo img {
            height: 30px;
        }

        header .company-name {
            flex-grow: 1;
            text-align: center;
            font-size: 16px;
        }

        header .employee-account {
            text-align: right;
            font-size: 12px;
        }

        .registration-container {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
            margin: 80px auto 40px;
            position: relative;
        }

        .registration-container h2 {
            margin-top: 0;
            font-size: 28px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #fff;
        }

        .form-group input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }

        .form-group input[type="radio"] + label {
            display: inline;
        }

        button {
            padding: 12px 20px;
            background-color: #444;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>  
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

            <!-- Registration Container -->
            <div class="registration-container">
                <form id="registration-form" method="post">
                    <h2>Register Your Details</h2>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="icnumber">IC Number:</label>
                        <input type="text" id="icnumber" name="icnumber" required>
                    </div>

                    <div class="form-group">
                        <label for="customerid">Customer ID:</label>
                        <input type="text" id="customerid" name="customerid" required>
                        <button type="button" onclick="generateId('customer')">Generate ID</button>
                    </div>

                    <div class="form-group">
                        <label for="dateofbirth">Date of Birth:</label>
                        <input type="date" id="dateofbirth" name="dateofbirth" required>
                    </div>

                    <div class="form-group">
                        <label>Gender:</label>
                        <input type="radio" id="male" name="gender" value="Male">
                        <label for="male">Male</label>
                        <input type="radio" id="female" name="gender" value="Female">
                        <label for="female">Female</label>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone:(+60)</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <!-- Membership Option -->
                    <div class="form-group">
                        <label for="membership">Become a member:</label>
                        <select id="membership" name="membership" onchange="toggleMembershipQuestions()">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    
                    <div id="membership-questions" style="display: none;">
                        <!-- Membership-related questions -->
                        <div class="form-group">
                            <label for="membership_id">Membership ID:</label>
                            <input type="text" id="membership_id" name="membership_id">
                            <button type="button" onclick="generateId('membership')">Generate ID</button>
                        </div>
                        <div class="form-group">
                            <label for="membership_expirydate">Membership Expiry Date:</label>
                            <input type="date" id="membership_expirydate" name="membership_expirydate">
                        </div>
                        <div class="form-group">
                            <label for="membership_points">Membership Points:</label>
                            <input type="text" id="membership_points" name="membership_points">
                        </div>
                        <div class="form-group">
                            <label for="membership_type">Membership Type:</label>
                            <input type="text" id="membership_type" name="membership_type">
                        </div>
                    </div>

                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('hover-trigger').addEventListener('mouseover', function() {
            document.getElementById('side-nav').classList.add('open');
            document.getElementById('main-content').classList.add('active');
        });

        document.getElementById('side-nav').addEventListener('mouseleave', function() {
            document.getElementById('side-nav').classList.remove('open');
            document.getElementById('main-content').classList.remove('active');
        });

        function toggleMembershipQuestions() {
            var membershipValue = document.getElementById('membership').value;
            document.getElementById('membership-questions').style.display = membershipValue === 'yes' ? 'block' : 'none';
        }

        function generateId(type) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '?generate=' + type, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var generatedId = xhr.responseText;
                    if (type === 'customer') {
                        document.getElementById('customerid').value = generatedId;
                    } else if (type === 'membership') {
                        document.getElementById('membership_id').value = generatedId;
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
