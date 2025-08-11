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

    // Database connection settings
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "POS";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize user input
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $icnumber = $conn->real_escape_string($_POST['icnumber']);
        $dateofbirth = $conn->real_escape_string($_POST['dateofbirth']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $contactnumber = $conn->real_escape_string($_POST['contactnumber']);
        $address = $conn->real_escape_string($_POST['address']);
        $employmentdate = $conn->real_escape_string($_POST['employmentdate']);
        $employeeid = $conn->real_escape_string($_POST['employeeid']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $conn->real_escape_string($_POST['password']);
        $confirmpassword = $conn->real_escape_string($_POST['confirmpassword']);
        $role_name = $conn->real_escape_string($_POST['role_name']);

        // Check if passwords match
        if ($password !== $confirmpassword) {
            echo "Passwords do not match.";
            exit;
        }

           // Check if icnumber already exists in the employeedetails table
        $check_ic_query = "SELECT * FROM employeedetails WHERE icnumber = ?";
        $stmt_check = $conn->prepare($check_ic_query);
        $stmt_check->bind_param("s", $icnumber);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

         if ($result_check->num_rows > 0) {
            // IC number already exists
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: IC number already exists in the system.'];
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to the form page
            exit();
        }
        $stmt_check->close();
        

        // Prepare and bind the SQL statement for the usersystem table
        $stmt = $conn->prepare("INSERT INTO employeedetails (fullname, icnumber, dateofbirth, gender, contactnumber, address, employmentdate, employeeid, username, password, role_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $fullname, $icnumber, $dateofbirth, $gender, $contactnumber, $address, $employmentdate, $employeeid, $username, $password, $role_name);

        // Execute the statement for the usersystem table
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'New user registered successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: ' . $stmt->error];
        }

        // Close the statement for the usersystem table
        $stmt->close();

        // Prepare and bind the SQL statement for the employee_credentials table
        $stmt2 = $conn->prepare("INSERT INTO usersystem (employeeid, username, password, confirmpassword, role_name) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssss", $employeeid, $username, $password, $confirmpassword, $role_name);

        if ($stmt2->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Employee credentials inserted successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: ' . $stmt2->error];
        }

        // Close the statement and connection
        $stmt2->close();
        $conn->close();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: margin-left 0.3s;
            position: relative;
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
            position: relative;
        }

        .main-content.active {
            margin-left: 250px;
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

        .side-nav a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .side-nav a:hover {
            background-color: #444;
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


        .password-criteria {
            font-size: 14px;
            color: #f8d7da;
        }

        .password-criteria ul {
            list-style-type: none;
            padding: 0;
        }

        .password-criteria li {
            margin: 5px 0;
        }

        .password-criteria .valid {
            color: #28a745;
        }

        .password-criteria .invalid {
            color: #dc3545;
        }

        /* Message box CSS */

        .message {
            padding: 10px;
            margin: 80px auto 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: normal;
            text-align: center;
            max-width: 300px; /* Set a maximum width for the message box */
            width: 100%; /* Ensure the box takes up full width up to the max-width */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
            overflow-wrap: break-word; /* Wrap long words */
        }

        .message.success {
            background-color: #28a745; /* Green background for success */
            color: white;
            border: 2px solid #218838; /* Darker green border */
        }

        .message.error {
            background-color: #dc3545; /* Red background for error */
            color: white;
            border: 2px solid #c82333; /* Darker red border */
        }
        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input[type="password"] {
            width: calc(100% - 40px); /* Adjust width to accommodate the button */
            padding-right: 40px; /* Add padding to the right */
        }

        .password-container button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #e0e0e0; /* Adjust color to fit your theme */
            font-size: 18px;
        }

    </style>
</head>
<body>
  
    <?php
    //session_start();
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo '<div class="message ' . $message['type'] . '">' . $message['text'] . '</div>';
        // Clear the message after displaying it
        unset($_SESSION['message']);
    }
    ?>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    
    <div class="container">

            <!-- Registration Container -->
            <div class="registration-container">
                <h1>User Registration</h1>
                <form id="registration-form" method="post">
                    <div class="form-group">
                        <label for="fullname">Full Name:</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="icnumber">IC No:</label>
                        <input type="text" id="icnumber" name="icnumber" required>
                    </div>
                    <div class="form-group">
                        <label for="dateofbirth">Date of Birth:</label>
                        <input type="date" id="dateofbirth" name="dateofbirth" required>
                    </div>
                    <div class="form-group">
                        <label>Gender:</label>
                        <input type="radio" id="male" name="gender" value="Male" required>
                        <label for="male">Male</label>

                        <input type="radio" id="female" name="gender" value="Female" required>
                        <label for="female">Female</label>
                    </div>
                    <div class="form-group">
                        <label for="contactnumber">Contact:</label>
                        <input type="text" id="contactnumber" name="contactnumber" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" required>
                    </div>

                    <div class="form-group">
                        <label for="employmentdate">Employment Date:</label>
                        <input type="date" id="employmentdate" name="employmentdate" required>
                    </div>

                    <h2>System User Registration</h2>

                    <div class="form-group">
                        <label for="employeeid">Employee ID:</label>
                        <input type="text" id="employeeid" name="employeeid" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="togglePassword" aria-label="Toggle password visibility">
                            <span id="eyeIcon" class="eye-icon">👁️</span>
                        </button>
                        <small id="password-criteria" class="password-criteria">
                            Password must be at least 8 characters long and include:
                            <ul>
                                <li id="min-length" class="invalid">At least 8 characters</li>
                                <li id="uppercase" class="invalid">An uppercase letter</li>
                                <li id="lowercase" class="invalid">A lowercase letter</li>
                                <li id="number" class="invalid">A number</li>
                                <li id="special" class="invalid">A special character (e.g., @, #, $, &)</li>
                            </ul>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword">Confirm Password:</label>
                        <input type="password" id="confirmpassword" name="confirmpassword" required>
                        <button type="button" id="toggleConfirmPassword" aria-label="Toggle password visibility">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="role_name">Role:</label>
                        <select id="role_name" name="role_name" required>
                            <option value="User">User</option>
                            <option value="Guest">Guest</option>
                            <!-- Add more roles as needed -->
                        </select>
                    </div>
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for navigation bar -->
    <script>

    // Function to toggle visibility for any password input
    function togglePasswordVisibility(inputId, buttonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(buttonId);
        const eyeIcon = toggleButton.querySelector('.eye-icon');

        toggleButton.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.textContent = type === 'password' ? '👁️' : ' 🕶️'; // Change icon based on visibility
        });
    }

    // Initialize toggle for each password input
    togglePasswordVisibility('password', 'togglePassword');
    togglePasswordVisibility('confirmpassword', 'toggleConfirmPassword');




        document.getElementById('hover-trigger').addEventListener('mouseover', function() {
            document.getElementById('side-nav').classList.add('open');
            document.getElementById('main-content').classList.add('active');
        });

        document.getElementById('side-nav').addEventListener('mouseleave', function() {
            document.getElementById('side-nav').classList.remove('open');
            document.getElementById('main-content').classList.remove('active');
        });
    
        // Javascript for the Password settings

        document.addEventListener('DOMContentLoaded', function () {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmpassword');
            const minLength = document.getElementById('min-length');
            const uppercase = document.getElementById('uppercase');
            const lowercase = document.getElementById('lowercase');
            const number = document.getElementById('number');
            const special = document.getElementById('special');

            function validatePassword() {
                const value = password.value;

                // Check length
                minLength.classList.toggle('valid', value.length >= 8);
                minLength.classList.toggle('invalid', value.length < 8);

                // Check uppercase
                uppercase.classList.toggle('valid', /[A-Z]/.test(value));
                uppercase.classList.toggle('invalid', !/[A-Z]/.test(value));

                // Check lowercase
                lowercase.classList.toggle('valid', /[a-z]/.test(value));
                lowercase.classList.toggle('invalid', !/[a-z]/.test(value));

                // Check number
                number.classList.toggle('valid', /\d/.test(value));
                number.classList.toggle('invalid', !/\d/.test(value));

                // Check special character
                special.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(value));
                special.classList.toggle('invalid', !/[!@#$%^&*(),.?":{}|<>]/.test(value));

                // Check if passwords match
                const isPasswordValid = minLength.classList.contains('valid') &&
                    uppercase.classList.contains('valid') &&
                    lowercase.classList.contains('valid') &&
                    number.classList.contains('valid') &&
                    special.classList.contains('valid');

                confirmPassword.disabled = !isPasswordValid;
            }

            password.addEventListener('input', validatePassword);
        });
            document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmpassword');
        const passwordMatchMessage = document.getElementById('password-match-message');

        function validatePasswordMatch() {
            if (password.value !== confirmPassword.value) {
                passwordMatchMessage.textContent = 'Passwords do not match.';
                passwordMatchMessage.style.color = '#dc3545'; // Red color for error
                confirmPassword.classList.add('invalid');
            } else {
                passwordMatchMessage.textContent = 'Passwords match.';
                passwordMatchMessage.style.color = '#28a745'; // Green color for success
                confirmPassword.classList.remove('invalid');
            }
        }

        password.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
    })
    </script>
</body>
</html>
