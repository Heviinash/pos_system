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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        /* Container for the entire layout */
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        /* Form container */
        .form-container {
            background: rgba(0, 0, 0, 0.8); /* Dark semi-transparent background */
            color: #fff; /* White text color for better readability */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); /* Slightly more pronounced shadow for better depth */
            max-width: 600px;
            margin: 20px auto;
        }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #fff; /* White label text color */
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #666; /* Light border color */
            background-color: #333; /* Dark input background */
            color: #fff; /* White input text */
        }
        button {
            padding: 10px 15px;
            background-color: #444; /* Dark button background */
            color: #fff; /* White button text */
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #555; /* Slightly lighter button background on hover */
        }
        .radio-group label {
            display: block;
            margin-bottom: 5px;
            margin-right: 20px;
            color: #fff; /* White label text color */
        }

        /* Delete button styling */
        .delete-button {
            width: 24px; /* Adjust width as needed */
            height: 24px; /* Adjust height as needed */
            font-size: 14px; /* Adjust font size as needed */
            line-height: 24px; /* Center text vertically */
            text-align: center; /* Center text horizontally */
            background-color: #d9534f; /* Red background for delete */
            color: #fff; /* White text */
            border: none;
            border-radius: 4px; /* Rounded corners */
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c9302c; /* Darker red on hover */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    
    
    <!-- Main container -->
    <div class="container">

        <!-- Page Content -->
        <main style="padding: 20px;">
            <div class="form-container">
                <h2>Set ID Format</h2>

                <?php
                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "POS";

                $conn = new mysqli($servername, $username, $password, $dbname);

                // Very Important Code for Time Generation
                date_default_timezone_set('Asia/Kuala_Lumpur');

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id_type = $_POST['id_type'];
                    $prefix = strtoupper($_POST['prefix']);
                    $custom_code_option = $_POST['custom_code_option'];

                    if ($custom_code_option === 'date') {
                        $custom_code = date('ymd'); // Current date in YYMMDD format
                    } else {
                        $custom_code = strtoupper($_POST['custom_code']);
                    }

                    // Check if settings already exist for the ID type
                    $stmt = $conn->prepare("INSERT INTO customerid_settings (id_type, prefix, custom_code) VALUES (?, ?, ?)
                                            ON DUPLICATE KEY UPDATE prefix=?, custom_code=?");
                    $stmt->bind_param("sssss", $id_type, $prefix, $custom_code, $prefix, $custom_code);

                    if ($stmt->execute()) {
                        echo "<p>Settings saved successfully!</p>";
                        echo "<p>Server date and time: " . date('Y-m-d H:i:s') . "</p>";
                    } else {
                        echo "<p>Error saving settings: " . $stmt->error . "</p>";
                    }

                    $stmt->close();
                }

                $conn->close();
                ?>

                <form action="" method="post">
                    <div class="form-group">
                        <label for="id_type">ID Type:</label>
                        <select id="id_type" name="id_type" required>
                            <option value="customer">Customer ID </option>
                            <option value="membership">Customer Membership</option>
                            <option value="company">Company ID</option>
                            <option value="companymembership">Company Membership</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prefix">Prefix (2 characters):</label>
                        <input type="text" id="prefix" name="prefix" maxlength="2" required>
                    </div>
                    <label for="custom_code_option">Custom Code Option:</label>
                    <div class="radio-group" style="display: flex; align-items: center;">
                        <label style="margin-right: 20px;">
                            <input type="radio" id="use_custom_code" name="custom_code_option" value="custom" checked style="margin-right: 5px;">
                            Custom Code
                        </label>
                        <label>
                            <input type="radio" id="use_date" name="custom_code_option" value="date" style="margin-right: 5px;">
                            Use Current Date
                        </label>
                    </div>

                    <br>

                    <div class="form-group" id="custom_code_container">
                        <label for="custom_code">Custom Code (6 characters):</label>
                        <input type="text" id="custom_code" name="custom_code" maxlength="6">
                    </div>
                    <button type="submit">Save Settings</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Toggle custom code input based on radio selection
        document.querySelectorAll('input[name="custom_code_option"]').forEach((elem) => {
            elem.addEventListener('change', function() {
                const customCodeContainer = document.getElementById('custom_code_container');
                if (this.value === 'date') {
                    customCodeContainer.style.display = 'none';
                } else {
                    customCodeContainer.style.display = 'block';
                }
            });
        });

        // Set default to custom code
        document.getElementById('use_custom_code').dispatchEvent(new Event('change'));

        document.addEventListener('DOMContentLoaded', () => {
            const hoverTrigger = document.getElementById('hover-trigger');
            const sideNav = document.getElementById('side-nav');

            // Show side navigation on hover
            hoverTrigger.addEventListener('mouseenter', () => {
                sideNav.style.left = '0';
            });

            // Hide side navigation when the cursor leaves it
            sideNav.addEventListener('mouseleave', () => {
                sideNav.style.left = '-250px';
            });
        });
    </script>
</body>
</html>
