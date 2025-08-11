<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employeeid'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Database connection (update with your actual connection details)
$host = 'localhost';
$dbname = 'pos';
$username = 'root';
$password = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Fetch employee details from employeedetails table
$employeeid = $_SESSION['employeeid'];
$query = "SELECT * FROM employeedetails WHERE employeeid = :employeeid";
$stmt = $conn->prepare($query);
$stmt->execute(['employeeid' => $employeeid]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if employee record is found
if (!$employee) {
    echo "<p style='color: red;'>Error: No employee found with employeeid = $employeeid</p>";
    exit(); // Stop script execution if no employee found
}

// Update profile details if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactnumber = $_POST['contactnumber'];
    $address = $_POST['address'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $employee['password']; // Keep old password if not updated
    $username = $_POST['username']; // Update username

    // Update query
    $updateQuery = "UPDATE employeedetails SET contactnumber = :contactnumber, address = :address, password = :password, username = :username WHERE employeeid = :employeeid";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([
        'contactnumber' => $contactnumber,
        'address' => $address,
        'password' => $password,
        'username' => $username,
        'employeeid' => $employeeid
    ]);

    // Reload updated data
    header("Location: profile_account.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile and Account Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            width: 80%;
            margin: 50px auto;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black for dark theme */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .tabs {
            flex: 1;
            border-right: 1px solid #444; /* Darker border for dark theme */
            padding: 20px;
        }
        .tabs h3 {
            margin-bottom: 20px;
            color: #fff; /* White for better contrast */
        }
        .tabs label {
            display: block;
            margin: 10px 0;
            padding: 10px;
            background-color: #555; /* Darker background for tabs */
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .tabs label:hover {
            background-color: #666; /* Lighter gray on hover */
        }
        .content {
            flex: 3;
            padding: 20px;
        }
        .profile-info, .account-info {
            display: none;
        }
        .active {
            display: block;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #444; /* Darker border */
            border-radius: 4px;
            background-color: #333; /* Darker background for input */
            color: #fff; /* White text */
        }
        button {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .message {
            text-align: center;
            color: #5cb85c; /* Lighter green for success messages */
        }
    </style>
    <script>
        function showTab(tabName) {
            const profileInfo = document.getElementById("profile-info");
            const accountInfo = document.getElementById("account-info");
            if (tabName === 'profile') {
                profileInfo.classList.add("active");
                accountInfo.classList.remove("active");
            } else {
                profileInfo.classList.remove("active");
                accountInfo.classList.add("active");
            }
        }
        window.onload = function() {
            showTab('profile'); // Default to showing profile tab
        }
    </script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <div class="container">
        <div class="tabs">
            <h3>Navigation</h3>
            <label onclick="showTab('profile')">Profile</label>
            <label onclick="showTab('account')">Account Settings</label>
        </div>
        <div class="content">
            <?php if (isset($_GET['success'])): ?>
                <p class="message">Profile updated successfully!</p>
            <?php endif; ?>

        <div id="profile-info" class="profile-info active" style="padding: 20px; background-color: #222; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);">
            <h2 style="text-align: center; margin-bottom: 20px;">User Profile</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Full Name:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['fullname']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">IC Number:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['icnumber']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Date of Birth:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['dateofbirth']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Gender:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['gender']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Employment Date:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['employmentdate']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Username:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['username']); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #444; border-radius: 4px; padding: 10px; background-color: #333;">
                    <label style="font-weight: bold; flex: 1;">Role Name:</label>
                    <p style="flex: 2;"><?php echo htmlspecialchars($employee['role_name']); ?></p>
                </div>
            </div>
        </div>

        <div id="account-info" class="account-info" style="padding: 20px; background-color: #222; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);">
            <h2 style="text-align: center; margin-bottom: 20px;">Account Settings</h2>
            <form method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($employee['username']); ?>" required>

                <label for="contactnumber">Contact Number:</label>
                <input type="text" name="contactnumber" value="<?php echo htmlspecialchars($employee['contactnumber']); ?>" required>

                <label for="address">Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($employee['address']); ?>" required>

                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">

                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
