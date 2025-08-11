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







// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "pos");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch roles for dropdown
$roles_query = "SELECT role_name FROM roles";
$roles_result = mysqli_query($conn, $roles_query);

if (!$roles_result) {
    die("Error fetching roles: " . mysqli_error($conn));
}

// Reset the result pointer for roles after fetching it for use in multiple sections
function reset_roles_result($conn) {
    global $roles_query;
    return mysqli_query($conn, $roles_query);
}

// Handle role updates or downgrades
if (isset($_POST['update_role'])) {
    $employeeid = $_POST['employeeid'];
    $new_role = $_POST['role'];

    // Update the employee's role in employeedetails
    $update_query = "UPDATE employeedetails SET role_name = '$new_role' WHERE employeeid = $employeeid";
    if (!mysqli_query($conn, $update_query)) {
        die("Error updating role in employeedetails: " . mysqli_error($conn));
    }

    // Also update the role in usersystem
    $update_usersystem_query = "UPDATE usersystem SET role_name = '$new_role' WHERE employeeid = $employeeid";
    if (!mysqli_query($conn, $update_usersystem_query)) {
        die("Error updating role in usersystem: " . mysqli_error($conn));
    }

    // Redirect to avoid form resubmission
    header("Location: manageuserrolemanagement.php");
    exit();
}

// Fetch users (non-guest roles)
$users_query = "SELECT * FROM employeedetails WHERE role_name != 'guest'";
$users_result = mysqli_query($conn, $users_query);

if (!$users_result) {
    die("Error fetching users: " . mysqli_error($conn));
}

// Fetch guests (role = 'guest')
$guests_query = "SELECT * FROM employeedetails WHERE role_name = 'guest'";
$guests_result = mysqli_query($conn, $guests_query);

if (!$guests_result) {
    die("Error fetching guests: " . mysqli_error($conn));
}

// Fetch pending invites (role is NULL)
$invites_query = "SELECT * FROM employeedetails WHERE role_name IS NULL";
$invites_result = mysqli_query($conn, $invites_query);

if (!$invites_result) {
    die("Error fetching invites: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212; /* Dark background */
            background-image: url('darkwallpaper.jpg'); /* Add the path to your wallpaper */
            background-size: cover;
            background-position: center;
            color: #f0f0f0; /* Light text color */
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #f1c40f; /* Bright accent color for headers */
        }

        .dashboard {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 0 auto;
            max-width: 1600px;
            flex-direction: column;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.6); /* Dark theme with transparent effect */ /* Transparent background for dark theme */
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.5);
            border-left: 5px solid #e67e22; /* Bright accent color for container border */
            max-height: 500px;
            overflow-y: auto;
        }

        .container h3 {
            text-align: center;
            color: #e67e22; /* Accent color */
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .item-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #444; /* Darker border for separation */
            padding-bottom: 10px;
        }

        .item-container p {
            flex: 1;
            font-size: 1.1rem;
            color: #f0f0f0; /* Light text color */
            margin: 0;
        }

        .form-container {
            display: flex;
            align-items: center;
            gap: 10px; /* Space between select and button */
        }

        select, button {
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #444; /* Darker border for input fields */
            background-color: #2c3e50; /* Dark background for inputs */
            color: #f0f0f0; /* Light text color */
            box-sizing: border-box; /* Ensures padding and border are included in element's total width and height */
        }

        select {
            flex: 1;
        }

        button {
            background-color: #e67e22; /* Bright accent color */
            color: white;
            border: none;
            cursor: pointer;
            flex-shrink: 0; /* Prevents the button from shrinking */
        }

        button:hover {
            background-color: #d35400; /* Darker accent color on hover */
        }
    </style>

</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <h1>User Role Management</h1>

    <div class="dashboard">
        <!-- First Container: Users with Selected Roles -->
        <div class="container" id="users_container">
            <h3>Users with Selected Roles</h3>
            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                <div class="item-container">
                    <p>
                        <?php echo htmlspecialchars($user['fullname']); ?> - Role: <?php echo htmlspecialchars($user['role_name']); ?>
                    </p>
                    <div class="form-container">
                        <form method="POST" action="">
                            <select name="role">
                                <?php
                                // Reset the result set so that we can use it multiple times
                                $roles_result = reset_roles_result($conn);
                                while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                    <option value="<?php echo htmlspecialchars($role['role_name']); ?>" <?php echo htmlspecialchars($role['role_name']) == htmlspecialchars($user['role_name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($user['employeeid']); ?>">
                            <button type="submit" name="update_role">Update Role</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Second Container: Registered Guests -->
        <div class="container" id="guests_container">
            <h3>Registered Guests</h3>
            <?php while ($guest = mysqli_fetch_assoc($guests_result)): ?>
                <div class="item-container">
                    <p><?php echo htmlspecialchars($guest['fullname']); ?></p>
                    <div class="form-container">
                        <form method="POST" action="">
                            <select name="role">
                                <?php
                                $roles_result = reset_roles_result($conn);
                                while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                    <option value="<?php echo htmlspecialchars($role['role_name']); ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($guest['employeeid']); ?>">
                            <button type="submit" name="update_role">Update Role</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

</body>
</html>
