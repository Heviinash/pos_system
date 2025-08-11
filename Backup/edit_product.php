<?php
// Session control
session_start();
$employee_id = $_SESSION['employeeid'] ?? null;
$role_name = $_SESSION['role_name'] ?? null;

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'pos';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the product barcode from the query parameter
$barcode = $_GET['barcode'] ?? null;

if ($barcode) {
    // Fetch the product details from the database
    $query = "SELECT * FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        echo "Product not found!";
        exit();
    }
} else {
    echo "No product selected!";
    exit();
}

// Handle the update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $product_type = $_POST['producttype'];
    $variant = $_POST['variant'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];
    $image_path = $product['productpic_url']; // Default image path

    // Check if a new image was uploaded
    if (!empty($_FILES['productpic_url']['name'])) {
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        $image_path = $target_dir . basename($_FILES['productpic_url']['name']);
        $imageFileType = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

        // Validate the image file (optional)
        $check = getimagesize($_FILES['productpic_url']['tmp_name']);
        if ($check === false) {
            echo "<div class='message error'>File is not an image.</div>";
            exit();
        }

        // Only allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            echo "<div class='message error'>Sorry, only JPG, JPEG, and PNG files are allowed.</div>";
            exit();
        }

        // Upload the image
        if (!move_uploaded_file($_FILES['productpic_url']['tmp_name'], $image_path)) {
            echo "<div class='message error'>Sorry, there was an error uploading your file.</div>";
            exit();
        }
    }

    // Update the product details in the database
    $update_query = "UPDATE productinventorytable SET Brand = ?, ProductType = ?, Variant = ?, Price = ?, Stock = ?, Status = ?, productpic_url = ? WHERE barcode = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssisss", $brand, $product_type, $variant, $price, $stock, $status, $image_path, $barcode);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<div class='message success'>Product updated successfully!</div>";
    } else {
        echo "<div class='message error'>Failed to update product!</div>";
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!-- Edit product form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7); /* Black with 70% opacity */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }


        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: white; /* Change text color to white */
            text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.8); /* Stroke effect */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            color: white;
        }

        input[type="text"], input[type="number"], select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            font-size: 14px;
            color: #333;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        input[type="file"] {
            margin-bottom: 15px;
        }

        button {
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #28a745;
            color: lightcyan;
        }

        .message.error {
            background-color: #dc3545;
            color: white;
        }

        img {
            max-width: 100px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="navbar.css">
<br>
<br>

<div class="container">
    <h2>Quick Edit Product</h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Brand:</label>
        <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>">

        <label>Product Type:</label>
        <input type="text" name="producttype" value="<?php echo htmlspecialchars($product['producttype']); ?>">

        <label>Variant:</label>
        <input type="text" name="variant" value="<?php echo htmlspecialchars($product['variant']); ?>">

        <label>Price:</label>
        <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01">

        <label>Stock:</label>
        <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>">

        <label>Status:</label>
        <select name="status">
            <option value="-" <?php echo ($product['status'] == '-') ? 'selected' : ''; ?>>-</option>
            <option value="Out of Stock" <?php echo ($product['status'] == 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
            <option value="Available" <?php echo ($product['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
            <option value="Discontinue" <?php echo ($product['status'] == 'Discontinue') ? 'selected' : ''; ?>>Discontinue</option>
        </select>

        <label> Current Product Image:</label>
        <?php if (!empty($product['productpic_url'])): ?>
            <img src="<?php echo $product['productpic_url']; ?>" alt="Product Image">
        <?php endif; ?>


        <label> New Product Image:</label>
        <input type="file" name="productpic_url">

        <button type="submit">Update Product</button>

        <br>
        <br>

        <a href="mainpage.php" style="display: inline-block; background-color: #1abc9c; color: white; padding: 12px 24px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 16px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; transition: background-color 0.3s ease;">
            Main Page
        </a>
    </form>
</div>

</body>
</html>
