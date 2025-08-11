<?php
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

// Database connection settings
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeid = $_POST['employeeid'] ?? '';
    $password = $_POST['password'] ?? '';

    // SQL to check if user exists
    $query = "SELECT role_name FROM usersystem WHERE (employeeid = ? OR username = ?) AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $employeeid, $employeeid, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the role of the user
        $user = $result->fetch_assoc();
        $_SESSION['employeeid'] = $employeeid;
        $_SESSION['role_name'] = $user['role_name']; // Store role in session

        // Insert login time into login_history table

        $login_time = date("Y-m-d H:i:s");
        $history_query = "INSERT INTO login_history (employeeid, login_time) VALUES (?, ?)";
        $history_stmt = $conn->prepare($history_query);
        $history_stmt->bind_param("ss", $employeeid, $login_time);
        $history_stmt->execute();
        $history_stmt->close();

        header("Location: mainpage.php");

    } else {
        // Invalid login
        header("Location: unauthorized.php");  // Redirect back with error
        exit();
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Body styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url('darkwallpaper.jpg'); /* Set the background here */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            color: #ccc;
            overflow: hidden; /* Prevent scrolling */
            height: 100vh; /* Ensure body covers full height */
        }

        /* Welcome screen animation */
        .welcome-screen {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-image: url('darkwallpaper.jpg'); /* Keep the same background */
            background-size: cover;
            background-position: center;
            background-color: rgba(0, 0, 0, 0.8); /* Dark overlay for readability */
            color: white;
            position: fixed; /* Cover entire screen */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100; /* On top of everything */
            opacity: 1;
            animation: fadeOut 3s forwards;
            animation-delay: 3s; /* Display for 3 seconds, then fade out */
            backdrop-filter: blur(5px); /* Add blur effect */
        }



        .welcome-screen h1, .welcome-screen h2 {
            margin: 0;
            opacity: 0;
            animation: fadeIn 2s forwards;
        }

        .welcome-screen h1 {
            animation-delay: 0.5s;
        }

        .welcome-screen h2 {
            animation-delay: 1s;
        }

        /* Fade in and fade out animations */
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Login form container */
        .login-container {
            width: 350px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.1); /* Transparent background */
            backdrop-filter: blur(10px); /* Blurred background effect */
            margin: 0 auto;
            margin-top: 50px;
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.8); /* Dark shadow */
            display: none; /* Initially hidden */
            z-index: 1; /* Ensure it's below welcome screen initially */
            position: relative;
        }

        h2 {
            text-align: center;
            color: #fff;
            font-weight: 300;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
        }

        input::placeholder {
            color: #bbb;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        p {
            text-align: center;
            color: #ccc;
        }

        a {
            color: #28a745;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>

</head>
<body>
    <div class="welcome-screen">
        <h1>Welcome to the System</h1>
        <h2>Loading...</h2>
    </div>

    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <label for="employeeid">Employee ID</label>
            <input type="text" id="employeeid" name="employeeid" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        // Show login form after welcome screen fades out
        setTimeout(function() {
            document.querySelector('.login-container').style.display = 'block';
        }, 6000); // 3 seconds for welcome + 3 seconds fade out
    </script>
</body>
</html>
