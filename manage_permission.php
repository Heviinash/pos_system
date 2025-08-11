<?php

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

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $permissions = $_POST['permissions'] ?? [];

    // Clear existing permissions for the selected role
    $deleteQuery = "DELETE FROM roles_permission WHERE role_name = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("s", $role);
    $stmt->execute();

    // Insert new permissions for the selected role
    foreach ($permissions as $permission) {
        $insertQuery = "INSERT INTO roles_permission (role_name, permission_name) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ss", $role, $permission);
        $stmt->execute();
    }

    header("Location: mainpage.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Permissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #e0e0e0; /* Light text for better readability */
        }
        .container {
            max-width: 800px;
            margin: 60px auto;
            background-color: rgba(30, 30, 30, 0.8); /* Darker background for better contrast */
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.8); /* Darker shadow for depth */
        }
        h2 {
            text-align: center;
            color: #f0f0f0; /* Lighter heading for contrast */
            font-weight: 700;
            margin-bottom: 20px;
        }
        label {
            font-weight: 500;
            color: #bbbbbb; /* Slightly lighter label color */
        }
        select, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #444; /* Darker border */
            border-radius: 6px;
            font-size: 16px;
            background-color: #1e1e1e; /* Darker input background */
            color: #e0e0e0; /* Light text */
        }
        .permission-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px; /* Space between permission items */
            margin-bottom: 20px;
        }
        .permission-card {
            background-color: rgba(50, 50, 50, 0.8); /* Card background */
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            accent-color: #1db954; /* Customize checkbox color */
        }
        button {
            background-color: #1db954; /* Bright accent color (green) for contrast */
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            padding: 14px; /* Increased padding for button */
        }
        button:hover {
            background-color: #1aa34a; /* Darken button on hover */
        }
        .success-message {
            color: #4caf50; /* Green for success message */
            text-align: center;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>

</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <div class="container">
        <h2>Assign Permissions to Role</h2>
        <form method="POST">
            <!-- Select Role -->
            <label for="role">Select Role:</label>
            <select id="role" name="role" required>
                <?php
                // Fetch all roles from the database
                $query = "SELECT role_name FROM roles";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['role_name']}'>{$row['role_name']}</option>";
                }
                ?>
            </select>

            <!-- Permissions List -->
            <div class="permission-list">
                <h3>Available Permissions</h3>
                <?php
                // Fetch all available permissions from the permissions table
                $query = "SELECT permission_name, description FROM permissions";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='permission-card'><label><input type='checkbox' name='permissions[]' value='{$row['permission_name']}'> {$row['description']}</label></div>";
                }
                ?>
            </div>

            <!-- Submit Button -->
            <button type="submit">Save Permissions</button>
        </form>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">Permissions saved successfully!</p>
        <?php endif; ?>
    </div>
</body>
</html>
