<?php

session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "pos"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch roles and permissions from the database
$roles_permissions = [];
$roles = [];
$permissions = [];
$users_by_role = [];

// Fetch roles
$roles_query = "SELECT role_name FROM roles_permission GROUP BY role_name";
$roles_result = $conn->query($roles_query);
while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row['role_name'];
}

// Fetch permissions and their descriptions
$permissions_query = "SELECT permission_name, description FROM permissions";
$permissions_result = $conn->query($permissions_query);
while ($row = $permissions_result->fetch_assoc()) {
    $permissions[$row['permission_name']] = $row['description'];
}

// Fetch the permissions for each role
foreach ($roles as $role) {
    $query = "SELECT permission_name FROM roles_permission WHERE role_name = '$role'";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $roles_permissions[$role][$row['permission_name']] = true; // Mark permission as granted
    }
}

// Fetch usernames for each role from the usersystem table
$users_query = "SELECT username, role_name FROM usersystem";
$users_result = $conn->query($users_query);
while ($row = $users_result->fetch_assoc()) {
    $users_by_role[$row['role_name']][] = $row['username'];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Permissions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 20px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        th, td {
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: rgba(76, 175, 80, 0.7);
            color: white;
        }

        .description {
            width: 40%;
            max-width: 200px;
            text-align: left;
            word-wrap: break-word;
        }

        .access {
            width: 20%;
            text-align: center;
        }

        .usernames {
            font-size: 0.9em;
            color: lightgray;
            margin-top: 5px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h1>Role Permissions</h1>

<div class="button-container">
    <a href="mainpage.php" class="btn">Go to Main Page</a>
</div>

<table>
    <thead>
        <tr>
            <th class="description">Descriptions</th>
            <?php foreach ($roles as $role): ?>
                <th class="access"><?php echo htmlspecialchars($role); ?>
                    <div class="usernames">
                        <?php
                        // Display usernames associated with this role
                        if (isset($users_by_role[$role])) {
                            echo implode(', ', array_map('htmlspecialchars', $users_by_role[$role]));
                        }
                        ?>
                    </div>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($permissions as $permission_name => $description): ?>
            <tr>
                <td class="description"><?php echo htmlspecialchars($description); ?></td>
                <?php foreach ($roles as $role): ?>
                    <td class="access">
                        <?php
                        if (isset($roles_permissions[$role][$permission_name])) {
                            echo "✔️"; // Tick for access
                        } else {
                            echo "❌"; // Cross for no access
                        }
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
