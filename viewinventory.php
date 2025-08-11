
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
    // User is not logged in, redirect to login page
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
    // User doesn't have permission, redirect to unauthorized page
    header("Location: unauthorized.php");  // Replace with your unauthorized access page
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();




// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'pos';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct values for filters
$brands_query = "SELECT DISTINCT Brand FROM productinventorytable";
$brands_result = $conn->query($brands_query);

$product_types_query = "SELECT DISTINCT ProductType FROM productinventorytable";
$product_types_result = $conn->query($product_types_query);

// Initialize variables for search filters
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
$filter_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$filter_producttype = isset($_GET['producttype']) ? $_GET['producttype'] : '';
$filter_variant = isset($_GET['variant']) ? $_GET['variant'] : '';
$filter_sku = isset($_GET['sku']) ? $_GET['sku'] : '';
$filter_barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'card'; // Default to 'card'

// Fetch products based on search or filters
$query = "SELECT * FROM productinventorytable WHERE 1";
if ($search_value != '') {
    $query .= " AND (Brand LIKE '%$search_value%' OR ProductType LIKE '%$search_value%' OR Variant LIKE '%$search_value%' OR SKU LIKE '%$search_value%' OR Barcode LIKE '%$search_value%')";
}
if ($filter_brand != '' && $filter_brand != 'all') {
    $query .= " AND Brand = '$filter_brand'";
}
if ($filter_producttype != '' && $filter_producttype != 'all') {
    $query .= " AND ProductType = '$filter_producttype'";
}
if ($filter_variant != '' && $filter_variant != 'all') {
    $query .= " AND Variant = '$filter_variant'";
}
if ($filter_sku != '' && $filter_sku != 'all') {
    $query .= " AND SKU = '$filter_sku'";
}
if ($filter_barcode != '' && $filter_barcode != 'all') {
    $query .= " AND Barcode = '$filter_barcode'";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory View</title>
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
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }

        .search-bar input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: none;
            width: 300px;
        }

        .search-bar button {
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            background-color: #00f;
            color: #fff;
            cursor: pointer;
        }

        .search-bar select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #007bff; /* Border color */
            width: 200px; /* Width for dropdowns */
            margin-right: 10px; /* Space between dropdown and search input */
            font-size: 16px;
            color: #333; /* Text color */
            background-color: #fff; /* Background color */
            transition: border-color 0.3s;
        }

        .search-bar select:hover, 
        .search-bar select:focus {
            border-color: #0056b3; /* Darker border on hover/focus */
            outline: none; /* Remove default outline */
        }

        .view-toggle {
            margin-bottom: 20px;
            text-align: center;
        }

        .view-toggle button {
            padding: 10px 15px;
            margin-right: 10px;
            cursor: pointer;
            border-radius: 5px;
            background-color: #00f;
            color: #fff;
            border: none;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 15px;
            width: calc(33% - 30px);
            box-sizing: border-box;
            transition: transform 0.3s;
            text-align: center;
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 8px;
            background-color: #f4f4f4;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .list-container {
            display: none;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #fff;
        }

        th {
            background-color: rgba(0, 0, 0, 0.9);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        a {
            color: #00f;
            text-decoration: underline;
        }

        .view-more, .edit-product {
            display: inline-block;
            padding: 8px 15px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            margin-left: 5px;
        }

        .view-more {
            background-color: #007bff;
        }

        .edit-product {
            background-color: turquoise;
            display: inline-block;
            padding: 8px 15px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>



<div class="container">
    
    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <h1>Inventory View</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by brand, type, or variant" value="<?php echo htmlspecialchars($search_value); ?>">

            <!-- Brand Dropdown -->
            <select name="brand">
                <option value="all">All Brands</option>
                <?php if ($brands_result->num_rows > 0) {
                    while ($brand_row = $brands_result->fetch_assoc()) {
                        $selected = ($filter_brand == $brand_row['Brand']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($brand_row['Brand']) . '" ' . $selected . '>' . htmlspecialchars($brand_row['Brand']) . '</option>';
                    }
                } ?>
            </select>

            <!-- Product Type Dropdown -->
            <select name="producttype">
                <option value="all">All Product Types</option>
                <?php if ($product_types_result->num_rows > 0) {
                    while ($type_row = $product_types_result->fetch_assoc()) {
                        $selected = ($filter_producttype == $type_row['ProductType']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($type_row['ProductType']) . '" ' . $selected . '>' . htmlspecialchars($type_row['ProductType']) . '</option>';
                    }
                } ?>
            </select>

            <!-- Hidden input to track view mode -->
            <input type="hidden" name="view" value="<?php echo htmlspecialchars($view_mode); ?>">

            <button type="submit">Search</button>


        </form>

    </div>

    <div class="view-toggle">
        <button id="toggleViewBtn">Toggle View</button>
    </div>

    

    <div id="content" class="card-container" style="<?php echo $view_mode === 'card' ? 'display: flex;' : 'display: none;'; ?>">
        <?php
        // Card View
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<img src="' . htmlspecialchars($row['productpic_url']) . '" alt="Product Image">';
                echo '<h3>' . htmlspecialchars($row['brand']) . '</h3>';
                echo '<p>Type: ' . htmlspecialchars($row['producttype']) . '</p>';
                echo '<p>Variant: ' . htmlspecialchars($row['variant']) . '</p>';
                echo '<p>Price: RM' . htmlspecialchars($row['price']) . '</p>';
                echo '<p>Wholesale: RM' . htmlspecialchars($row['wholesaleprice']) . '</p>';
                echo '<p>Stock: ' . htmlspecialchars($row['stock']) . '</p>';
                echo '<p>Status: ' . htmlspecialchars($row['status']) . '</p>';
                echo '<a href="edit_product.php?barcode=' . htmlspecialchars($row['barcode']) . '" class="edit-product">Edit Product</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No products found.</p>';
        }
        ?>
    </div>

    <div id="listContent" class="list-container" style="<?php echo $view_mode === 'list' ? 'display: block;' : 'display: none;'; ?>">
        <table>
            <tr>
                <th>Image</th>
                <th>Brand</th>
                <th>Product Type</th>
                <th>Variant</th>
                <th>Price</th>
                <th>Wholesale Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Product Edit</th>
            </tr>
            <?php
            // Reset the result pointer to reuse it for displaying
            $result->data_seek(0);
            ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($row['productpic_url']); ?>" alt="<?php echo htmlspecialchars($row['brand']); ?>"></td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                    <td><?php echo htmlspecialchars($row['producttype']); ?></td>
                    <td><?php echo htmlspecialchars($row['variant']); ?></td>
                    <td>RM<?php echo htmlspecialchars($row['price']); ?></td>
                    <td>RM<?php echo htmlspecialchars($row['wholesaleprice']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><a href="edit_product.php?barcode=<?php echo urlencode($row['barcode']); ?>" class="edit-product">Edit Product</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script>
    const toggleViewBtn = document.getElementById('toggleViewBtn');
    const content = document.getElementById('content');
    const listContent = document.getElementById('listContent');

    let isCardView = '<?php echo $view_mode; ?>' === 'card';

    toggleViewBtn.addEventListener('click', () => {
        isCardView = !isCardView;
        content.style.display = isCardView ? 'flex' : 'none';
        listContent.style.display = isCardView ? 'none' : 'block';

        // Update the hidden input for view mode
        const viewInput = document.querySelector('input[name="view"]');
        viewInput.value = isCardView ? 'card' : 'list';
    });
</script>

</body>
</html>
