<?php
// Database connection
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "pos"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the posted data
    $log_id = $_POST['log_id'];
    $customer_name = $_POST['customer_name'];
    $customer_id = $_POST['customer_id'];
    $contact_info = $_POST['contact_info'];
    $problem_desc = $_POST['problem_desc'];
    $followup_date = $_POST['followup_date'];
    $products_json = $_POST['products']; // JSON string of products
    $services_json = $_POST['services']; // JSON string of services
    $total_product_price = $_POST['total_product_price'];
    $total_service_price = $_POST['total_service_price'];
    $grand_total = $_POST['grand_total'];

    // Insert quotation data into the database
    $insert_query = "INSERT INTO quotation (log_id, customer_name, customer_id, contact_info, problem_desc, followup_date, products, services, total_product_price, total_service_price, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_query);
    $stmt_insert->bind_param("isssssssddd", $log_id, $customer_name, $customer_id, $contact_info, $problem_desc, $followup_date, $products_json, $services_json, $total_product_price, $total_service_price, $grand_total);

    // Execute the statement
    if ($stmt_insert->execute()) {
        // If the insert was successful, redirect with log_id
        echo '<div style="background-color: #28a745; color: white; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 20px;">
            Quotation saved successfully!
          </div>';
        echo '<script>
                setTimeout(function() {
                    window.location.href = "generate_quotation.php?log_id=' . $log_id . '";
                }, 1000); // 1000 milliseconds = 1 second
              </script>';

        exit(); // Important: exit after redirect to stop further execution
    } else {
        // Handle error - consider logging this or displaying an error message
        echo "Error saving quotation: " . $stmt_insert->error;
    }
}




// Get log_id from the URL
$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;

// Fetch customer log details
$log_query = "SELECT * FROM testcustomer_logs WHERE log_id = ?";
$stmt_log = $conn->prepare($log_query);
$stmt_log->bind_param("i", $log_id);
$stmt_log->execute();
$log_result = $stmt_log->get_result();
$customer_log = $log_result->fetch_assoc();

// Fetch associated products with price
$product_query = "SELECT product_needed, quantity, price FROM testcustomer_products WHERE log_id = ?";
$stmt_product = $conn->prepare($product_query);
$stmt_product->bind_param("i", $log_id);
$stmt_product->execute();
$product_result = $stmt_product->get_result();
$products = $product_result->fetch_all(MYSQLI_ASSOC);

// Fetch associated services with price
$service_query = "
    SELECT s.service_needed, srv.price 
    FROM testcustomer_services s 
    LEFT JOIN services srv ON s.service_needed = srv.service_name 
    WHERE s.log_id = ?";
$stmt_service = $conn->prepare($service_query);
$stmt_service->bind_param("i", $log_id);
$stmt_service->execute();
$service_result = $stmt_service->get_result();
$services = $service_result->fetch_all(MYSQLI_ASSOC);

// Close the statements
$stmt_log->close();
$stmt_product->close();
$stmt_service->close();

// Initialize total variables
$total_product_price = 0;
$total_service_price = 0;

// Calculate total product price
foreach ($products as $product) {
    $total_product_price += $product['quantity'] * $product['price'];
}

// Calculate total service price
foreach ($services as $service) {
    $total_service_price += $service['price'];
}

// Calculate grand total
$grand_total = $total_product_price + $total_service_price;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed; /* Background image */
            background-size: cover; /* Cover the entire body */
            color: #fff; /* White text for better contrast */
            margin: 20px;
        }

        .quotation {
            width: 1000px; /* Increased max-width */
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for the quotation box */
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); /* Darker shadow for depth */
            border-radius: 8px; /* Rounded corners for a softer look */
        }

        h1, h2, h3 {
            color: #fff; /* White headings for better visibility */
        }

        table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid rgba(255, 255, 255, 0.5); /* Light border for table and cells */
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #28a745; /* Green background for table headers */
            color: white; /* White text for headers */
        }

        td {
            background-color: rgba(255, 255, 255, 0.05); /* Slightly transparent background for table cells */
            color: #fff; /* White text for cells */
        }

        

        /* PDF Button styling */
        #updateTermsBtn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 20px;
        }

        #updateTermsBtn:hover {
            background-color: #45a049;
        }

        /* Modal (pop-up) styling */
        #termsModal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1; /* On top */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with transparency */
        }

        /* Modal content box */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 50%; /* Could be more or less, depending on screen size */
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        }

        /* Text area styling */
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Close button inside modal */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Update button inside modal */
        #saveTermsBtn {
            padding: 10px 20px;
            background-color: #008CBA;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        #saveTermsBtn:hover {
            background-color: #007BB5;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            
        }


    </style>

</head>

<body>

<div class="container">


<div class="quotation">

    <button onclick="window.location.href='mainpage.php'" style="background-color: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
                Home 
    </button>

    <h1>Quotation</h1>
    
    <h2>Customer Details</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($customer_log['name']); ?></p>
    <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($customer_log['icnumber']); ?></p>
    <p><strong>Contact Info:</strong> <?php echo htmlspecialchars($customer_log['contact_info']); ?></p>
    <p><strong>Problem Description:</strong> <?php echo nl2br(htmlspecialchars($customer_log['problem_desc'])); ?></p>
    <p><strong>Follow-up Date:</strong> <?php echo htmlspecialchars($customer_log['followupdate']); ?></p>
    
    <h2>Products Needed</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['product_needed']); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No products needed.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <h2>Services Needed</h2>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Price (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['service_needed']); ?></td>
                        <td><?php echo htmlspecialchars($service['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No services needed.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>    




    <input type="hidden" id="customerPhone" value="<?php echo preg_replace('/\D/', '', $customer_log['contact_info']); ?>">

    

    <form id="saveQuotationForm" action="generate_quotation.php" method="POST">
        <input type="hidden" name="log_id" value="<?php echo $log_id; ?>">
        <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_log['name']); ?>">
        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_log['icnumber']); ?>">
        <input type="hidden" name="contact_info" value="<?php echo htmlspecialchars($customer_log['contact_info']); ?>">
        <input type="hidden" name="problem_desc" value="<?php echo nl2br(htmlspecialchars($customer_log['problem_desc'])); ?>">
        <input type="hidden" name="followup_date" value="<?php echo htmlspecialchars($customer_log['followupdate']); ?>">
        <input type="hidden" name="products" value='<?php echo json_encode($products); ?>'> <!-- JSON string of products -->
        <input type="hidden" name="services" value='<?php echo json_encode($services); ?>'> <!-- JSON string of services -->
        <input type="hidden" name="total_product_price" value="<?php echo $total_product_price; ?>">
        <input type="hidden" name="total_service_price" value="<?php echo $total_service_price; ?>">
        <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">

        <br>
        <br>

        <div style="text-align: center;">

        <button type="submit" style="background-color: #28A745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
            Save Quotation
        </button>

        </div>

    </form>
        
        <div style="text-align: right;">

            <button onclick="openWhatsApp()" style="background-color: #25D366; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; ">
                    Send to Customer
            </button>

        </div>

        <br>
        <br>

        <div class="ImportantButton" style="display: flex; justify-content: center; gap: 15px; align-items: center; margin-top: 20px;">

            <button onclick="window.location.href='generate_quotation_pdf.php?log_id=<?php echo $log_id; ?>'" style="background-color: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
                Download Quotation as PDF
            </button>


            <button onclick="window.location.href='generate_invoice_pdf.php?log_id=<?php echo $log_id; ?>'" style="background-color: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s;">
                Download Invoice as PDF
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



        <!-- Terms & Conditions Update Modal -->
        <div id="termsModal" style="display:none;">
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); max-width: 500px; margin: auto;">
                <h2 style="font-family: Arial, sans-serif; color: #333;">Edit Quotation Terms and Conditions</h2>
                <form id="termsForm" method="POST" action="update_terms.php">
                    <textarea name="terms" id="terms" style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);">
                        <?php echo isset($_SESSION['terms']) ? $_SESSION['terms'] : 'Validity: This quotation is valid for 30 days from the date issued.
                            1. Payment: Payment is due within 30 days of invoice date.
                            2. Delivery: Delivery dates are estimates and may vary.
                            3. Acceptance: Please sign and return this quotation to confirm acceptance.
                            4. Warranty: All products come with a one-year warranty against defects'; ?>
                    </textarea><br><br>
                    <button type="submit" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                        Update
                    </button>
                    <button type="button" onclick="document.getElementById('termsModal').style.display='none'" style="background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); margin-left: 10px;">
                        Close
                    </button>
                </form>
            </div>

        </div>



        <script>
            // Function to close the modal
            function closeTermsModal() {
                document.getElementById('termsModal').style.display = 'none';
            }
        </script>


</div>


</div>



<script>
    

        function openWhatsApp() {
        let customerPhone = document.getElementById('customerPhone').value.trim(); // Get phone number from hidden input field and remove whitespace

        // Check if the phone number starts with a plus (+) sign (indicating it already has a country code)
        if (!customerPhone.startsWith("+")) {
            // If the phone number does not start with a country code, add Malaysia's country code (+60)
            if (customerPhone.startsWith("0")) {
                // Remove the leading zero from local numbers
                customerPhone = customerPhone.substring(1);
            }
            customerPhone = "60" + customerPhone;
        }

        if (customerPhone) {
            const whatsappUrl = `https://wa.me/${customerPhone}`;
            window.open(whatsappUrl, '_blank'); // Open WhatsApp chat in new tab
        } else {
            alert("No valid phone number available.");
        }
    }





</script>

</body>
</html>
