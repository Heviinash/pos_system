<?php
// Database configuration
$servername = "localhost"; // Change this as needed
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "pos"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search request
$customers = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchTerm = $conn->real_escape_string($searchTerm);

    // Prepare the SQL query
    $sql = "SELECT * FROM customerregistration WHERE name LIKE '%$searchTerm%' OR icnumber = '$searchTerm'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch all customer data
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Search Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        #customer-info {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 15px;
            display: none;
        }
        .input-group {
            margin-bottom: 10px;
        }
        label {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h1>Customer Search</h1>
<div>
    <div class="input-group">
        <label for="search-input">Search by Name or IC Number:</label>
        <input type="text" id="search-input" placeholder="Enter customer name or IC number">
        <button onclick="searchCustomer()">Search</button>
    </div>

    <div id="customer-info">
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> <span id="name"></span></p>
        <p><strong>IC Number:</strong> <span id="icnumber"></span></p>
        <p><strong>Customer ID:</strong> <span id="customerid"></span></p>
        <p><strong>Date of Birth:</strong> <span id="dateofbirth"></span></p>
        <p><strong>Gender:</strong> <span id="gender"></span></p>
        <p><strong>Email:</strong> <span id="email"></span></p>
        <p><strong>Address:</strong> <span id="address"></span></p>
        <p><strong>Phone:</strong> <span id="phone"></span></p>
        <p><strong>Membership:</strong> <span id="membership"></span></p>
    </div>
</div>

<script>
    function searchCustomer() {
        const searchInput = document.getElementById('search-input').value;
        const apiUrl = `customer_search.php?search=${encodeURIComponent(searchInput)}`;

        fetch(apiUrl)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const htmlDocument = parser.parseFromString(data, 'text/html');
                const customer = JSON.parse(htmlDocument.querySelector('script[type="application/json"]').textContent);

                if (customer.length > 0) {
                    const c = customer[0]; // Assuming you want the first result
                    document.getElementById('name').textContent = c.name;
                    document.getElementById('icnumber').textContent = c.icnumber;
                    document.getElementById('customerid').textContent = c.customerid;
                    document.getElementById('dateofbirth').textContent = c.dateofbirth;
                    document.getElementById('gender').textContent = c.gender;
                    document.getElementById('email').textContent = c.email;
                    document.getElementById('address').textContent = c.address;
                    document.getElementById('phone').textContent = c.phone;
                    document.getElementById('membership').textContent = c.membership;

                    document.getElementById('customer-info').style.display = 'block';
                } else {
                    alert('No customer found.');
                    document.getElementById('customer-info').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching customer data:', error);
                alert('An error occurred while searching for the customer.');
            });
    }
</script>

<script type="application/json">
    <?php echo json_encode($customers); ?>
</script>

</body>
</html>
