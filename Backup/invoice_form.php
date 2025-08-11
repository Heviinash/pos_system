<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'pos';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch company details from companyregistration table if needed
$company_query = "SELECT * FROM companyregistration";
$company_result = $conn->query($company_query);

// Fetch cart items from the database (assuming you have a cart table)
$cart_query = "SELECT * FROM cart"; // Adjust according to your table structure
$cart_result = $conn->query($cart_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Form</title>
    <style>
        /* Global Styling */
        body {
            font-family: Arial, sans-serif;
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            text-align: center;
            color: #f8f8f8;
        }

        /* Form Styling */
        form {
            width: 80%;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table, th, td {
            border: 1px solid white;
            padding: 10px;
            text-align: left;
        }

        th, td {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Invoice Form</h1>

    <!-- Company Invoice Form -->
    <form action="generate_invoice.php" method="post">
        <h2>Company Details</h2>

        <label for="company_name">Company Name:</label>
        <select name="company_name" id="company_name" required>
            <option value="">Select a company</option>
            <?php while ($row = $company_result->fetch_assoc()): ?>
                <option value="<?php echo $row['company_id']; ?>">
                    <?php echo $row['company_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="contact_person">Contact Person:</label>
        <input type="text" name="contact_person" id="contact_person" required>

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phone" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="membership">Membership Status:</label>
        <input type="text" name="membership" id="membership" required>

        <label for="industry">Industry Type:</label>
        <input type="text" name="industry" id="industry" required>

        <input type="submit" value="Generate Invoice">
    </form>

    <!-- Display Cart Items -->
    <h2>Cart Items</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($cart_result->num_rows > 0) {
                while ($item = $cart_result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$item['product_name']}</td>
                            <td>{$item['sku']}</td>
                            <td>{$item['quantity']}</td>
                            <td>{$item['price']}</td>
                            <td>" . $item['quantity'] * $item['price'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No items in the cart.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div style="text-align: center;">
        <a href="admininventory.php" class="btn">Continue Shopping</a>
    </div>
</body>
</html>
