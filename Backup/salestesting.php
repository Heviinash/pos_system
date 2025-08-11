<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check for database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate unique transaction ID
function generateTransactionID($conn) {
    $isUnique = false;
    $transaction_id = '';

    // Keep generating until a unique ID is found
    while (!$isUnique) {
        // Generate a random alphanumeric string of 29 characters and prefix with "TID"
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 29);
        $transaction_id = 'TID' . $randomString;

        // Check if this transaction ID already exists in the database
        $query = "SELECT COUNT(*) AS count FROM sales_transaction WHERE transaction_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] == 0) {
            $isUnique = true;  // Found a unique transaction ID
        }

        // Close statement
        $stmt->close();
    }

    return $transaction_id;  
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcodes = $_POST['barcode'];
    $quantities = $_POST['quantity'];
    $icnumber = $_POST['icnumber'];

    $total_transaction_price = 0;
    $total_profit = 0; // Initialize total profit variable

    // Generate a unique transaction ID
    $transaction_id = generateTransactionID($conn);
    $_SESSION['transaction_id'] = $transaction_id; // Store transaction_id in session

    foreach ($barcodes as $index => $barcode) {
        $quantity = $quantities[$index];

        // Step 1: Fetch product details
        $product_query = "SELECT brand, producttype, variant, price, wholesaleprice, stock FROM productinventorytable WHERE barcode = ?";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        // Step 2: Check if the product exists and has enough stock
        if ($product && $product['stock'] >= $quantity) {
            // Calculate total price
            $total_price = $product['price'] * $quantity;
            $total_transaction_price += $total_price;

            // Calculate profit
            $wholesale_price = $product['wholesaleprice'];
            $profit = ($product['price'] - $wholesale_price) * $quantity;
            $total_profit += $profit; // Accumulate total profit

            // Step 3: Insert into sales_transaction table
            $sales_query = "INSERT INTO sales_transaction (transaction_id, employeeid, barcode, quantity, icnumber, total_price) VALUES (?, ?, ?, ?, ?, ?)";
            $sales_stmt = $conn->prepare($sales_query);
            $sales_stmt->bind_param("sssisd", $transaction_id, $_SESSION['employeeid'], $barcode, $quantity, $icnumber, $total_price);

            if ($sales_stmt->execute()) {
                // Step 4: Update stock in productinventorytable
                $new_stock = $product['stock'] - $quantity;
                $update_query = "UPDATE productinventorytable SET stock = ? WHERE barcode = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("is", $new_stock, $barcode);
                $update_stmt->execute();
            } else {
                echo "<p class='error'>Error recording the sale for product $barcode. Please try again.</p>";
            }
        } else {
            echo "<p class='error'>Insufficient stock or product not found for barcode $barcode.</p>";
        }

        // Close the first statement
        $stmt->close();
    }

    // After processing all products, insert total profit into profit table
    $profit_query = "INSERT INTO profit (transaction_id, profit, sale_datetime) VALUES (?, ?, NOW())";
    $profit_stmt = $conn->prepare($profit_query);
    $profit_stmt->bind_param("sd", $transaction_id, $total_profit);
    $profit_stmt->execute();

    echo "<p class='success'>Sale completed successfully! Total Transaction Price: RM$total_transaction_price</p>";
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Transaction</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <style>
        /* General body settings */
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        /* Container styling */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background for dark theme */
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        }

        /* Heading styling */
        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        /* Form field styling */
        label {
            display: block;
            margin: 10px 0 5px;
            color: #ddd;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #222;
            color: #fff;
            box-sizing: border-box;
        }

        /* General button styling */
        button {
            background-color: #007bff; /* Medium blue color */
            color: white;
            padding: 14px; /* Medium padding for appropriate size */
            font-size: 16px; /* Medium-sized text */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 20%; /* Buttons will take up full width in the form */
            margin-bottom: 15px; /* Space between buttons */
            transition: background 0.3s ease, transform 0.2s ease; /* Smooth hover effect */
        }

        /* Hover effect for buttons */
        button:hover {
            background-color: #0056b3; /* Darker shade of blue on hover */
            transform: scale(1.03); /* Slight enlarge effect on hover */
        }

        /* Individual button styling */
        #addProduct {
            background-color: cyan; /* Green button for 'Add Product' */
        }

        #addProduct:hover {
            background-color: darkcyan; /* Darker green on hover */
        }

        #startScanner {
            background-color: skyblue; /* Yellow for 'Scan Barcode' button */
        }

        #startScanner:hover {
            background-color: lightblue; /* Darker yellow on hover */
        }

        button[type="submit"] {
            background-color: maroon; /* Cyan button for 'Complete Sale' */
        }

        button[type="submit"]:hover {
            background-color: indianred; /* Darker cyan on hover */
        }

        /* Success message styling */
        .success {
            color: #28a745;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Error message styling */
        .error {
            color: #dc3545;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Scanner area */
        #scanner {
            display: none; /* Hidden by default */
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #333; /* Dark background for the scanner */
        }

    </style>
</head>
<body>
    <div class="container">
        <?php
        if (isset($_SESSION['employeeid'])) {
            echo "<p>Employee ID: <strong>" . $_SESSION['employeeid'] . "</strong></p>";
        } else {
            echo "<p>Employee ID not set.</p>";
        }
        ?>

        <h1>Sales Transaction</h1>

        <!-- Form to capture IC number -->
        <form method="POST" action="salestesting.php" id="salesForm">
            <label for="icnumber">IC Number:</label>
            <input type="text" name="icnumber" required>

            <!-- Table to display scanned products -->
            <table id="productTable" style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>TotalPrice</th>

                    </tr>
                </thead>
                <tbody id="productRows"></tbody>


                <tfoot>
                    <tr>
                        <td colspan="4">Total:</td>
                        <td id="totalAmount">0.00</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Scanner area -->
            <div id="scanner">
                <label for="barcode">Scan Product Barcode:</label>
                <input type="text" id="barcode" placeholder="Scan barcode here..." autofocus>
                <button type="button" id="addProduct">Add Product</button>
                <button type="button" id="startScanner">Start Barcode Scanner</button>
            </div>
            

            <button type="submit">Complete Sale</button>
        </form>
    </div>

    <script>
        // Handle add product button click
        document.getElementById('addProduct').addEventListener('click', async function() {
            const barcode = document.getElementById('barcode').value;
            const quantity = prompt("Enter quantity for barcode " + barcode + ":"); // Prompt for quantity
            if (quantity && !isNaN(quantity) && quantity > 0) {
                try {
                    // Fetch product name and price from the database using AJAX
                    const response = await fetch('fetch_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ barcode: barcode })
                    });
                    const data = await response.json();

                    const productName = data.product_name ? data.product_name : "Not Found"; // Default if not found
                    const productPrice = data.price ? parseFloat(data.price).toFixed(2) : "N/A"; // Convert price to number and format



                    // Calculate Total
                    const totalPrice = (productPrice !== "N/A") ? (productPrice * quantity).toFixed(2) : "N/A"; 
                    const productTable = document.getElementById('productRows');
                    const newRow = document.createElement('tr');

                    // Create cells for barcode, product name, quantity, and price
                    newRow.innerHTML = `
                        <td>${barcode}</td>
                        <td>${productName}</td>
                        <td>${quantity}</td>
                        <td>${productPrice}</td> 
                        <td>${totalPrice}</td>
                        <!-- Add price cell -->
                    `;
                    productTable.appendChild(newRow);




                    // Create hidden inputs for barcode and quantity
                    const hiddenBarcodeInput = document.createElement('input');
                    hiddenBarcodeInput.type = 'hidden';
                    hiddenBarcodeInput.name = 'barcode[]';
                    hiddenBarcodeInput.value = barcode;

                    const hiddenQuantityInput = document.createElement('input');
                    hiddenQuantityInput.type = 'hidden';
                    hiddenQuantityInput.name = 'quantity[]';
                    hiddenQuantityInput.value = quantity;

                    // Append hidden inputs to the form
                    document.getElementById('salesForm').appendChild(hiddenBarcodeInput);
                    document.getElementById('salesForm').appendChild(hiddenQuantityInput);
                

                    // Clear the barcode input
                    document.getElementById('barcode').value = '';
                } catch (error) {
                    alert("Error fetching product details: " + error.message);
                }
            } else {
                alert("Please enter a valid quantity.");
            }
        });


        // Handle start scanner button click
        document.getElementById('startScanner').addEventListener('click', function() {
            const scanner = document.getElementById('scanner');
            scanner.style.display = scanner.style.display === 'none' ? 'block' : 'none';
        });    

    </script>
</body>
</html>
