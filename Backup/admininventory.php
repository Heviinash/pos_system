<?php


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

// Fetch categories for the dropdown filter
$category_query = "SELECT DISTINCT category_name FROM productsinventory";
$category_result = $conn->query($category_query);

// Initialize variables for search and category filter
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch products based on search or category filter
$query = "SELECT * FROM productsinventory WHERE 1";
if ($search_value != '') {
    $query .= " AND (product_id LIKE '%$search_value%' OR product_name LIKE '%$search_value%')";
}
if ($filter_category != '' && $filter_category != 'all') {
    $query .= " AND category_name = '$filter_category'";
}

$result = $conn->query($query);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Inventory</title>
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
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Search bar */
        .search-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            width: 50%;
            padding: 10px;
            border-radius: 5px;
            border: none;
        }

        .search-bar select {
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #fff;
            color: #000;
            width: 20%;
        }

        .search-bar button {
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #00f;
            color: #fff;
            cursor: pointer;
        }

        /* Card View */
        .product-card {
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 15px;
            margin: 15px;
            display: inline-block;
            width: calc(33% - 30px);
            box-sizing: border-box;
            transition: transform 0.3s;
            text-align: center;
        }

        .product-card img {
            width: 100%; /* Full width of the container */
            height: 200px; /* Set a fixed height */
            object-fit: contain; /* Ensures the full image is visible without cropping */
            border-radius: 8px; /* For rounded corners */
            background-color: #f4f4f4; /* Optional: A background color to fill any empty space */
        }

        .product-card h3 {
            margin: 10px 0;
        }

        .product-card p {
            margin: 5px 0;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card a {
            display: inline-block;
            margin-top: 10px;
            color: #00f;
            text-decoration: underline;
        }

        /* List View */
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
            width: 100px; /* Standard width */
            height: 100px; /* Standard height */
            object-fit: cover; /* Ensures the image fills the dimensions without distortion */
            border-radius: 8px; /* Optional for rounded corners */
        }

        a {
            color: #00f;
            text-decoration: underline;
        }

        /* Toggle Button */
        .toggle-button {
            background-color: #00f;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .toggle-button:hover {
            background-color: #0066cc;
        }

        /* Hide views initially */
        .product-list, .product-cards {
            display: none;
        }


        /* Side Panel */
        .side-panel {
            width: 0;
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            color: #fff;
        }

        .side-panel-content {
            padding: 20px;
        }

        .side-panel h2, .side-panel p {
            margin: 10px 0;
        }

        /* Close Button */
        .close-panel {
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 36px;
            cursor: pointer;
        }


        /* Side Panel */
        .side-panel {
            width: 0;
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95); /* Slightly darker for a sleek look */
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            color: #fff;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5); /* Add shadow for depth */
        }

        .side-panel-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center content horizontally */
        }

        .side-panel img {
            width: 120px; /* Set a fixed width */
            height: 150px; /* Set a fixed height */
            object-fit: contain; /* Ensures the full image is visible without cropping */
            border-radius: 8px; /* Rounded corners */
            margin-bottom: 15px; /* Spacing below image */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Add shadow to the image */
            background-color: #f4f4f4; /* Optional: Background to fill any empty space */
        }

        .side-panel h2 {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
            color: #00f; /* Add some contrast with a bright color */
        }

        .side-panel p {
            font-size: 16px;
            margin: 5px 0;
            text-align: center;
        }

        .side-panel #variant-select {
            margin: 15px 0;
            padding: 10px;
            width: 100%;
            background-color: #222;
            color: #fff;
            border: none;
            border-radius: 5px;
        }

        #variant-details {
            width: 100%;
            text-align: center;
        }

        #variant-details p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        #add-to-cart {
            padding: 10px 20px;
            background-color: #00f;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        #add-to-cart:hover {
            background-color: #0066cc;
        }

        .close-panel {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 28px;
            cursor: pointer;
            color: #fff;
        }

    </style>
</head>
<body>

    <div class="container">
        <h1>Inventory</h1>

        <!-- Search Bar and Filter -->
        <form method="GET" class="search-bar" style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search by Product ID or Name" value="<?php echo htmlspecialchars($search_value); ?>" 
                   style="padding: 8px 15px; font-size: 14px; margin-right: 10px; width: 40%;">

            <select name="category" style="padding: 8px 15px; font-size: 14px; margin-right: 10px; width: 20%;">
                <option value="all">All Categories</option>
                <?php while ($category_row = $category_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($category_row['category_name']); ?>" 
                    <?php echo $filter_category == $category_row['category_name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category_row['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Search Button -->
            <button type="submit" class="btn btn-primary" 
                    style="padding: 8px 15px; font-size: 14px; background-color: #007bff; color: white; border: none; cursor: pointer; margin-right: 5px;">
                Search
            </button>

            <!-- Reset Button -->
            <button type="button" class="btn btn-reset" onclick="resetFilters()" 
                    style="padding: 8px 15px; font-size: 14px; background-color: #f44336; color: white; border: none; cursor: pointer; margin-left: 5px;">
                Reset
            </button>

            <a href="view_cart.php" style="text-decoration: none;">
                <button type="button" class="btn" 
                        style="padding: 8px 15px; font-size: 14px; background-color: darkcyan; color: white; border: none; cursor: pointer; margin-left: 5px;">
                    View Cart
                </button>
            </a>


        </form>


        <script>
            function resetFilters() {
                // Get the search input and category filter elements
                const searchInput = document.querySelector('input[name="search"]');
                const categorySelect = document.querySelector('select[name="category"]');

                // Reset search input and category filter to default values
                searchInput.value = '';
                categorySelect.value = 'all';

                // Submit the form to refresh the page with default filter and search
                document.querySelector('.search-bar').submit();
            }
        </script>

        <button class="toggle-button" onclick="toggleView()">Switch to List View</button>

        <!-- Card View -->
        <div class="product-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php
                // Reset the result pointer to reuse it for displaying
                $result->data_seek(0);
                ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>Description: <?php echo htmlspecialchars($row['product_description']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($row['category_name']); ?></p>
                        <a href="product_edit.php?id=<?php echo $row['product_id']; ?>" 
                           style="display: inline-block; padding: 8px 15px; font-size: 14px; background-color: turquoise; color: white; text-align: center; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; margin-left: 5px;">
                            Edit Product
                        </a>
                        <br>
                        <br>
                        <button class="view-more" 
                                data-product-id="<?php echo $row['product_id']; ?>" 
                                style="display: inline-block; padding: 8px 15px; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 5px; text-align: center; cursor: pointer; margin-left: 5px; text-decoration: none;">
                            View More
                        </button>


                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>

        <!-- List View -->
        <div class="product-list">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Action</th>

                    </tr>
                    <?php
                    // Reset the result pointer to reuse it for displaying
                    $result->data_seek(0);
                    ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_description']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><a href="product_edit.php?id=<?php echo $row['product_id']; ?>" 
                           style="display: inline-block; padding: 8px 15px; font-size: 14px; background-color: turquoise; color: white; text-align: center; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; margin-left: 5px;">
                            Edit Product
                        </a>
                        <button class="view-more" 
                                data-product-id="<?php echo $row['product_id']; ?>" 
                                style="display: inline-block; padding: 8px 15px; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 5px; text-align: center; cursor: pointer; margin-left: 5px; text-decoration: none;">
                            View More
                        </button>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>

        <?php $conn->close(); ?>
    </div>

    <div id="side-panel" class="side-panel">
        <div class="side-panel-content">
            <span id="close-panel" class="close-panel">&times;</span>
            <img id="product-image" src="" alt="Product Image" style="width: 100%; border-radius: 8px; margin-bottom: 10px;">
            <h2 id="product-name"></h2>
            <p id="product-category"></p>
            <p id="product-description"></p>
            <select id="variant-select">
                <!-- Variants will be populated here -->
            </select>
            <div id="variant-details">
                <p id="variant-stock_quantity"></p>
                <p id="variant-price"></p>
                <p id="variant-sku"></p>
                
            </div>

            <input type="number" id="quantity-input" min="1" value="1" style="padding: 8px; width: 100%; border-radius: 5px; border: none; background-color: #222; color: #fff;">

            <!-- Update Add to Cart Button -->
            <button id="add-to-cart" onclick="addToCart()">Add to Cart</button>


        </div>
    </div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let isCardView = true;

        // Check for view preference in localStorage when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            // Check if a view preference (card or list) is saved
            const savedView = localStorage.getItem('viewPreference');
            if (savedView === 'list') {
                isCardView = false; // Set to list view if stored preference is 'list'
            } else {
                isCardView = true;  // Otherwise, default to card view
            }
            toggleView(); // Apply the view based on saved preference
        });

        // Function to toggle between list and card view
        function toggleView() {
            const cardView = document.querySelector('.product-cards');
            const listView = document.querySelector('.product-list');
            const toggleButton = document.querySelector('.toggle-button');

            if (isCardView) {
                cardView.style.display = 'block';   // Show card view
                listView.style.display = 'none';    // Hide list view
                toggleButton.innerHTML = 'Switch to List View'; // Change button text
                localStorage.setItem('viewPreference', 'card');  // Save card view preference
            } else {
                cardView.style.display = 'none';    // Hide card view
                listView.style.display = 'block';   // Show list view
                toggleButton.innerHTML = 'Switch to Card View'; // Change button text
                localStorage.setItem('viewPreference', 'list');  // Save list view preference
            }

            isCardView = !isCardView; // Toggle the state


        }


        document.querySelectorAll('.view-more').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        
        // Fetch product details and variants using AJAX
        fetchProductDetails(productId);
    });
});

    function fetchProductDetails(productId) {
    fetch(`get_product_details.php?product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }

        // Update side panel with product details
        document.getElementById('product-name').innerText = data.product_name;
        document.getElementById('product-category').innerText = `Category: ${data.category_name}`;
        document.getElementById('product-image').src = data.image_url;

        // Populate variants dropdown
        const variantSelect = document.getElementById('variant-select');
        variantSelect.innerHTML = ''; // Clear existing options
        data.variants.forEach(variant => {
            const option = document.createElement('option');
            option.value = variant.variant_name; // Use variant name or another identifier
            option.textContent = variant.variant_name;
            variantSelect.appendChild(option);
        });

        // Handle variant change
        variantSelect.addEventListener('change', function() {
            const selectedVariant = data.variants.find(v => v.variant_name === this.value);
            if (selectedVariant) {
                document.getElementById('variant-price').innerText = `Price: RM${selectedVariant.price}`;
                document.getElementById('variant-stock_quantity').innerText = `Stock: ${selectedVariant.stock_quantity}`;
                document.getElementById('variant-sku').innerText = `Stock: ${selectedVariant.sku}`;
            } else {
                document.getElementById('variant-price').innerText = 'Price: N/A';
                document.getElementById('variant-stock_quantity').innerText = 'Stock: N/A';
            }
        });

        // Show side panel
        openSidePanel();
    })
    .catch(error => console.error('Error:', error));
}




    

    function openSidePanel() {
        document.getElementById('side-panel').style.width = '400px';  // Adjust the width as needed
    }

    document.getElementById('close-panel').addEventListener('click', function() {
        document.getElementById('side-panel').style.width = '0';
    });




    // Add to cart Feature coding





function fetchProductDetails(productId) {
    fetch(`get_product_details.php?product_id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }

        // Update side panel with product details
        document.getElementById('product-name').innerText = data.product_name;
        document.getElementById('product-category').innerText = `Category: ${data.category_name}`;
        document.getElementById('product-image').src = data.image_url;

        // Populate variants dropdown
        const variantSelect = document.getElementById('variant-select');
        variantSelect.innerHTML = ''; // Clear existing options
        data.variants.forEach(variant => {
            const option = document.createElement('option');
            option.value = variant.variant_name; // Use variant name or another identifier
            option.textContent = variant.variant_name;
            variantSelect.appendChild(option);
        });

        // Handle variant change
        variantSelect.addEventListener('change', function() {
            const selectedVariant = data.variants.find(v => v.variant_name === this.value);
            if (selectedVariant) {
                document.getElementById('variant-price').innerText = `Price: RM${selectedVariant.price}`;
                document.getElementById('variant-stock_quantity').innerText = `Stock: ${selectedVariant.stock_quantity}`;
                document.getElementById('variant-sku').innerText = `SKU: ${selectedVariant.sku}`;
                // Save the selected variant details
                document.getElementById('selected-sku').value = selectedVariant.sku; // Set SKU in hidden input
            } else {
                document.getElementById('variant-price').innerText = 'Price: N/A';
                document.getElementById('variant-stock_quantity').innerText = 'Stock: N/A';
                document.getElementById('variant-sku').innerText = 'SKU: N/A';
            }
        });

        // Show side panel
        openSidePanel();
    })
    .catch(error => console.error('Error:', error));
}


function addToCart() {
    const sku = document.querySelector('.view-more').getAttribute('data-sku');
    const variantName = document.getElementById('variant-select').value;
    const quantity = document.getElementById('quantity-input').value;

    console.log(`SKU: ${sku}, Variant Name: ${variantName}, Quantity: ${quantity}`);

    if (variantName && quantity) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `sku=${encodeURIComponent(sku)}&variant_name=${encodeURIComponent(variantName)}&quantity=${encodeURIComponent(quantity)}`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            if (data === 'success') {
                alert('Item added to cart successfully!');
                document.getElementById('side-panel').style.width = '0'; // Close panel
            } else {
                alert('Failed to add item to cart.');
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        alert('Please select a variant and quantity.');
    }
}













    </script>


</body>
</html>
