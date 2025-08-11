<?php
// Database connection (adjust credentials as needed)
$conn = new mysqli("localhost", "root", "", "pos");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $userid = $_POST['userid'];
    $expenseType = $_POST['expense_type'];
    $amount = $_POST['amount'];
    $payment = $_POST['payment'];
    $date = $_POST['date'];
    $vendor = $_POST['vendor'];
    $customVendor = $_POST['custom_vendor'];
    $notes = $_POST['notes'];
    $files = $_FILES['attachments'];

    // Choose vendor
    $chosenVendor = ($vendor === 'custom') ? $customVendor : $vendor;

    // Handle file uploads
    $uploadedFiles = [];
    $uploadDir = "uploads/";
    foreach ($files['tmp_name'] as $key => $tmpName) {
        $fileName = $files['name'][$key];
        $filePath = $uploadDir . basename($fileName);
        if (move_uploaded_file($tmpName, $filePath)) {
            $uploadedFiles[] = $filePath;
        }
    }
    $filePaths = implode(",", $uploadedFiles);

    // Insert expense into the database
    $stmt = $conn->prepare("INSERT INTO expenses (username, userid, expense_type, amount, payment,  date, vendor, notes, attachments, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sssdsssss", $username, $userid, $expenseType, $amount, $payment, $date, $chosenVendor, $notes, $filePaths);

    if ($stmt->execute()) {
        echo "<p>Expense submitted successfully.</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Deduct from running balance Remove the comment if needed
    /*
    $stmt = $conn->prepare("UPDATE running_balance SET balance = balance - ? WHERE id = 1"); // Adjust balance id logic as needed
    $stmt->bind_param("d", $amount);
    $stmt->execute();
    */
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracking Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .custom-vendor {
            display: none;
        }
    </style>
</head>
<body>

<h1>Expense Tracking Form</h1>
<form method="POST" enctype="multipart/form-data">

    <label for="name">Name</label>
    <input id="username" name="username">

    <label for="userid">UserID</label>
    <input id="userid" name="userid">


    <label for="expense_type">Expense Type</label>
    <select id="expense_type" name="expense_type" required>
        <option value="">Select an expense type</option>
        <option value="Restock Expenses">Restock Expenses</option>
        <option value="Rent">Rent</option>
        <option value="Utilities">Utilities</option>
        <option value="Advertising">Advertising</option>
        <option value="Taxes">Taxes</option>
        <option value="Salaries">Salaries</option>
        <option value="Supplies">Supplies</option>
    </select>

    <label for="amount">Amount</label>
    <input type="number" id="amount" name="amount" step="0.01" required>

    <label for="payment">Payment:</label>
    <select id="payment" name="payment" required>
        <option value="cash">Cash</option>
        <option value="account payable">Account Payable</option>
    </select>

    <label for="date">Date</label>
    <input type="date" id="date" name="date" required>

    <label for="vendor">Vendor</label>
    <select id="vendor" name="vendor" required>
        <option value="">Select a vendor</option>
        <?php
        // Fetch vendors from the database
        $result = $conn->query("SELECT name FROM suppliers");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['name']}'>{$row['name']}</option>";
        }
        ?>
        <option value="custom">Custom</option>
    </select>
    <input type="text" id="custom_vendor" name="custom_vendor" class="custom-vendor" placeholder="Enter custom vendor">

    <label for="attachments">Attachments</label>
    <input type="file" id="attachments" name="attachments[]" multiple>

    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="4" placeholder="Add additional details..."></textarea>

    <button type="submit">Submit Expense</button>
</form>

<script>
    document.getElementById("vendor").addEventListener("change", function () {
        const customVendorField = document.querySelector(".custom-vendor");
        if (this.value === "custom") {
            customVendorField.style.display = "block";
            customVendorField.required = true;
        } else {
            customVendorField.style.display = "none";
            customVendorField.required = false;
        }
    });
</script>

</body>
</html>
