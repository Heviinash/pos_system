<?php


session_start();

// Database connection
$servername = "localhost"; // Your server name
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "pos"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $promotion_type = $_POST['promotion_type'];
    $custom_promotion_type = $_POST['custom_promotion_type'];
    $promotion_name = $_POST['promotion_name'];
    $description = $_POST['description'];
    $promo_code = $_POST['promo_code'];
    $discount_value = $_POST['discount_value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Use custom promotion type if provided, otherwise use selected promotion type
    $final_promotion_type = !empty($custom_promotion_type) ? $custom_promotion_type : $promotion_type;

    // Convert discount value to float and assign to a variable
    $discount_value_float = (float)$discount_value;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO promotions (promotion_type, promotion_name, description, promo_code, discount_value, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdss", $final_promotion_type, $promotion_name, $description, $promo_code, $discount_value_float, $start_date, $end_date);

    // Execute the statement
    if ($stmt->execute()) {
        $success_message = "Promotion saved successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Fetch current promotions
$current_promotions = [];
$result = $conn->query("SELECT * FROM promotions WHERE CURDATE() BETWEEN start_date AND end_date");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $current_promotions[] = $row;
    }
}

// Fetch upcoming promotions
$upcoming_promotions = [];
$result = $conn->query("SELECT * FROM promotions WHERE start_date > CURDATE()");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcoming_promotions[] = $row;
    }
}

// Fetch past promotions
$past_promotions = [];
$result = $conn->query("SELECT * FROM promotions WHERE end_date < CURDATE()");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $past_promotions[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotional Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
            color: white; /* Change text color to white for better visibility */
        }
        #promo-settings-form {
            background: rgba(255, 255, 255, 0.2); /* Transparent container */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.2); /* Transparent input fields */
            color: white; /* Change input text color to white */
        }
        button {
            margin-top: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            margin: 10px 0;
            color: lightgreen; /* Change message color */
        }
        .error {
            color: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: darkcyan;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            color: white; /* Change table text color to white */
        }
        th {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

    <h2 style="text-align: center;">Promotional Setup</h2>

    <form id="promo-settings-form" method="POST" action="">
        <label for="promotion-type">Promotion Type:</label>
        <select id="promotion-type" name="promotion_type" onchange="toggleCustomType()" required style="background-color:dimgrey; color: white; border: 1px solid #ccc;">
            <option value="voucher">Voucher</option>
            <option value="seasonal">Seasonal Promotion</option>
            <option value="price_discount">Price Discount</option>
            <option value="custom">Custom Promotion Type</option>
        </select>

        <div id="custom-type-container" style="display:none;">
            <label for="custom-promotion-type">Custom Promotion Type:</label>
            <input type="text" id="custom-promotion-type" name="custom_promotion_type" placeholder="Enter custom promotion type">
        </div>
        
        <label for="promotion-name">Promotion Name:</label>
        <input type="text" id="promotion-name" name="promotion_name" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description"></textarea>

        <label for="promo-code">Promo Code (Optional):</label>
        <input type="text" id="promo-code" name="promo_code">

        <label for="discount-value">Discount Value:</label>
        <input type="number" id="discount-value" name="discount_value" required step="0.01">

        <label for="start-date">Start Date:</label>
        <input type="date" id="start-date" name="start_date" required>

        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" name="end_date" required>

        <button type="submit">Save Promotion</button>
        <button type="button" onclick="cancelPromo()">Cancel</button>
    </form>

    <script>
    function cancelPromo() {
        document.getElementById("promo-settings-form").reset();
    }

    function toggleCustomType() {
        var selectBox = document.getElementById("promotion-type");
        var customTypeContainer = document.getElementById("custom-type-container");
        if (selectBox.value === "custom") {
            customTypeContainer.style.display = "block";
        } else {
            customTypeContainer.style.display = "none";
        }
    }
    </script>

    <?php if (isset($success_message)): ?>
        <div class="message"><?= $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="error"><?= $error_message; ?></div>
    <?php endif; ?>

    <h2>Current Promotions</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Description</th>
                <th>Promo Code</th>
                <th>Discount Value</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($current_promotions)): ?>
                <?php foreach ($current_promotions as $promo): ?>
                    <tr>
                        <td><?= $promo['promotion_type']; ?></td>
                        <td><?= $promo['promotion_name']; ?></td>
                        <td><?= $promo['description']; ?></td>
                        <td><?= $promo['promo_code']; ?></td>
                        <td><?= $promo['discount_value']; ?></td>
                        <td><?= $promo['start_date']; ?></td>
                        <td><?= $promo['end_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No current promotions available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>

    <h2>Upcoming Promotions</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Description</th>
                <th>Promo Code</th>
                <th>Discount Value</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($upcoming_promotions)): ?>
                <?php foreach ($upcoming_promotions as $promo): ?>
                    <tr>
                        <td><?= $promo['promotion_type']; ?></td>
                        <td><?= $promo['promotion_name']; ?></td>
                        <td><?= $promo['description']; ?></td>
                        <td><?= $promo['promo_code']; ?></td>
                        <td><?= $promo['discount_value']; ?></td>
                        <td><?= $promo['start_date']; ?></td>
                        <td><?= $promo['end_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No upcoming promotions available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <h2>Past Promotions</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Description</th>
                <th>Promo Code</th>
                <th>Discount Value</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($past_promotions)): ?>
                <?php foreach ($past_promotions as $promo): ?>
                    <tr>
                        <td><?= $promo['promotion_type']; ?></td>
                        <td><?= $promo['promotion_name']; ?></td>
                        <td><?= $promo['description']; ?></td>
                        <td><?= $promo['promo_code']; ?></td>
                        <td><?= $promo['discount_value']; ?></td>
                        <td><?= $promo['start_date']; ?></td>
                        <td><?= $promo['end_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No past promotions available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
