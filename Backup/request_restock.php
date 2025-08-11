<?php
// Session control
session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);  // Get current file name

// Database connection
$host = 'localhost';
$db = 'pos';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!$employee_id) {
    header("Location: login.php");
    exit();
}

// Check if the user role has permission to access the current page
$query = "SELECT * FROM roles_permission WHERE role_name = ? AND permission_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $role_name, $current_page);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: unauthorized.php");
    exit();
}

// Fetch products and suppliers for restocking
$product_query = "SELECT * FROM productinventorytable";
$product_result = $conn->query($product_query);

$supplier_query = "SELECT * FROM suppliers";
$supplier_result = $conn->query($supplier_query);

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_products = $_POST['products'] ?? [];
    $selected_quantities = $_POST['quantities'] ?? [];
    $selected_supplier = $_POST['supplier'] ?? '';

    // Create PDF if products are selected
    if (!empty($selected_products) && $selected_supplier) {
        // Fetch supplier info
        $supplier_query = "SELECT * FROM suppliers WHERE id = ?";
        $supplier_stmt = $conn->prepare($supplier_query);
        $supplier_stmt->bind_param("i", $selected_supplier);
        $supplier_stmt->execute();
        $supplier_info = $supplier_stmt->get_result()->fetch_assoc();

        // Include the separate file and call the generatePDF function
        require_once("C:\\xampp\\htdocs\\DevelopmentProject\\generate_restock_pdf.php");
        generatePDF($selected_products, $selected_quantities, $supplier_info, $conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            color: #fff;
        }
        h1 {
            text-align: center;
        }
        .product-item {
            margin-bottom: 10px;
        }
        .product-item input {
            width: 50px;
            margin-left: 10px;
        }
        /* General button styles */
        button {
            display: inline-block;
            padding: 12px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            transition: background-color 0.3s, transform 0.2s;
            width: auto;
            margin: 10px 5px;
        }

        /* Submit Button */
        .submit-btn {
            background-color: #007BFF; /* Blue background */
        }

        .submit-btn:hover {
            background-color: #0056b3; /* Darker green on hover */
            transform: translateY(-2px); /* Slight hover lift */
        }

        .submit-btn:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Focus shadow */
        }

        /* Mailto Button */
        .mailto-btn {
            background-color: #4CAF50; /* Blue background */
        }

        .mailto-btn:hover {
            background-color: #45a049; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight hover lift */
        }

        .mailto-btn:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Focus shadow */
        }

        /* WhatsApp Button */
        .whatsapp-btn {
            background-color: #25D366; /* WhatsApp green */
        }

        .whatsapp-btn:hover {
            background-color: #128C7E; /* Darker WhatsApp green on hover */
            transform: translateY(-2px); /* Slight hover lift */
        }

        .whatsapp-btn:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); /* Focus shadow */
        }

        #searchBarcode:focus {
            border-color: #007bff; /* Bright blue border when focused */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5); /* Subtle blue glow */
        }


        
    </style>

    <script>
        function openMail() {
            const supplierSelect = document.getElementById('supplier');
            const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
            const supplierEmail = selectedOption.getAttribute('data-email');
            const subject = "Restock Request";
            const body = "Hello,\n\nPlease find attached the restock list.\n\nBest regards,\n[Your Name]";
            window.location.href = `mailto:${supplierEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }

        function openWhatsApp() {
            const supplierSelect = document.getElementById('supplier');
            const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
            let supplierPhone = selectedOption.getAttribute('data-phone');
            
            if (supplierPhone) {
                // Check if the phone number starts with '0' and replace it with '+60'
                if (supplierPhone.startsWith('0')) {
                    supplierPhone = `+60${supplierPhone.slice(1)}`;
                }
                
                // WhatsApp URL
                const whatsappUrl = `https://wa.me/${supplierPhone}`;
                window.open(whatsappUrl, '_blank');
            } else {
                alert("Please select a supplier with a valid phone number.");
            }
        }


    </script>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <br>

<div class="container">
    <h1>Restock Products</h1>
    <form method="POST" action="">
        <div>
            <label for="supplier">Select Supplier:</label>
            <select name="supplier" id="supplier" required style="width: 40%; padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; background-color: #fff; color: #333; appearance: none; -webkit-appearance: none; -moz-appearance: none; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-top: 10px;">
                <option value="" style="color: #666;">-- Choose a supplier --</option>
                <?php while ($supplier_row = $supplier_result->fetch_assoc()): ?>
                    <option value="<?php echo $supplier_row['id']; ?>" 
                            data-email="<?php echo htmlspecialchars($supplier_row['email']); ?>" 
                            data-phone="<?php echo htmlspecialchars($supplier_row['phone']); ?>" 
                            style="color: #333;">
                        <?php echo htmlspecialchars($supplier_row['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <br>
        <br>
        <button type="submit" class="submit-btn">Generate & Download PDF</button>
        <button type="button" class="mailto-btn" onclick="openMail()">Auto Mail Supplier</button>
        <button type="button" class="whatsapp-btn" onclick="openWhatsApp()">WhatsApp Supplier</button>
        <br>

        <h3>Select Products to Restock</h3>
        <br>
        <div style="max-width: 600px; margin: 0 auto; padding: 10px; background-color: #282c34; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); text-align: center;">
            <input type="text" id="searchBarcode" placeholder="Search by Barcode" oninput="filterProducts()" 
            style="width: 100%; padding: 14px 20px; border: 1px solid #555; border-radius: 8px; font-size: 16px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #e0e0e0; background-color: #333; transition: border-color 0.3s ease, box-shadow 0.3s ease; outline: none; box-sizing: border-box;">
        </div>
        <br>
        <div class="product-list" style="display: flex; flex-direction: column; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #2c3e50; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); color: #ecf0f1;">
            <?php while ($product_row = $product_result->fetch_assoc()): ?>
                <div class="product-item" data-barcode="<?php echo htmlspecialchars($product_row['barcode']); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border: 1px solid #444; border-radius: 8px; margin: 8px 0; background-color: #34495e; transition: background-color 0.3s ease; color: #ecf0f1;">
                    <input type="checkbox" name="products[<?php echo htmlspecialchars($product_row['barcode']); ?>]" value="1" class="product-checkbox" style="margin-right: 15px; accent-color: #1abc9c;">
                    <label class="product-label" style="flex-grow: 1; font-weight: 500; font-size: 14px; color: #ecf0f1; line-height: 1.4;"><?php echo htmlspecialchars($product_row['brand']) . ' - ' . htmlspecialchars($product_row['producttype']) . ' - ' . htmlspecialchars($product_row['variant']); ?> - <strong>Stock:</strong> <?php echo htmlspecialchars($product_row['stock']); ?></label>
                    <input type="number" name="quantities[<?php echo htmlspecialchars($product_row['barcode']); ?>]" min="1" placeholder="Quantity" class="quantity-input" style="width: 80px; padding: 8px; border: 1px solid #5a6d7e; border-radius: 4px; background-color: #2c3e50; color: #ecf0f1; font-size: 14px; transition: border-color 0.3s, background-color 0.3s;" onfocus="this.style.borderColor='#1abc9c'; this.style.backgroundColor='#34495e';" onblur="this.style.borderColor='#5a6d7e'; this.style.backgroundColor='#2c3e50';">
                </div>
            <?php endwhile; ?>
        </div>

    </form>
</div>

<script>
    function filterProducts() {
        const searchValue = document.getElementById('searchBarcode').value.toLowerCase(); // Get the search input
        const productItems = document.querySelectorAll('.product-item'); // Get all product items
        
        productItems.forEach(item => {
            const barcode = item.getAttribute('data-barcode').toLowerCase(); // Get the barcode of the product
            if (barcode.includes(searchValue)) {
                item.style.display = 'flex'; // Show the item if barcode matches
            } else {
                item.style.display = 'none'; // Hide the item if barcode doesn't match
            }
        });
    }
</script>


</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
