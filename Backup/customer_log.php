<?php

session_start();

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying it
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $servername = "localhost";
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "pos"; // Database name

    // Create the connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the form data
    $name = $_POST['name'];
    $icnumber = $_POST['icnumber'];
    $contact_info = $_POST['contact_info'];
    $problem_desc = $_POST['problem_desc'];
    $followupdate = $_POST['followupdate'];

    // Handle image upload
    $image_paths = []; // Array to store paths of uploaded images
    if (isset($_FILES['images'])) {
        $total_files = count($_FILES['images']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $target_dir = "uploads/"; // Directory where images will be stored
            $target_file = $target_dir . basename($_FILES['images']['name'][$i]);
            $upload_ok = 1;

            // Check file type
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if (!in_array($image_file_type, ['jpg', 'png', 'jpeg', 'gif'])) {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $upload_ok = 0;
            }

            // Check if $upload_ok is set to 0 by an error
            if ($upload_ok == 1) {
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    $image_paths[] = $target_file; // Store the uploaded image path
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }
    }

    // Convert image paths array to string for database insertion
    $image_paths_string = implode(',', $image_paths);

    // Insert data into customer_logs table
    $stmt_log = $conn->prepare("INSERT INTO testcustomer_logs (name, icnumber, contact_info, problem_desc, followupdate, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_log->bind_param("ssssss", $name, $icnumber, $contact_info, $problem_desc, $followupdate, $image_paths_string);
    
    if ($stmt_log->execute()) {
        // Get the last inserted log ID
        $log_id = $stmt_log->insert_id;

        // Insert products
        // Insert products
        if (isset($_POST['product_needed']) && is_array($_POST['product_needed'])) {
            foreach ($_POST['product_needed'] as $index => $product_needed) {
                $quantity = isset($_POST['quantity'][$index]) ? $_POST['quantity'][$index] : 1; // Default to 1 if not set
                $price = isset($_POST['price'][$index]) ? $_POST['price'][$index] : 0.00; // Default to 0 if not set

                if (!empty($product_needed)) {
                    $stmt_product = $conn->prepare("INSERT INTO testcustomer_products (log_id, product_needed, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt_product->bind_param("isid", $log_id, $product_needed, $quantity, $price); // 'd' for double (decimal)
                    $stmt_product->execute();
                }
            }
        }


        // Insert services
        if (isset($_POST['service_needed']) && is_array($_POST['service_needed'])) {
            foreach ($_POST['service_needed'] as $index => $service_needed) {
                $price = isset($_POST['service_price'][$index]) ? $_POST['service_price'][$index] : 0.00; // Default to 0 if not set

                if (!empty($service_needed)) {
                    $stmt_service = $conn->prepare("INSERT INTO testcustomer_services (log_id, service_needed, price) VALUES (?, ?, ?)");
                    $stmt_service->bind_param("isd", $log_id, $service_needed, $price); // 'd' for double (decimal)
                    $stmt_service->execute();
                }
            }
        }


        echo "Customer log created successfully!";
        header("Location: generate_quotation.php?log_id=$log_id");
        exit; // Good practice to prevent further script execution
    } else {
        echo "Error: " . $stmt_log->error; // Correct placement of error handling
    }

    // Close the statement and connection
    $stmt_log->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Log Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff; /* White text for better contrast on dark background */
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 650px; /* Set a maximum width for the container */
            margin: 30px auto; /* Add margin to the top and bottom to separate from navbar */
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent white background */
            backdrop-filter: blur(10px); /* Optional: adds a blur effect behind the container */
            padding: 30px; /* Increased padding for more internal space */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5); /* Darker shadow for depth */
            border-radius: 8px; /* Rounded corners for a softer look */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        fieldset {
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Lighter border for fieldset */
            padding: 10px; /* Add padding to fieldset */
        }

        input, textarea, button {
            margin: 15px 0; /* Increased margin for more spacing between elements */
            padding: 12px; /* Increased padding for a more comfortable size */
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.5); /* Light border for inputs */
            border-radius: 4px; /* Rounded corners for inputs */
            background-color: rgba(255, 255, 255, 0.2); /* Slightly transparent input background */
            color: #fff; /* White text in inputs */
            font-size: 16px; /* Increased font size for better readability */
        }

        button {
            background-color: #28a745; /* Green button */
            color: white; /* White text on button */
            border: none;
            cursor: pointer;
            transition: background-color 0.3s; /* Smooth transition for hover effect */
            height: 45px; /* Set a standard height for buttons */
        }

        button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .suggestions {
            border: 1px solid rgba(255, 255, 255, 0.3); /* Light border for suggestions box */
            background-color: rgba(255, 255, 255, 0.1); /* Transparent background for suggestions */
            max-height: 150px;
            overflow-y: auto;
        }

        .suggestions div {
            padding: 10px;
            cursor: pointer;
        }

        .suggestions div:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Slightly lighter background on hover */
        }
    </style>

</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>


<div class="container">
    <h1>Customer Log</h1>

    <form method="POST" action="customer_log.php" enctype="multipart/form-data">
        <!-- Customer Details -->
        <fieldset>
            <legend>Customer Details</legend>
            <label for="name">Customer Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="icnumber">Customer IC:</label>
            <input type="text" id="icnumber" name="icnumber">

            <label for="contact_info">Contact Info:</label>
            <input type="text" id="contact_info" name="contact_info" required>
        </fieldset>

        <!-- Problem Description -->
        <fieldset>
            <legend>Problem Description</legend>
            <label for="problem_desc">Describe the problem:</label>
            <textarea id="problem_desc" name="problem_desc" rows="4" required></textarea>
        </fieldset>

        <!-- Products/Services Needed -->
        <fieldset>
            <legend>Products/Services Needed</legend>
            <div id="product-list"></div>
            <button type="button" id="add-service" style="width: 100px; padding: 8px; font-size: 14px;">Add Service</button>
            <button type="button" id="add-product" style="width: 100px; padding: 8px; font-size: 14px;">Add Product</button>
        </fieldset>

        <!-- Follow-up Date -->
        <fieldset>
            <legend>Follow-up</legend>
            <label for="followupdate">Follow-up Date:</label>
            <input type="date" id="followupdate" name="followupdate">
        </fieldset>

        <!-- Image Upload -->
        <fieldset>
            <legend>Upload Images</legend>
            <input type="file" name="images[]" multiple required>
        </fieldset>

        <!-- Submit Button -->
        <button type="submit"style="width: 100px; padding: 8px; font-size: 14px; align-self: center;">Save Log</button>
    </form>
</div>

<script>


document.getElementById('add-service').addEventListener('click', function () {
    var productList = document.getElementById('product-list');
    var newService = document.createElement('div');
    newService.classList.add('product-entry');
    newService.innerHTML = `
        <label for="service_needed">Service Needed:</label>
        <input type="text" name="service_needed[]" class="service-input">
        <div style="display: flex; gap: 10px;">
            <button type="button" class="fetch-services" style="background-color: #4CAF50; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; width: 120px;">Show Services</button>
            <button type="button" class="close-services" style="background-color: #f44336; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; width: 100px;">Close</button>
        </div>
        <label for="service_price">Price:</label>
        <input type="text" name="service_price[]" class="service-price-input" readonly>
        <div class="suggestions service-suggestions"></div>
    `;
    productList.appendChild(newService);

    // Attach event for fetching suggestions when typing
    newService.querySelector('.service-input').addEventListener('input', function() {
        let input = this.value;
        let suggestionsContainer = newService.querySelector('.service-suggestions');

        if (input.length > 1) {
            fetch('fetch_services.php?q=' + input)
                .then(response => response.json())
                .then(data => {
                    suggestionsContainer.innerHTML = ''; // Clear previous suggestions
                    data.forEach(service => {
                        let div = document.createElement('div');
                        div.textContent = `${service.service_name} (Price: $${service.price})`;
                        div.addEventListener('click', () => {
                            newService.querySelector('.service-input').value = service.service_name;
                            newService.querySelector('.service-price-input').value = service.price;
                            suggestionsContainer.innerHTML = ''; // Clear suggestions after selection
                        });
                        suggestionsContainer.appendChild(div);
                    });
                });
        } else {
            suggestionsContainer.innerHTML = ''; // Clear suggestions if input is too short
        }
    });

    // Attach event for "Show Services" button
    newService.querySelector('.fetch-services').addEventListener('click', function() {
        let suggestionsContainer = newService.querySelector('.service-suggestions');
        let closeBtn = newService.querySelector('.close-services');  // Get the close button

        fetch('fetch_services.php') // Fetch all services
            .then(response => response.json())
            .then(data => {
                suggestionsContainer.innerHTML = ''; // Clear previous suggestions
                closeBtn.style.display = 'inline';  // Show the close button when services are shown

                data.forEach(service => {
                    let div = document.createElement('div');
                    div.textContent = `${service.service_name} (Price: RM${service.price})`;
                    div.addEventListener('click', () => {
                        newService.querySelector('.service-input').value = service.service_name;
                        newService.querySelector('.service-price-input').value = service.price;
                        suggestionsContainer.innerHTML = ''; // Clear suggestions after selection
                    });
                    suggestionsContainer.appendChild(div);
                });
            })
            .catch(error => console.error('Error fetching services:', error));
    });

    // Close the service suggestions list when "Close" button is clicked
    newService.querySelector('.close-services').addEventListener('click', function() {
        let suggestionsContainer = newService.querySelector('.service-suggestions');
        suggestionsContainer.innerHTML = '';  // Clear the suggestions
        this.style.display = 'none';  // Hide the close button again
    });

});




// Function to add a new product
// Function to add a new product


document.getElementById('add-product').addEventListener('click', function () {
    var productList = document.getElementById('product-list');
    var newProduct = document.createElement('div');
    newProduct.classList.add('product-entry');
    newProduct.innerHTML = `
        <label for="product_needed">Product Needed:</label>
        <input type="text" name="product_needed[]" class="product-input">
        <div style="display: flex; gap: 10px;">
            <button type="button" class="fetch-products" style="background-color: #4CAF50; color: white; padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; width: 150px;">Show Products</button>
            <button type="button" class="close-products" style="background-color: #f44336; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; width: 100px;">Close</button>
        </div>
        <label for="price">Price:</label>
        <input type="text" name="price[]" class="price-input" readonly>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity[]" min="1" value="1" required>
        <div class="suggestions" id="product-suggestions"></div>
    `;
    productList.appendChild(newProduct);

    // Attach event for fetching products when "Show Products" button is clicked
    newProduct.querySelector('.fetch-products').addEventListener('click', function() {
        let suggestionsContainer = newProduct.querySelector('.suggestions');
        let closeBtn = newProduct.querySelector('.close-products');  // Get the close button

        // Fetch all products (no search query, just fetch everything)
        fetch('fetch_products.php')
            .then(response => response.json())
            .then(data => {
                suggestionsContainer.innerHTML = '';  // Clear any previous suggestions
                closeBtn.style.display = 'inline';  // Show the close button when products are shown

                // Display each product in the suggestions box
                data.forEach(product => {
                    let div = document.createElement('div');
                    div.textContent = `${product.brand} - ${product.producttype} - ${product.variant} (Price: RM${product.price})`;

                    // When clicked, set the value in the input field and display price
                    div.addEventListener('click', () => {
                        newProduct.querySelector('.product-input').value = `${product.brand} - ${product.producttype} - ${product.variant}`;
                        newProduct.querySelector('.price-input').value = product.price;  // Set price in the price field
                        suggestionsContainer.innerHTML = '';  // Clear suggestions after selection
                    });
                    
                    suggestionsContainer.appendChild(div);
                });
            })
            .catch(error => console.error('Error fetching products:', error));
    });

    // Close the product suggestions list when "Close" button is clicked
    newProduct.querySelector('.close-products').addEventListener('click', function() {
        let suggestionsContainer = newProduct.querySelector('.suggestions');
        suggestionsContainer.innerHTML = '';  // Clear the suggestions
        this.style.display = 'none';  // Hide the close button again
    });
});




</script>

</body>
</html>
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare query based on input
$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT brand, producttype, variant FROM productinventorytable WHERE 
         CONCAT(brand, ' ', producttype, ' ', variant) LIKE '%$q%'";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
//echo json_encode($products);
$conn->close();
?>
