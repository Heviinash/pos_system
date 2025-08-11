<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$response = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchTerm = $conn->real_escape_string($searchTerm);

    // Prepare the SQL query for customer information
    $sql = "SELECT * FROM customerregistration WHERE name LIKE '%$searchTerm%' OR icnumber = '$searchTerm'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch customer data
        $response['customer'] = $result->fetch_assoc();
    } else {
        $response['customer'] = null; // No customer found
    }

    // If a customer was found, fetch the membership data
    if ($response['customer'] !== null) {
        $icnumber = $response['customer']['icnumber'];
        $membershipSql = "SELECT * FROM customermemberships WHERE icnumber = '$icnumber'";
        $membershipResult = $conn->query($membershipSql);

        if ($membershipResult->num_rows > 0) {
            // Fetch membership data
            $response['membership'] = $membershipResult->fetch_assoc();
        } else {
            $response['membership'] = null; // No membership found
        }
    }

    // Close the connection
    $conn->close();

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
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
        #customer-info, #membership-info {
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

    <div id="membership-info">
        <h2>Membership Information</h2>
        <p><strong>Membership ID:</strong> <span id="membership_id"></span></p>
        <p><strong>Membership Type:</strong> <span id="membership_type"></span></p>
        <p><strong>Membership Expiry Date:</strong> <span id="membership_expirydate"></span></p>
        <p><strong>Membership Points:</strong> <span id="membership_points"></span></p>
    </div>
</div>

<script>
    function searchCustomer() {
        const searchInput = document.getElementById('search-input').value;
        const apiUrl = `customer_search.php?search=${encodeURIComponent(searchInput)}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.customer) {
                    const c = data.customer; // Customer data
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

                    if (data.membership) {
                        const m = data.membership; // Membership data
                        document.getElementById('membership_id').textContent = m.membership_id;
                        document.getElementById('membership_type').textContent = m.membership_type;
                        document.getElementById('membership_expirydate').textContent = m.membership_expirydate;
                        document.getElementById('membership_points').textContent = m.membership_points;

                        document.getElementById('membership-info').style.display = 'block';
                    } else {
                        document.getElementById('membership-info').style.display = 'none'; // No membership found
                    }
                } else {
                    alert('No customer found.');
                    document.getElementById('customer-info').style.display = 'none';
                    document.getElementById('membership-info').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching customer data:', error);
                alert('An error occurred while searching for the customer.');
            });
    }
</script>

</body>
</html>
