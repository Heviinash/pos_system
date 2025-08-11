<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch settings from the database
$settings_query = "SELECT * FROM invoicepdf_settings LIMIT 1";
$settings_result = $conn->query($settings_query);

$watermark_text = 'INVOICE'; // Default value
$font_size = 12; // Default font size
$font_family = 'Arial'; // Default font family
$sender_info_position = 'left'; // Default sender info position

// Check if settings are already saved in the database
if ($settings_result->num_rows > 0) {
    $settings = $settings_result->fetch_assoc();
    $watermark_text = $settings['watermark_text'];
    $font_size = $settings['font_size'];
    $font_family = $settings['font_family'];
    $sender_info_position = $settings['sender_info_position'];
}

// Check if form is submitted to update settings
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $watermark_text = !empty($_POST['watermark_text']) ? $_POST['watermark_text'] : 'INVOICE';
    $font_size = !empty($_POST['font_size']) ? (int)$_POST['font_size'] : 12; // Default font size
    $font_family = !empty($_POST['font_family']) ? $_POST['font_family'] : 'Arial'; // Default font family
    $sender_info_position = !empty($_POST['sender_info_position']) ? $_POST['sender_info_position'] : 'left';

    // Check if any required value is empty
    if (empty($watermark_text) || empty($font_size) || empty($font_family) || empty($sender_info_position)) {
        echo "Some form fields are empty. Please check your input.";
    } else {
        // Update data into the invoicepdf_settings table
        $sql = "UPDATE invoicepdf_settings SET 
                watermark_text = '$watermark_text',
                font_size = $font_size,
                font_family = '$font_family',
                sender_info_position = '$sender_info_position'";

        if ($conn->query($sql) === TRUE) {
            echo "Settings updated successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice PDF Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        h2 {
            color: #007BFF;
            text-align: center;
            margin-bottom: 30px;
        }

        label {
            font-size: 15px;
            color: #555;
            display: block;
            margin: 15px 0 5px;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        input[type="submit"], button {
            width: 100%;
            background-color: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        input[type="submit"]:hover, button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Customize Invoice PDF Settings</h2>
        <form action="invoicepdfsettings.php" method="POST">
            <label for="watermark_text">Watermark Text:</label>
            <input type="text" id="watermark_text" name="watermark_text" value="<?php echo htmlspecialchars($watermark_text); ?>" required><br><br>

            <label for="font_size">Font Size:</label>
            <select id="font_size" name="font_size" required>
                <option value="8" <?php echo ($font_size == 8) ? 'selected' : ''; ?>>8</option>
                <option value="9" <?php echo ($font_size == 9) ? 'selected' : ''; ?>>9</option>
                <option value="10" <?php echo ($font_size == 10) ? 'selected' : ''; ?>>10</option>
                <option value="11" <?php echo ($font_size == 11) ? 'selected' : ''; ?>>11</option>
                <option value="12" <?php echo ($font_size == 12) ? 'selected' : ''; ?>>12</option>
                <option value="14" <?php echo ($font_size == 14) ? 'selected' : ''; ?>>14</option>
            </select><br><br>

            <label for="font_family">Font Family:</label>
            <select id="font_family" name="font_family" required>
                <option value="helvetica" <?php echo ($font_family == 'helvetica') ? 'selected' : ''; ?>>Helvetica</option>
                <option value="times" <?php echo ($font_family == 'times') ? 'selected' : ''; ?>>Times</option>
                <option value="courier" <?php echo ($font_family == 'courier') ? 'selected' : ''; ?>>Courier</option>
            </select><br><br>

            <label for="sender_info_position">Sender Info Position:</label>
            <select id="sender_info_position" name="sender_info_position">
                <option value="left" <?php echo ($sender_info_position == 'left') ? 'selected' : ''; ?>>Left</option>
                <option value="right" <?php echo ($sender_info_position == 'right') ? 'selected' : ''; ?>>Right</option>
                <option value="center" <?php echo ($sender_info_position == 'center') ? 'selected' : ''; ?>>Center</option>
                <option value="topright" <?php echo ($sender_info_position == 'topright') ? 'selected' : ''; ?>>Top Right</option>
            </select><br><br>

            <button type="submit">Save Settings</button>
        </form>


        <button onclick="document.getElementById('invoiceTermsModal').style.display='block'" style="background-color: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
                Invoice Update Terms and Conditions
        </button>

        <button onclick="window.location.href='mainpage.php'" style="background-color: darkcyan; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
            Home Button
        </button>



    </div>

    <div id="invoiceTermsModal" style="display:none;">
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); max-width: 500px; margin: auto;">
                <h2 style="font-family: Arial, sans-serif; color: #333;">Edit Invoice Terms and Conditions</h2>
                <form id="invoiceTermsForm" method="POST" action="update_invoice_terms.php">
                    <textarea name="invoice_terms" id="invoice_terms" style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);">
                        <?php echo isset($_SESSION['invoice_terms']) ? $_SESSION['invoice_terms'] : 'Terms and Conditions

1. Payment Terms: Payment is due upon receipt of the invoice unless otherwise agreed in writing.
2. Late Payments: Any overdue amounts may incur a late fee of 1.5% per month on the outstanding balance.
3. Refund Policy: All sales are final. Refunds will only be issued for defective products or services, and requests must be made within 14 days of purchase.
4. Delivery: Delivery times are estimates and may vary. We are not responsible for delays caused by third parties.
5. Liability: Our liability is limited to the purchase price of the product or service. We are not liable for any indirect or consequential damages.'; ?>
                    </textarea><br><br>
                    <button type="submit" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                        Update
                    </button>
                    <button type="button" onclick="document.getElementById('invoiceTermsModal').style.display='none'" style="background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); margin-left: 10px;">
                        Close
                    </button>
                </form>
            </div>
        </div>
    
</body>
</html>
