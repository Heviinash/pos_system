<?php

session_start();

$conn = new mysqli('localhost', 'root', '', 'pos');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding services
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    // Insert service into services table
    $sql = "INSERT INTO services (service_name, description, price, duration) 
            VALUES ('$service_name', '$description', '$price', '$duration')";

    if ($conn->query($sql) === TRUE) {
        $service_id = $conn->insert_id; // Get the last inserted service ID

        // Handle inventory items selection
        if (!empty($_POST['item_id']) && !empty($_POST['quantity_needed'])) {
            $item_ids = $_POST['item_id'];
            $quantities_needed = $_POST['quantity_needed'];

            foreach ($item_ids as $index => $item_id) {
                $quantity_needed = $quantities_needed[$index];
                $conn->query("INSERT INTO service_items (service_id, item_id, quantity_needed) 
                               VALUES ('$service_id', '$item_id', '$quantity_needed')");
            }
        }

        echo '<div style="color: #17a2b8; font-weight: bold; font-size: 16px; background-color: #e3f7fc; padding: 10px; border: 1px solid #17a2b8; border-radius: 5px; text-align: center; width: auto; display: inline-block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;" id="successMessage">
        New service added successfully with associated inventory items
      </div>
      <script>
        setTimeout(function() {
          document.getElementById("successMessage").style.display = "none";
        }, 4000); // 4000 milliseconds = 4 seconds
      </script>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle deletion of services
if (isset($_GET['delete'])) {
    $service_id = $_GET['delete'];

    $conn->query("DELETE FROM service_items WHERE service_id = $service_id");
    
    // Now, delete the service from the services table
    $conn->query("DELETE FROM services WHERE service_id = $service_id");

}

// Fetch all services
$result = $conn->query("SELECT * FROM services");

// Fetch all inventory items for the dropdown
$inventory_result = $conn->query("SELECT * FROM serviceinventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Management</title>
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

        .form-container, .table-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        h1, h2 {
            text-align: center;
            color: #00bfff;
        }

        /* Form styling */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        form input, form textarea, form select {

            padding: 10px;              /* Adjust padding for inside spacing */
            margin: 10px 0;            /* Add margin for top and bottom spacing */
            border-radius: 4px;
            border: none;
            width: calc(100% - 20px);  /* Adjust width to account for padding */
        }

        button {
            background-color: #00bfff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 40%;
        }

        button:hover {
            background-color: #008fb3;
        }

        /* Table styling */
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
            background-color: #00bfff;
        }

        a {
            color: #00bfff;
            text-decoration: none;
        }

        a:hover {
            color: #008fb3;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    
    <div class="container">
        <div class="form-container">
            <h1>Assign Service & Item</h1>
            <form method="POST">
                <input type="text" name="service_name" placeholder="Service Name" required>
                <textarea name="description" placeholder="Description" required></textarea>
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="number" name="duration" placeholder="Duration (minutes)" required>

                <h3>Select Items</h3>
                <div id="inventory-items">
                    <select name="item_id[]" required>
                        <?php while ($inventory_row = $inventory_result->fetch_assoc()) { ?>
                            <option value="<?php echo $inventory_row['item_id']; ?>">
                                <?php echo $inventory_row['item_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                    <input type="number" name="quantity_needed[]" placeholder="Quantity Needed" required>
                    
                </div>

                <div style="text-align: center;">

                    <button type="button" onclick="addItem()">Add More Items</button>

                </div>

                <br>

                <div style="text-align: center;">
                    <button type="submit" name="add_service" style="background-color: goldenrod; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 5px;">
                        Submit Service
                    </button>
                </div>

                

            </form>
        </div>

        <div class="table-container">
            <h2>Services</h2>
            <table>
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['service_name']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td><?php echo $row['duration']; ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['service_id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function addItem() {
            const itemSelect = document.createElement('select');
            itemSelect.name = 'item_id[]';
            itemSelect.required = true;
            itemSelect.innerHTML = `<?php
                $inventory_result->data_seek(0); // Reset the pointer
                while ($inventory_row = $inventory_result->fetch_assoc()) {
                    echo '<option value="' . $inventory_row['item_id'] . '">' . $inventory_row['item_name'] . '</option>';
                }
            ?>`;
            
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = 'quantity_needed[]';
            quantityInput.placeholder = 'Quantity Needed';
            quantityInput.required = true;

            const inventoryItemsDiv = document.getElementById('inventory-items');
            inventoryItemsDiv.appendChild(itemSelect);
            inventoryItemsDiv.appendChild(quantityInput);
        }
    </script>

    <?php $conn->close(); ?>
</body>
</html>
