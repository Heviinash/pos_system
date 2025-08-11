<?php

// Database connection settings
$host = 'localhost'; // Your database host
$dbname = 'pos'; // Your database name
$username = 'root'; // Your database username
$password = ''; // Your database password


try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit; // Stop execution if the connection fails
}

// Fetch user permissions based on their role
$role_name = $_SESSION['role_name']; // Assuming the role_name is stored in the session
$query = "SELECT permission_name FROM roles_permission WHERE role_name = ?";
$stmt = $db->prepare($query);
$stmt->execute([$role_name]);
$permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Convert permissions array to a more manageable format
$permissions = array_flip($permissions); // Flip to make permissions keys for easier checking
?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="mainpage.php">Home</a></li>

        <li>
            <a href="viewinventory.php">Inventory</a>
            <ul class="submenu">
                <?php if (isset($permissions['viewinventory.php'])) : ?>
                    <li><a href="viewinventory.php">View Inventory</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['addingproduct.php'])) : ?>
                    <li><a href="addingproduct.php">Adding Product</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['inventorytrack.php'])) : ?>
                    <li><a href="inventorytrack.php">Inventory Records</a></li>
                <?php endif; ?>
                <li>
                    <a href="#">Restock</a>
                    <ul class="submenu">
                        <?php if (isset($permissions['request_restock.php'])) : ?>
                            <li><a href="request_restock.php">Request Restock</a></li>
                        <?php endif; ?>
                        <?php if (isset($permissions['restock.php'])) : ?>
                            <li><a href="restock.php">Manual Restock</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </li>

        <li>
            <a href="#">Services</a>
            <ul class="submenu">
                <?php if (isset($permissions['add_inventory.php'])) : ?>
                    <li><a href="add_inventory.php">Add New Service Product/Items</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['add_service.php'])) : ?>
                    <li><a href="add_service.php">Create Service & Assign Product</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#">Customer Management</a>
            <ul class="submenu">
                    <li>
                        <a href="#">Registration</a>
                        <ul class="submenu">
                            <?php if (isset($permissions['customerregistration.php'])) : ?>
                                <li><a href="customerregistration.php">Customer Registration</a></li>
                            <?php endif; ?>
                            <?php if (isset($permissions['companyregistration.php'])) : ?>
                                <li><a href="companyregistration.php">Company Registration</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php if (isset($permissions['customer_log.php'])) : ?>
                    <li><a href="customer_log.php">Log Customer Visit</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['viewcustomerlogs.php'])) : ?>
                    <li><a href="viewcustomerlogs.php">View Customer Logs</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['customer_followup.php'])) : ?>
                    <li><a href="customer_followup.php">Customer Follow Up</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['save_id_settings.php'])) : ?>
                    <li><a href="save_id_settings.php">Custom ID Generator</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#">Financials</a>
            <ul class="submenu">
                <?php if (isset($permissions['financialdashboard.php'])) : ?>
                    <li><a href="financialdashboard.php">Financial Dashboard</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['trialpaymenttest.php'])) : ?>
                    <li><a href="trialpaymenttest.php">Trial Payment Test</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['comissions.php'])) : ?>
                    <li><a href="comissions.php">Employee Commission Table</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#">Promotions</a>
            <ul class="submenu">
                <?php if (isset($permissions['promo_setup.php'])) : ?>
                    <li><a href="promo_setup.php">Promo Setup</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#">User Management</a>
            <ul class="submenu">
                <?php if (isset($permissions['systemuserregistration.php'])) : ?>
                    <li><a href="systemuserregistration.php">System User Registration</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['userrolemanagement.php'])) : ?>
                    <li><a href="userrolemanagement.php">Create User Role</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['manageuserrolemanagement.php'])) : ?>
                    <li><a href="manageuserrolemanagement.php">User Role Management</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['manage_permission.php'])) : ?>
                    <li><a href="manage_permission.php">Manage Permissions</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="profile.php">Profile</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
