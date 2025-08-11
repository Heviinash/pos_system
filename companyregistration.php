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
        header("Location: index.php");
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



    






date_default_timezone_set('Asia/Kuala_Lumpur'); // Set timezone to Malaysia

// Database configuration
$servername = "localhost";  // Change this to your server
$username = "root";         // Change this to your database username
$password = "";             // Change this to your database password
$dbname = "POS";            // Change this to your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $companyname = $_POST['companyname'];
    $companyssmform = $_POST['companyssmform'];
    $companyregistration = $_POST['companyregistration'];
    $companyid = $_POST['companyid'];
    $industrytype = $_POST['industrytype'];
    $companyemail = $_POST['companyemail'];
    $companyphone = $_POST['companyphone'];
    $contactpersonname = $_POST['contactpersonname'];
    $contactpersonmobile = $_POST['contactpersonmobile'];
    $contactpersonemail = $_POST['contactpersonemail'];
    $contactpersonname2 = $_POST['contactpersonname2'];
    $contactpersonmobile2 = $_POST['contactpersonmobile2'];
    $contactpersonemail2 = $_POST['contactpersonemail2'];
    $membership = $_POST['membership'];
    $banktype = $_POST['banktype'];
    $accountnumber = $_POST['accountnumber'];

    $stmt = $conn->prepare("INSERT INTO companyregistration (companyname, companyssmform, companyregistration, companyid, industrytype, companyemail, companyphone, contactpersonname, contactpersonmobile, contactpersonemail, contactpersonname2, contactpersonmobile2, contactpersonemail2, membership, banktype, accountnumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssss", $companyname, $companyssmform, $companyregistration, $companyid, $industrytype, $companyemail, $companyphone, $contactpersonname, $contactpersonmobile, $contactpersonemail, $contactpersonname2, $contactpersonmobile2, $contactpersonemail2, $membership, $banktype, $accountnumber);
    
    if ($stmt->execute()) {
        // Check if the customer wants to become a member
        if ($membership === 'yes') {
            $membership_id = $_POST['companymembership_id'];
            $membership_expirydate = $_POST['membership_expirydate'];
            $membership_points = $_POST['membership_points'];
            $membership_type = $_POST['membership_type'];

            // Insert membership details into the companymemberships table
            $stmt = $conn->prepare("INSERT INTO companymemberships (companyregistration, companymembership_id, membership_expirydate, membership_points, membership_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $companyregistration, $membership_id, $membership_expirydate, $membership_points, $membership_type);
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

    if ($type == 'company') {
        $prefix = $id_settings['prefix'];
        $custom_code = $id_settings['custom_code']; // Read the custom code
        $unique_number = getRandomUniqueNumber($conn, 'companyregistration', 'companyid', 4); // 4 characters for random part
        echo generateCustomId($prefix, $custom_code, $unique_number);
    } elseif ($type == 'companymembership') {
        $prefix = $id_settings['prefix'];
        $custom_code = $id_settings['custom_code']; // Read the custom code
        $unique_number = getRandomUniqueNumber($conn, 'companymemberships', 'companymembership_id', 4); // 4 characters for random part
        echo generateCustomId($prefix, $custom_code, $unique_number);
    } else {
        echo "Invalid type.";
    }

    // Close the database connection
    $conn->close();
    exit;
}

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
            width: 90%;
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
    <div class="hover-trigger" id="hover-trigger"></div>
    
        <!-- Main Content -->
        <div class="main-content" id="main-content">

            <!-- Registration Container -->
            <div class="registration-container">
                <form id="registration-form" method="post">
                    <h2>Register Your Details</h2>
                    <div class="form-group">
                        <label for="companyname">Company Name:</label>
                        <input type="text" id="companyname" name="companyname" required>
                    </div>

                    <div class="form-group">
                        <label for="companyssmform">Company SSM Form (PDF):</label>
                        <input type="file" id="companyssmform" name="companyssmform" accept=".pdf" required>
                    </div>

                    <div class="form-group">
                        <label for="companyregistration">Company Reg Number:</label>
                        <input type="text" id="companyregistration" name="companyregistration" required>
                    </div>

                    <div class="form-group">
                        <label for="companyid">Company ID:</label>
                        <input type="text" id="companyid" name="companyid" required>
                        <button type="button" onclick="generateId('company')">Generate ID</button>
                    </div>

                    <div class="form-group">
                        <label for="industrytype">Industry Type:</label>
                        <select name="industrytype" id="industrytype" onchange="toggleOtherInput()">
                            <option value="">Select Industry</option>
                            <option value="agriculture">Agriculture and Farming</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="technology">Technology and Software</option>
                            <option value="healthcare">Healthcare and Medicine</option>
                            <option value="finance">Finance and Banking</option>
                            <option value="retail">Retail and Consumer Goods</option>
                            <option value="transportation">Transportation and Logistics</option>
                            <option value="energy">Energy and Utilities</option>
                            <option value="construction">Construction and Real Estate</option>
                            <option value="education">Education and Training</option>
                            <option value="media">Media and Entertainment</option>
                            <option value="hospitality">Hospitality and Tourism</option>
                            <option value="telecommunications">Telecommunications</option>
                            <option value="professional_services">Professional Services</option>
                            <option value="government">Government and Public Sector</option>
                            <option value="nonprofit">Nonprofit and NGO</option>
                            <option value="aerospace">Aerospace and Defense</option>
                            <option value="mining">Mining and Natural Resources</option>
                            <option value="chemicals">Chemicals</option>
                            <option value="automotive">Automotive</option>
                            <option value="other">Other</option>
                        </select>

                        <div id="other-industry-container" style="display: none;">
                            <label for="other-industry">Please specify:</label>
                            <input type="text" id="other-industry" name="other-industry" />
                        </div>

                        <script>
                        function toggleOtherInput() {
                            var select = document.getElementById('industrytype');
                            var otherContainer = document.getElementById('other-industry-container');
                            
                            if (select.value === 'other') {
                                otherContainer.style.display = 'block';
                            } else {
                                otherContainer.style.display = 'none';
                            }
                        }
                        </script>

                    </div>

                    <div class="form-group">
                        <label for="companyemail">Company Email:</label>
                        <input type="companyemail" id="companyemail" name="companyemail" required>
                    </div>

                    <div class="form-group">
                        <label for="companyphone">Company Phone:</label>
                        <input type="tel" id="companyphone" name="companyphone" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonname">Contact Person Name:</label>
                        <input type="contactpersonname" id="contactpersonname" name="contactpersonname" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonmobile">Contact Person Mobile:</label>
                        <input type="contactpersonmobile" id="contactpersonmobile" name="contactpersonmobile" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonemail">Contact Person Email:</label>
                        <input type="contactpersonemail" id="contactpersonemail" name="contactpersonemail" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonname2">Contact Person Name 2:</label>
                        <input type="contactpersonname2" id="contactpersonname2" name="contactpersonname2" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonmobile2">Contact Person Mobile 2:</label>
                        <input type="contactpersonmobile2" id="contactpersonmobile2" name="contactpersonmobile2" required>
                    </div>

                    <div class="form-group">
                        <label for="contactpersonemail2">Contact Person Email 2:</label>
                        <input type="contactpersonemail2" id="contactpersonemail2" name="contactpersonemail2" required>
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
                            <label for="companymembership_id">Membership ID:</label>
                            <input type="text" id="companymembership_id" name="companymembership_id">
                            <button type="button" onclick="generateId('companymembership')">Generate ID</button>
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

                    <!-- Bank Type and Account Number -->
                    <div class="form-group">
                        <label for="banktype">Select Bank:</label>
                        <select id="banktype" name="banktype" required>
                            <option value="">Select Bank</option>
                            <option value="maybank">Maybank</option>
                            <option value="cimb">CIMB Bank</option>
                            <option value="public_bank">Public Bank</option>
                            <option value="hong_leong">Hong Leong Bank</option>
                            <option value="rhb">RHB Bank</option>
                            <option value="ambank">AmBank</option>
                            <option value="bank_islam">Bank Islam</option>
                            <option value="affin_bank">Affin Bank</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="accountnumber">Bank Account Number:</label>
                        <input type="text" id="accountnumber" name="accountnumber" required pattern="\d{10,16}" title="Please enter a valid bank account number (10 to 16 digits)">
                    </div>

                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>

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
                    if (type === 'company') {
                        document.getElementById('companyid').value = generatedId;
                    } else if (type === 'companymembership') {
                        document.getElementById('companymembership_id').value = generatedId;
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
