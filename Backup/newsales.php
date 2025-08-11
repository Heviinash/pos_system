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


$total_profit = 0; // Initialize total profit variable

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
        <form id="salesForm" method="POST" action="">
            <div id="productFields">
                <div class="productField">
                    <label for="icnumber">Customer IC:</label>
                    <input type="text" name="icnumber" required>

                    <label for="barcode[]">Product Barcode:</label>
                    <input type="text" name="barcode[]" required readonly>
                    
                </div>
            </div>
            
            <button type="button" id="addProduct">Add Another Product</button>
            <button type="button" id="startScanner">Scan Barcode</button>
            <button type="submit">Complete Sale</button>




            <?php if (isset($total_transaction_price)) { ?>
    <button type="button" onclick="window.location.href='generate_receipt.php?icnumber=<?php echo $icnumber; ?>&total=<?php echo $total_transaction_price; ?>&transaction_id=<?php echo $_SESSION['transaction_id']; ?>'">Generate PDF Receipt</button>
            <?php } ?>




            <table id="productTable" style="width: 100%; margin-top: 20px; display: none;">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Product rows will be inserted here -->
                </tbody>
            </table>


        </form>

        <div id="scanner"></div>
    </div>

    <script>
        document.getElementById('addProduct').addEventListener('click', function() {
            const productFields = document.getElementById('productFields');
            const newProductField = document.createElement('div');
            newProductField.classList.add('productField');
            newProductField.innerHTML = `
                <label for="barcode[]">Product Barcode:</label>
                <input type="text" name="barcode[]" required readonly>
            `;
            productFields.appendChild(newProductField);
        });

        document.getElementById('startScanner').addEventListener('click', function() {
            const scanner = document.getElementById('scanner');
            scanner.style.display = 'block'; // Show the scanner

            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: scanner,
                },
                decoder: {
                    readers: ["code_128_reader"] // Adjust this based on your barcode type
                },
            }, function(err) {
                if (err) {
                    console.log(err);
                    return;
                }
                Quagga.start();
            });


            Quagga.onDetected(function(data) {
                const code = data.codeResult.code;
                const inputs = document.querySelectorAll('input[name="barcode[]"]');
                let foundEmpty = false;

                for (let input of inputs) {
                    if (input.value === '') {
                        input.value = code; // Set the scanned barcode
                        foundEmpty = true;

                        // Fetch product details via AJAX
                        fetch(`get_product_details.php?barcode=${code}`)
                            .then(response => response.json())
                            .then(product => {
                                if (product) {
                                    const productTable = document.getElementById('productTable');
                                    const productTableBody = productTable.querySelector('tbody');

                                    // Create a new row for the product
                                    const newRow = document.createElement('tr');
                                    newRow.innerHTML = `
                                        <td>${product.brand} ${product.producttype} ${product.variant}</td>
                                        <td>${product.price}</td>
                                        <td><input type="number" name="quantity[]" min="1" value="1" required></td>
                                    `;
                                    productTableBody.appendChild(newRow);
                                    productTable.style.display = 'table'; // Show the table
                                } else {
                                    alert("Product not found for barcode: " + code);
                                }
                            })
                            .catch(error => {
                                console.error("Error fetching product details:", error);
                                alert("Could not fetch product details. Please try again.");
                            });
                        break;
                    }
                }

                if (!foundEmpty) {
                    alert("All barcode fields are filled. Please add another product to scan.");
                }

                Quagga.stop(); // Stop scanning after a successful scan
                scanner.style.display = 'none'; // Hide the scanner
            });

        });
    </script>
</body>
</html>
