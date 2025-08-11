<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pos');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX requests for product details
if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    $sql = "SELECT brand, producttype, variant, price FROM productinventorytable WHERE barcode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle AJAX requests for customer names
if (isset($_GET['customer_names'])) {
    $sql = "SELECT icnumber, name FROM customerregistration";
    $result = $conn->query($sql);
    $customers = [];

    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    echo json_encode($customers);
    $conn->close();
    exit;
}

// Handle AJAX requests for customer details
if (isset($_GET['icnumber'])) {
    $icnumber = $_GET['icnumber'];
    $sql = "SELECT icnumber, email, phone FROM customerregistration WHERE icnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
        echo json_encode($details);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle AJAX requests for membership details
if (isset($_GET['membership_details'])) {
    $icnumber = $_GET['membership_details'];
    $sql = "SELECT membership_id, membership_expirydate, membership_points, membership_type FROM customermemberships WHERE icnumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $membership = $result->fetch_assoc();
        echo json_encode($membership);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Sales Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            display: flex;
        }

        .right-panel {
            flex: 1;
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        #membership-details {
            display: none;
        }
    </style>
    <script src="https://cdn.rawgit.com/zxing/zxing/master/javascript/examples/legacy/zxing.js"></script>
</head>
<body>
    <div class="right-panel">
        <h2>Customer Details</h2>
        <label for="membership-checkbox">Member:</label>
        <input type="checkbox" id="membership-checkbox">

        <label for="icnumber">IC Number:</label>
        <input type="text" id="icnumber" name="icnumber">

        <label for="customer-name">Customer Name:</label>
        <select id="customer-name" name="customer-name">
            <!-- Customer names will be populated here -->
        </select>

        <label for="customer-number">Customer Number:</label>
        <input type="text" id="customer-number" name="customer-number">

        <label for="customer-email">Customer Email:</label>
        <input type="email" id="customer-email" name="customer-email">

        <div id="membership-details">
            <h3>Membership Details</h3>
            <input type="text" id="membership-info" disabled>
        </div>

        <label for="voucher">Voucher:</label>
        <input type="text" id="voucher" name="voucher">

        <h3>Totals</h3>
        <label for="subtotal">Subtotal:</label>
        <input type="text" id="subtotal" name="subtotal" disabled>

        <label for="discount">Discount (%):</label>
        <select id="discount">
            <option value="0">0</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="30">30</option>
        </select>

        <label for="grand-total">Grand Total:</label>
        <input type="text" id="grand-total" name="grand-total" disabled>
    </div>

    <script src="quagga.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const membershipCheckbox = document.getElementById('membership-checkbox');
            const customerNameSelect = document.getElementById('customer-name');
            const membershipDetailsDiv = document.getElementById('membership-details');
            const icNumberInput = document.getElementById('icnumber');

            // Start in non-member mode
            membershipDetailsDiv.style.display = 'none';
            customerNameSelect.style.display = 'none';

            // Create a text input for the customer name (for non-members)
            let customerNameInput = document.createElement('input');
            customerNameInput.type = 'text';
            customerNameInput.id = 'customer-name-input';
            customerNameInput.name = 'customer-name';
            customerNameInput.placeholder = 'Enter customer name';
            const label = document.querySelector('label[for="customer-name"]');
            label.after(customerNameInput);

            // Populate customer names from the database on checkbox change
            membershipCheckbox.addEventListener('change', () => {
                if (membershipCheckbox.checked) {
                    // Show membership-related fields
                    membershipDetailsDiv.style.display = 'block';
                    customerNameSelect.style.display = 'block';
                    customerNameInput.style.display = 'none'; // Hide text input for customer name

                    // Clear previous customer name options
                    customerNameSelect.innerHTML = '';

                    // Fetch customer names for members
                    fetch('sales_interface.php?customer_names=1')
                        .then(response => response.json())
                        .then(customers => {
                            customers.forEach(customer => {
                                const option = document.createElement('option');
                                option.value = customer.icnumber;
                                option.textContent = customer.name;
                                customerNameSelect.appendChild(option);
                            });
                        });
                } else {
                    // Show non-member text input for customer name
                    membershipDetailsDiv.style.display = 'none';
                    customerNameSelect.style.display = 'none';
                    customerNameInput.style.display = 'block'; // Show text input for customer name
                    customerNameSelect.innerHTML = ''; // Clear any dropdown options
                }
            });

            // Handle customer name selection (for members)
            customerNameSelect.addEventListener('change', () => {
                const selectedIcNumber = customerNameSelect.value;

                if (selectedIcNumber) {
                    fetch(`sales_interface.php?icnumber=${selectedIcNumber}`)
                        .then(response => response.json())
                        .then(details => {
                            if (details.icnumber) {
                                document.getElementById('icnumber').value = details.icnumber;
                                document.getElementById('customer-email').value = details.email || '';
                                document.getElementById('customer-number').value = details.phone || '';

                                if (membershipCheckbox.checked) {
                                    fetch(`sales_interface.php?membership_details=${selectedIcNumber}`)
                                        .then(response => response.json())
                                        .then(membership => {
                                            if (membership.membership_id) {
                                                document.getElementById('membership-info').value =
                                                    `ID: ${membership.membership_id}, Type: ${membership.membership_type}, Expiry: ${membership.membership_expirydate}`;
                                            } else {
                                                document.getElementById('membership-info').value = 'No membership found';
                                            }
                                        });
                                }
                            } else {
                                alert('Customer not found!');
                            }
                        });
                } else {
                    document.getElementById('icnumber').value = '';
                    document.getElementById('customer-email').value = '';
                    document.getElementById('customer-number').value = '';
                }
            });

            // Handle IC number input change
            icNumberInput.addEventListener('change', () => {
                const icnumber = icNumberInput.value;
                fetch(`sales_interface.php?icnumber=${icnumber}`)
                    .then(response => response.json())
                    .then(details => {
                        if (details.icnumber) {
                            document.getElementById('customer-email').value = details.email || '';
                            document.getElementById('customer-number').value = details.phone || '';
                        } else {
                            alert('Customer not found!');
                        }
                    });
            });
        });
    </script>

</body>
</html>
