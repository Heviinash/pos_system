<?php
// USER CONTROL CODE

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
    // User is not logged in, redirect to login page
    header("Location: index.php");
    exit();
}

// Check if the user role has permission to access the current page
$query = "SELECT * FROM roles_permission WHERE role_name = ? AND permission_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $role_name, $current_page);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // User doesn't have permission, redirect to unauthorized page
    header("Location: unauthorized.php");  // Replace with your unauthorized access page
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();

// Database connection
$host = 'localhost';
$user = 'root';     // your DB username
$password = '';     // your DB password
$dbname = 'pos';    // your database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST['brand'];
    $product_type = $_POST['product_type'];
    $variant = $_POST['variant'];
    $barcode = $_POST['barcode'];
    $sku = $_POST['sku'];
    $price = $_POST['price'];
    $wholesaleprice = $_POST['wholesaleprice'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    // Handle file upload
    $productpic_url = '';
    if (isset($_FILES['productpic_url']) && $_FILES['productpic_url']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['productpic_url']['name']);
        $target_file = $upload_dir . $file_name;
        $productpic_url = $target_file; // Assign the image URL

        if (move_uploaded_file($_FILES['productpic_url']['tmp_name'], $target_file)) {
            echo '<div style="color: #17a2b8; font-weight: bold; font-size: 16px; background-color: #e3f7fc; padding: 10px; border: 1px solid #17a2b8; border-radius: 5px; text-align: center;">
                File Uploaded Succesfully
      </div>';
        } else {
            echo "Error uploading file.";
        }
    }

    // Insert product into the inventory table
    $insert_product_query = "INSERT INTO productinventorytable (productpic_url, Brand, ProductType, Variant, SKU, Barcode, Price, Wholesaleprice, Stock, Status, product_added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_product_query);
    $stmt->bind_param("ssssssddiss", $productpic_url, $brand, $product_type, $variant, $sku, $barcode, $price, $wholesaleprice, $stock, $status, $employee_id);

    if ($stmt->execute()) {
        echo '<div style="color: #17a2b8; font-weight: bold; font-size: 16px; background-color: #e3f7fc; padding: 10px; border: 1px solid #17a2b8; border-radius: 5px; text-align: center;">
        Product added successfully!
      </div>';


    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();





}

// Fetch existing product types for the dropdown (if applicable)
$query = "SELECT DISTINCT ProductType FROM productinventorytable";
$product_types_result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Product</title>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 100%;
            max-width: 1200px;
            transition: margin-left 0.3s ease-in-out;
        }

        .form-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            transition: margin-left 0.3s;
            box-sizing: border-box;
        }

        .form-container label {
            display: block;
            margin-bottom: 10px;
        }

        .form-container input, 
        .form-container select, 
        .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
            box-sizing: border-box;
        }

        .form-container button {
            background-color: #444;
            color: white;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #555;
        }

    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>

    <div class="container">
        <!-- Main Content -->
        <div class="form-container">
            <h2>Add New Product</h2>
            <form method="post" enctype="multipart/form-data">
                
                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" required>

                <label for="product_type">Product Type:</label>
                <input type="text" id="product_type" name="product_type" required>
                    

                <label for="variant">Variant:</label>
                <input type="text" id="variant" name="variant" required>

                <label for="barcode">Barcode:</label>
                <input type="text" id="barcode" name="barcode" required>

                <label for="sku">SKU (Compulsory):</label>
                <input type="text" id="sku" name="sku">

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>


                <label for="wholesaleprice">Wholesale Price:</label>
                <input type="number" step="0.01" id="wholesaleprice" name="wholesaleprice" required>


                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" required>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                    <option value="Discontinued">Discontinued</option>
                </select>

                <label for="productpic_url">Product Image:</label>
                <input type="file" id="productpic_url" name="productpic_url" required>

                <button type="submit">Add Product</button>
            </form>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>
