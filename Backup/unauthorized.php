<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            color: #ff4d4d;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #333;
        }

        .home-btn {
            text-decoration: none;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .home-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>403 - Unauthorized</h1>
            <p>You do not have permission to access this page.</p>
            <a href="login.php" class="home-btn">Go to Home</a>
        </div>
    </div>
</body>
</html>
