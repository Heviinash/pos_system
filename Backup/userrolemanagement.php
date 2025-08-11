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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize an empty message variable
$message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_role'])) {
        $role_name = $_POST['role_name'];
        $description = $_POST['description'];

        // Check if role already exists
        $check_query = "SELECT * FROM roles WHERE role_name = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $role_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Role already exists
            $message = "Role already registered.";
        } else {
            // Insert new role
            $sql = "INSERT INTO roles (role_name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $role_name, $description);
            if ($stmt->execute()) {
                $message = "Role added successfully.";
            } else {
                $message = "Error adding role.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }

    if (isset($_POST['delete_role'])) {
        $role_name = $_POST['role_name'];
        $sql = "DELETE FROM roles WHERE role_name = '$role_name'";
        $conn->query($sql);
        $sql = "DELETE FROM roles_permission WHERE role_name = '$role_name'";
        $conn->query($sql); // Clean up related permissions
    }
    if (isset($_POST['add_permission'])) {
        $permission_name = $_POST['permission_name'];
        $description = $_POST['description'];
        $sql = "INSERT INTO permissions (permission_name, description) VALUES ('$permission_name', '$description')";
        $conn->query($sql);
    }
    if (isset($_POST['delete_permission'])) {
        $permission_name = $_POST['permission_name'];
        $sql = "DELETE FROM permissions WHERE permission_name = '$permission_name'";
        $conn->query($sql);
        $sql = "DELETE FROM roles_permission WHERE permission_name = '$permission_name'";
        $conn->query($sql); // Clean up related roles
    }
    if (isset($_POST['assign_permission'])) {
        $role_name = $_POST['role_name'];
        $permission_name = $_POST['permission_name'];
        $sql = "INSERT INTO roles_permission (role_name, permission_name) VALUES ('$role_name', '$permission_name')";
        $conn->query($sql);
    }
    if (isset($_POST['delete_role_permission'])) {
        $role_name = $_POST['role_name'];
        $permission_name = $_POST['permission_name'];
        $sql = "DELETE FROM roles_permission WHERE role_name = '$role_name' AND permission_name = '$permission_name'";
        $conn->query($sql);
    }
}

// Fetch roles, permissions, and role-to-permission assignments
$roles = $conn->query("SELECT * FROM roles");
$permissions = $conn->query("SELECT * FROM permissions");
$roles_permission = $conn->query("SELECT * FROM roles_permission");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
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

        .header {
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

        .header .logo img {
            height: 30px;
        }

        .header .company-name {
            flex-grow: 1;
            text-align: center;
            font-size: 16px;
        }

        .header .employee-account {
            text-align: right;
            font-size: 12px;
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
            z-index: 999;
        }

        .side-nav.open {
            transform: translateX(0);
        }

        .side-nav a {
            display: block;
            padding: 15px;
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            border-radius: 5px;
            margin: 5px 0;
            transition: background-color 0.3s;
        }

        .side-nav a:hover {
            background-color: #444;
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
            margin-left: 250px;
            margin-top: 50px;
            padding: 20px;
            transition: margin-left 0.3s;
            width: calc(100% - 250px);
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            position: relative;
            overflow: hidden;
            margin-left: 2%; /* Adjust this value to move the container to the left */
        }


        form {
            background: #333;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            width: 100%; /* Ensure the form takes the full width of its container */
            max-width: 600px; /* Set a max width for the form */
        }

        input, select, button {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #444;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
            background-color: #555;
            color: #fff;
        }

        button {
            background: #777;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #888;
        }

        .card {
            background: #2c2c2c;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            max-width: 1000px; /* Set a max width for the card */
            box-sizing: border-box;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
        }

        .card table th, .card table td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left; /* Align text to the left in table cells */
        }

       .card .delete-btn {
            background: red;
            color: #fff;
            border: none;
            cursor: pointer;
            padding: 6px 12px; /* Smaller padding for a smaller button */
            font-size: 15px; /* Smaller font size for a smaller button */
            border-radius: 3px; /* Adjust border-radius for a more compact shape */
            width: auto; /* Width adjusts to content */
        }

        .card .delete-btn:hover {
            background: darkred;
        }

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="navbar.css">
<br>
<br>

<div class="main-content" id="main-content">
    <div class="container">
        <!-- Display message if set -->
        <?php if ($message): ?>
            <p style="color: red;"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Add Role Form -->
        <form method="post">
            <h2>Add Role</h2>
            <input type="text" name="role_name" placeholder="Role Name" required>
            <input type="text" name="description" placeholder="Description">
            <button type="submit" name="add_role">Add Role</button>
        </form>
  
    </div>
</div>

<script>
    document.getElementById('hover-trigger').addEventListener('mouseover', function() {
        document.getElementById('side-nav').classList.add('open');
    });

    document.getElementById('side-nav').addEventListener('mouseleave', function() {
        document.getElementById('side-nav').classList.remove('open');
    });
</script>
</body>
</html>
