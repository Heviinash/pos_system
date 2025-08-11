<?php

// Database connection using PDO
$host = "localhost";  // Replace with your database host
$username = "root";   // Replace with your database username
$password = "";       // Replace with your database password
$dbname = "pos";      // Replace with your database name

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log and display error if connection fails
    error_log("Database connection failed: " . $e->getMessage(), 3, '/path/to/logfile.log');
    die("Connection failed.");
}

session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);  // Get current file name

// Check if user is logged in
if (!$employee_id) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Check if the user role has permission to access the current page
$query = "SELECT * FROM roles_permission WHERE role_name = :role_name AND permission_name = :permission_name";
$stmt = $pdo->prepare($query);
$stmt->bindParam(":role_name", $role_name, PDO::PARAM_STR);
$stmt->bindParam(":permission_name", $current_page, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    // User doesn't have permission, redirect to unauthorized page
    header("Location: unauthorized.php");  // Replace with your unauthorized access page
    exit();
}

// Fetch business information
$query = "SELECT business_name, address, contact_number FROM businessregistration LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$businessInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Convert the business info to JSON format for JavaScript
$businessInfoJson = $businessInfo ? json_encode($businessInfo) : json_encode([]);

// Fetch suggestions for services or products based on input
if (isset($_GET['query']) && isset($_GET['type'])) {
    $query = $_GET['query'];
    $type = $_GET['type'];  // "service" or "product"
    $results = [];

    // Use prepared statements to prevent SQL injection
    if ($type == 'service') {
        $serviceQuery = "SELECT service_name, price FROM services WHERE service_name LIKE :query";
        $stmt = $pdo->prepare($serviceQuery);
        $stmt->bindValue(":query", "%$query%", PDO::PARAM_STR);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = ['name' => $row['service_name'], 'price' => $row['price'], 'type' => 'service'];
        }
    } elseif ($type == 'product') {
        $productQuery = "SELECT CONCAT(producttype, ' ', variant, ' ', brand) AS product_name, price FROM productinventorytable WHERE producttype LIKE :query OR variant LIKE :query OR brand LIKE :query OR barcode LIKE :query";
        $stmt = $pdo->prepare($productQuery);
        $stmt->bindValue(":query", "%$query%", PDO::PARAM_STR);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = ['name' => $row['product_name'], 'price' => $row['price'], 'type' => 'product'];
        }
    }

    echo json_encode($results);
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <title>Product/Service Selector</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-image: url('darkwallpaper.jpg'); /* Add the path to your wallpaper */
        background-size: cover;
        background-position: center;
        color: #ccc;
        margin: 0;
        padding: 20px;
    }

    h3 {
        color: #6DD47E;
    }

    .form-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: rgba(30, 30, 30, 0.9);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #555;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        border-radius: 50%;
        left: 4px;
        bottom: 4px;
        background-color: #222;
        transition: 0.4s;
    }

    input:checked + .slider {
        background-color: #6DD47E;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .switch-label {
        margin-left: 10px;
        font-weight: bold;
        color: #ccc;
    }

    input[type="text"], input[type="number"] {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        margin-bottom: 20px;
        border: 1px solid #444;
        border-radius: 4px;
        background-color: #222;
        color: #ccc;
        font-size: 16px;
    }

    .btn {
        background-color: #6DD47E;
        color: #222;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin: 10px 0;
    }

    .btn:hover {
        background-color: #58b765;
    }

    .product-list {
        margin-top: 20px;
        padding: 0;
        list-style-type: none;
    }

    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: rgba(40, 40, 40, 0.9);
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .product-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.7);
        transform: translateY(-2px);
    }

    .product-item .name {
        font-size: 16px;
        font-weight: 600;
        color: #ddd;
    }

    .product-item .price {
        font-size: 16px;
        color: #8bd5c0;
        font-weight: 500;
    }

    .product-item .actions {
        display: flex;
        gap: 10px;
    }

    .product-item .actions button {
        padding: 8px 16px;
        font-size: 14px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .product-item .actions button:hover {
        background-color: #0056b3;
    }

    .product-item .actions button:disabled {
        background-color: #444;
        cursor: not-allowed;
    }

    .suggestions {
        max-height: 200px;
        overflow-y: auto;
        margin-top: 10px;
        background-color: rgba(30, 30, 30, 0.9);
    }

    .suggestion-item {
        padding: 10px;
        background-color: rgba(50, 50, 50, 0.9);
        border: 1px solid #444;
        cursor: pointer;
        color: #ccc;
    }

    .suggestion-item:hover {
        background-color: #555;
    }
</style>

</head>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<body>
<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="navbar.css">
<br><br><br>
<div class="form-container">
    <h3>Name:</h3>
    <input type="text" name="customername" id="customername">
    <h3>Mobile Number:</h3>
    <input type="number" name="customernumber" id="customernumber">
</div>

<div class="form-container">
    <h3>Product/Service Selector</h3>

    <div>
        <label class="toggle-switch">
            <input type="checkbox" id="toggle" name="toggle" onclick="toggleProductService()">
            <span class="slider"></span>
        </label>
        <span class="switch-label" id="toggleLabel">Product</span>
    </div>

    <input type="text" id="product_service" name="product_service" placeholder="Enter product or service" onkeyup="fetchSuggestions(this.value)">

    <div class="suggestions" id="suggestions"></div>

    <h3>Price</h3>
    <input type="text" id="price" name="price" placeholder="Price" readonly>

    <h3>Quantity</h3>
    <input type="number" id="quantity" name="quantity" value="1" oninput="updatePrice()">

    <button class="btn" id="addMoreButton" onclick="addMoreProducts()">Proceed/Add More</button>

    <div class="product-list" id="productList"></div>
</div>

<div style="text-align: center;">
    <button class="btn" id="generatePDF" onclick="generatePDF()">Generate PDF</button>
</div>

<script>

    let selectedProduct = {};
let productList = []; // Ensure productList is initialized as an array

// Function to toggle between Product and Service
function toggleProductService() {
    var toggle = document.getElementById("toggle");
    var label = document.getElementById("toggleLabel");

    if (toggle.checked) {
        label.textContent = "Service";
    } else {
        label.textContent = "Product";
    }

    // Clear the suggestions when toggling
    document.getElementById("suggestions").innerHTML = '';
}

// Fetch suggestions based on the input and toggle state
function fetchSuggestions(query) {
    if (query.length > 2) {
        const type = document.getElementById("toggle").checked ? 'service' : 'product'; // Determine type (service or product)

        fetch(`?query=${query}&type=${type}`)
            .then(response => response.json())
            .then(data => displaySuggestions(data));
    }
}

// Display suggestions (filtered by service or product type)
function displaySuggestions(data) {
    let suggestions = document.getElementById("suggestions");
    suggestions.innerHTML = '';

    data.forEach(item => {
        let div = document.createElement('div');
        div.classList.add('suggestion-item');
        div.textContent = `${item.name} - RM${item.price}`;
        div.onclick = () => selectProduct(item);
        suggestions.appendChild(div);
    });
}


function selectProduct(item) {
    selectedProduct = item;
    document.getElementById("product_service").value = item.name;
    document.getElementById("price").value = item.price;
    document.getElementById("quantity").value = 1;
    document.getElementById("suggestions").innerHTML = '';
}

function updatePrice() {
    if (selectedProduct.price) {
        const quantity = document.getElementById("quantity").value;
        document.getElementById("price").value = selectedProduct.price * quantity;
    }
}

function addMoreProducts() {
    if (!selectedProduct.name || !document.getElementById("price").value || !document.getElementById("quantity").value) {
        alert("Please fill in all fields.");
        return;
    }

    let productItem = {
        name: selectedProduct.name,
        price: document.getElementById("price").value,
        quantity: document.getElementById("quantity").value
    };

    // Add the product to the productList array
    productList.push(productItem);

    const productListContainer = document.getElementById("productList");
    let productItemDiv = document.createElement("div");
    productItemDiv.classList.add("product-item");

    productItemDiv.innerHTML = `
        <div><strong>${productItem.name}</strong></div>
        <div>Price: RM${productItem.price}</div>
        <div>Quantity: ${productItem.quantity}</div>
    `;

    productListContainer.appendChild(productItemDiv);

    // Clear inputs for next entry
    document.getElementById("product_service").value = "";
    document.getElementById("price").value = "";
    document.getElementById("quantity").value = 1;
}

const businessInfo = <?php echo $businessInfoJson; ?>;
function generatePDF(businessName, businessAddress, businessContact) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();


        // Generate unique invoice number
    function generateRandomString(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    function generateUniqueInvoice() {
        const datePart = new Date().toISOString().slice(0, 10).replace(/-/g, ''); // Format: YYYYMMDD
        const randomPart = generateRandomString(9);
        return `INV-${datePart}-${randomPart}`;
    }

    const invoiceNumber = generateUniqueInvoice();




    const customerName = document.getElementById("customername").value;
    const customerNumber = document.getElementById("customernumber").value;

     // Business details from the fetched data
    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text(businessInfo.business_name, 105, 10, { align: "center" });
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text(businessInfo.address, 105, 15, { align: "center" });
    doc.text(`Contact: ${businessInfo.contact_number}`, 105, 20, { align: "center" });

    // Invoice title
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.text("Invoice", 105, 30, { align: "center" });
    doc.setFont("helvetica", "normal");
    doc.text(`Invoice Number: ${invoiceNumber}`, 10, 35);
    doc.text(`Date: ${new Date().toLocaleDateString()}`, 10, 40);


    // Customer details
    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    doc.text(`Customer Name: ${customerName}`, 10, 45);
    doc.text(`Mobile Number: ${customerNumber}`, 10, 50);


    // Table headers
    const tableStartY = 60;
    const cellHeight = 10;
    const productColumnWidth = 80; // Width for the product name column
    const priceColumnX = 95;      // X position for price column
    const quantityColumnX = 125;  // X position for quantity column
    const totalColumnX = 160;     // X position for total column

    doc.setFontSize(10);
    doc.setFont("helvetica", "bold");
    doc.text("Product", 10, tableStartY);       // Column 1: Product
    doc.text("Price", priceColumnX, tableStartY);         // Column 2: Price
    doc.text("Quantity", quantityColumnX, tableStartY);     // Column 3: Quantity
    doc.text("Total", totalColumnX, tableStartY);        // Column 4: Total
    doc.line(10, tableStartY + 2, 200, tableStartY + 2);  // Underline header row

    // Add products to the table
    let yPosition = tableStartY + cellHeight;
    doc.setFont("helvetica", "normal");

    productList.forEach(product => {
        // Ensure price is a number
        const price = parseFloat(product.price);  // Convert to a float
        if (isNaN(price)) return;  // Skip if price is not a valid number

        const total = price * product.quantity;

        // Handle wrapping for long product names
        let productName = product.name;
        const maxWidth = productColumnWidth; // Limit the width of the product name column
        const wrappedText = doc.splitTextToSize(productName, maxWidth);

        // Print product name (wrapping if necessary)
        wrappedText.forEach((line, index) => {
            if (index === 0) {
                doc.text(line, 10, yPosition); // First line of the product name
            } else {
                yPosition += cellHeight;
                doc.text(line, 10, yPosition); // Additional lines
            }
        });

        // Print other columns
        doc.text(`RM${price.toFixed(2)}`, priceColumnX, yPosition);    // Product price
        doc.text(`${product.quantity}`, quantityColumnX, yPosition); // Product quantity
        doc.text(`RM${total.toFixed(2)}`, totalColumnX, yPosition);   // Total for this product
        yPosition += cellHeight;
    });

    // Total amount
    const totalAmount = productList.reduce((sum, product) => {
        const price = parseFloat(product.price);  // Ensure price is a number
        if (!isNaN(price)) {
            return sum + (price * product.quantity);
        }
        return sum;
    }, 0);



    // Calculate Tax (6%)
    const taxRate = 0.06;  // 6% tax rate
    const taxAmount = totalAmount * taxRate;  // Tax is totalAmount * 6%

    // Calculate Grand Total (Total + Tax)
    const grandTotal = totalAmount + taxAmount;  // Add tax to total to get grand total




    // Add total amount
    yPosition += 10; // Add some space before total
    yPosition += cellHeight;
    doc.setFont("helvetica", "bold");
    doc.text(`Total Amount: RM${totalAmount.toFixed(2)}`, totalColumnX - 20, yPosition); // Fine-tuned X position for Total Amount



    doc.setFont("helvetica", "bold");
    doc.text(`Total Amount: RM${totalAmount.toFixed(2)}`, totalColumnX - 20, yPosition);
    yPosition += cellHeight;

    doc.setFont("helvetica", "normal");
    doc.text(`Tax (6%): RM${taxAmount.toFixed(2)}`, totalColumnX - 20, yPosition);
    yPosition += cellHeight;

    doc.setFont("helvetica", "bold");
    doc.text(`Grand Total: RM${grandTotal.toFixed(2)}`, totalColumnX - 20, yPosition);


    // Footer: Terms and Conditions
    doc.setFontSize(8);
    const termsStartY = 280; // Fixed Y position for terms and conditions at the bottom
    doc.text("Terms and Conditions", 10, termsStartY);
    doc.setFont("helvetica", "normal");
    const termsText = `1. Goods sold are not returnable.\n2. Payments must be made within the 30 Days.\n3. Contact us for any inquiries regarding the invoice.`;
    doc.text(doc.splitTextToSize(termsText, 190), 10, termsStartY + 5); // Wrap terms text

    // Thank you note
    doc.setFont("helvetica", "bold");
    yPosition += 20;
    doc.text("Thank you for your purchase!", 105, yPosition + 20, { align: "center" });

    // Save the PDF
    doc.save("invoice.pdf");
}


</script>

</body>
</html>
