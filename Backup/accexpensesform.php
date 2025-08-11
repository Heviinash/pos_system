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
    $subExpenseType = $_POST['sub_expense_type'];  // Get sub-expense type
    $amount = $_POST['amount'];
    $payment = $_POST['payment'];
    $date = $_POST['date'];
    $vendor = $_POST['vendor'];
    $customVendor = $_POST['custom_vendor'];
    $notes = $_POST['notes'];
    $files = $_FILES['attachments'];

    // Choose vendor
    $chosenVendor = ($vendor === 'custom') ? $customVendor : $vendor;

    // Combine expense_type and sub_expense_type if "Account Payable" is selected
    if ($expenseType === "Account Payable" && !empty($subExpenseType)) {
        $expenseType = $expenseType . " - " . $subExpenseType;  // Concatenate both values
    }

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
    $stmt = $conn->prepare("INSERT INTO expenses (username, userid, expense_type, amount, payment, date, vendor, notes, attachments, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sssdsssss", $username, $userid, $expenseType, $amount, $payment, $date, $chosenVendor, $notes, $filePaths);

    if ($stmt->execute()) {
        echo "<p style='color: #28a745; font-size: 16px; font-weight: bold; padding: 10px; border: 1px solid #28a745; background-color: #d4edda; border-radius: 5px; text-align: center;'>Expense submitted successfully.</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Deduct from running balance (optional)
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
        margin: 0;
        background-image: url('darkwallpaper.jpg'); /* Add the path to your wallpaper */
        background-size: cover;
        background-position: center;
        color: #e0e0e0; /* Light text color */
    }
    
    form {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2); /* Light border for contrast */
        border-radius: 5px;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.6); /* Darker shadow for dark theme */
        background-color: rgba(0, 0, 0, 0.8); /* Dark translucent background */
    }
    
    label {
        display: block;
        margin: 10px 0 5px;
        color: #e0e0e0; /* Light text */
    }
    
    input, select, textarea, button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #555; /* Darker border */
        border-radius: 5px;
        background-color: #333; /* Dark input background */
        color: #e0e0e0; /* Light text for readability */
        transition: all 0.3s ease;
    }
    
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #4A90E2; /* Blue border on focus */
        background-color: #444; /* Slightly lighter background */
    }
    
    button {
        background-color: #28a745; /* Green button */
        color: #fff; /* White text */
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    button:hover {
        background-color: #218838; /* Darker green on hover */
    }
    
    .custom-vendor {
        display: none;
    }
    
    /* Sidebar Styling */
    #sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        z-index: 1;
        top: 0;
        left: -250px; /* Initially hidden */
        background-color: rgba(44, 62, 80, 0.9); /* Dark transparent background */
        transition: 0.3s;
        padding-top: 60px;
    }
    
    #sidebar a {
        padding: 15px 20px;
        text-decoration: none;
        font-size: 18px;
        color: #e0e0e0; /* Light text */
        display: block;
        transition: 0.3s;
    }
    
    #sidebar a:hover {
        background-color: rgba(87, 87, 87, 0.8); /* Lighter dark hover effect */
    }
    #sidebar .sidebar-header {
        font-size: 24px;
        color: #FFF;
        text-align: center;
        margin-bottom: 10px;
        border-bottom: 2px solid #4CAF50;
        padding-bottom: 10px;
    }
</style>

</head>
<body>


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<div id="sidebar">
        <div class="sidebar-header"><i class="fa fa-calculator"></i> <i class="fa fa-chart-line"></i>
          <i class="fa fa-money-bill-wave"></i></div>
        <a href="accdashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
        <a href="acccashflowstatement.php"><i class="fa fa-money-bill-wave"></i> Cash Flow Statement</a>
        <a href="accexpensesform.php"><i class="fa fa-edit"></i> Expenses Form</a>
        <a href="accexpensesapproval.php"><i class="fa fa-check-square"></i> Expenses Approval</a>
        <a href="accviewexpenses.php"><i class="fa fa-file-invoice-dollar"></i> View Expenses</a>
        <a href="accledger.php"><i class="fa fa-bookmark"></i> View Ledger</a>
        <a href="acctaxreport.php"><i class="fa fa-calculator"></i> Tax Report Table</a>
        <a href="mainpage.php"><i class="fa fa-sign-out-alt"></i> Exit</a>
</div>

<div class="form-container" style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: rgba(50, 50, 50, 0.9); border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); color: #e0e0e0;">
    <h1 style="text-align: center; color: #4A90E2; font-size: 1.8rem; margin-bottom: 20px;">Expense Tracking Form</h1>
    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">

        <!-- Name Input -->
        <label for="username" style="font-weight: bold; color: #e0e0e0;">Name</label>
        <input id="username" name="username" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>

        <!-- UserID Input -->
        <label for="userid" style="font-weight: bold; color: #e0e0e0;">UserID</label>
        <input id="userid" name="userid" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>

        <!-- Expense Type Dropdown -->
        <label for="expense_type" style="font-weight: bold; color: #e0e0e0;">Expense Type</label>
        <select id="expense_type" name="expense_type" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required onchange="showSubExpenseType()">
            <option value="">Select an expense type</option>
            <option value="Account Payable">Account Payable</option>
            <option value="Restock Expenses">Restock Expenses</option>
            <option value="Rent">Rent</option>
            <option value="Utilities">Utilities</option>
            <option value="Advertising">Advertising</option>
            <option value="Taxes">Taxes</option>
            <option value="Salaries">Salaries</option>
            <option value="Supplies">Supplies</option>
        </select>

        <label for="sub_expense_type" style="font-weight: bold; color: #e0e0e0;">Sub Expense Type</label>
        <select id="sub_expense_type" name="sub_expense_type" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s; display: none;">
            <option value="">Select a sub-expense type</option>
            <option value="Supplies">Supplies</option>
            <option value="Salaries">Salaries</option>
            <option value="Taxes">Taxes</option>
        </select>

        <!-- Amount Input -->
        <label for="amount" style="font-weight: bold; color: #e0e0e0;">Amount</label>
        <input type="number" id="amount" name="amount" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>

        <label for="payment" style="font-weight: bold; color: #e0e0e0;">Expense Type</label>
        <select id="payment" name="payment" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>
            <option value="Cash">Cash</option>
            <option value="Account Payable">Account Payable</option>
        </select>



        <!-- Date Input -->
        <label for="date" style="font-weight: bold; color: #e0e0e0;">Date</label>
        <input type="date" id="date" name="date" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>

        <!-- Vendor Dropdown -->
        <label for="vendor" style="font-weight: bold; color: #e0e0e0;">Vendor</label>
        <select id="vendor" name="vendor" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" required>
            <option value="" style="color: #555;">Select a vendor</option>
            <?php
            // Fetch vendors from the database
            $result = $conn->query("SELECT name FROM suppliers");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['name']}'>{$row['name']}</option>";
            }
            ?>
            <option value="custom">Custom</option>
        </select>

        <!-- Custom Vendor Input (shown when "Custom" is selected) -->
        <input type="text" id="custom_vendor" name="custom_vendor" class="custom-vendor" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" placeholder="Enter custom vendor">

        <!-- Attachments Input -->
        <label for="attachments" style="font-weight: bold; color: #e0e0e0;">Attachments</label>
        <input type="file" id="attachments" name="attachments[]" multiple style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;">

        <!-- Notes Textarea -->
        <label for="notes" style="font-weight: bold; color: #e0e0e0;">Notes</label>
        <textarea id="notes" name="notes" rows="4" style="padding: 10px; font-size: 1rem; border-radius: 5px; border: 1px solid #555; background-color: #333; color: #e0e0e0; transition: all 0.3s;" placeholder="Add additional details..."></textarea>

        <!-- Submit Button -->
        <button type="submit" style="background-color: #4A90E2; color: white; font-size: 1rem; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; margin-top: 20px; text-align: center;">
            Submit Expense
        </button>
    </form>
</div>


</div>


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

    // Get sidebar and main content
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");

    // Open the sidebar and push the main content
    sidebar.style.left = "0";
    mainContent.style.marginLeft = "250px"; // Adjust this value to the width of your sidebar




    function showSubExpenseType() {
        const expenseType = document.getElementById("expense_type").value;
        const subExpenseType = document.getElementById("sub_expense_type");
        
        if (expenseType === "Account Payable") {
            subExpenseType.style.display = "block";  // Show the sub-expense dropdown
        } else {
            subExpenseType.style.display = "none";   // Hide the sub-expense dropdown
        }
    }


</script>

</body>
</html>
