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

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<nav class="navbar">
    <ul class="nav-links">
        <li>
            <a href="mainpage.php"><i class="fa fa-home"></i></a>
        </li>
        <!---Home--->

        <li>
            <a href="#"><i class="fa fa-clipboard-list"></i> Register</a>
            <ul class="submenu">

                    <?php if (isset($permissions['customerregistration.php'])) : ?>
                        <li><a href="customerregistration.php"><i class="fa fa-user"></i>Customer</a></li>
                    <?php endif; ?>

                    <?php if (isset($permissions['companyregistration.php'])) : ?>
                        <li><a href="companyregistration.php"><i class="fa fa-building"></i>Company</a></li>
                    <?php endif; ?> 

            </ul>
        </li>

        <li>
            <?php if (isset($permissions['viewinventory.php'])) : ?>
                <a href="viewinventory.php"><i class="fa fa-warehouse"></i> Inventory</a>
            <?php endif; ?>   
            <ul class="submenu">

                <?php if (isset($permissions['viewinventory.php'])) : ?>
                    <li><a href="viewinventory.php"><i class="fa fa-box"></i>View Inventory</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['addingproduct.php'])) : ?>
                    <li><a href="addingproduct.php"><i class="fa fa-plus-square"></i> Add Product</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['inventorytrack.php'])) : ?>
                    <li><a href="inventorytrack.php"><i class="fa fa-clipboard-check"></i> Inventory Records</a></li>
                <?php endif; ?>

                <li>
                    <a href="#"><i class="fa fa-truck-loading"></i> Restock Menu</a>
                    <ul class="submenu">

                        <?php if (isset($permissions['request_restock.php'])) : ?>
                            <li><a href="request_restock.php"><i class="fa fa-paper-plane"></i> Request Restock</a></li>
                        <?php endif; ?>

                        <?php if (isset($permissions['restock.php'])) : ?>
                            <li><a href="restock.php"><i class="fa fa-cart-arrow-down"></i> Manual Restock</a></li>
                        <?php endif; ?>

                    </ul>
                </li>
            </ul>
        </li>

        <li>
            <a href="#"><i class="fa fa-briefcase"></i> Services</a>
            <ul class="submenu">
                <?php if (isset($permissions['add_inventory.php'])) : ?>
                    <li><a href="add_inventory.php"><i class="fa fa-toolbox"></i> Service Add-ons</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['add_service.php'])) : ?>
                    <li><a href="add_service.php"><i class="fa fa-cogs"></i> Service Setup</a></li>
                <?php endif; ?>
            </ul>
        </li>


        <li>
            <a href="#"><i class="fa fa-users"></i> Cust Management</a>
            <ul class="submenu">

                <?php if (isset($permissions['customer_log.php'])) : ?>
                    <li><a href="customer_log.php"><i class="fa fa-calendar-check"></i> Log Customer Visit</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['viewcustomerlogs.php'])) : ?>
                    <li><a href="viewcustomerlogs.php"><i class="fa fa-address-book"></i> Customer Log History</a></li>
                <?php endif; ?>
                
                <?php if (isset($permissions['customer_followup.php'])) : ?>
                    <li><a href="customer_followup.php"><i class="fa fa-phone"></i> Customer Follow Up</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['customer_purchase_history.php'])) : ?>
                    <li><a href="customer_purchase_history.php"><i class="fa fa-shopping-cart"></i> Purchase History</a></li>
                <?php endif; ?>

                <?php if (isset($permissions['save_id_settings.php'])) : ?>
                    <li><a href="save_id_settings.php"><i class="fa fa-id-card"></i> ID Generator</a></li>
                <?php endif; ?>

            </ul>
        </li>

        <li>
            <a href="#"><i class="fa fa-chart-line"></i> Finance</a>
            <ul class="submenu">
                <?php if (isset($permissions['save_id_settings.php'])) : ?>
                    <li><a href="accdashboard.php"><i class="fa fa-tachometer-alt"></i> Financial Dashboard</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['barcodetestpayment.php'])) : ?>
                    <li><a href="barcodetestpayment.php"><i class="fa fa-cash-register"></i> Sales</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['comissions.php'])) : ?> 
                    <li><a href="comissions.php"><i class="fa fa-money-bill-wave"></i> Employee Commission Table</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['transaction_details.php'])) : ?>
                    <li><a href="transaction_details.php"><i class="fa fa-receipt"></i> Receipt Search</a></li>
                <?php endif; ?>

            </ul>
        </li>

        <li>
            <a href="#"><i class="fa fa-tags"></i> Promo</a>
            <ul class="submenu">
                <?php if (isset($permissions['promo_setup.php'])) : ?>
                    <li><a href="promo_setup.php"><i class="fa fa-tags"></i> Promo Setup</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#"><i class="fa fa-user-cog"></i> User Management</a>
            <ul class="submenu">
                <?php if (isset($permissions['systemuserregistration.php'])) : ?>
                    <li><a href="systemuserregistration.php"><i class="fa fa-user-plus"></i> System User Registration</a></li>
                <?php endif; ?>   
                <?php if (isset($permissions['userrolemanagement.php'])) : ?>
                    <li><a href="userrolemanagement.php"><i class="fa fa-user-shield"></i> Create User Role</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['manageuserrolemanagement.php'])) : ?>
                    <li><a href="manageuserrolemanagement.php"><i class="fa fa-users"></i> User Role Management</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['manage_permission.php'])) : ?>
                    <li><a href="manage_permission.php"><i class="fa fa-user-lock"></i> Manage Permissions</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['rolepermissionview.php'])) : ?>
                    <li><a href="rolepermissionview.php"><i class="fa fa-key"></i> View Permission Based Role</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['loginhistory.php'])) : ?>
                    <li><a href="loginhistory.php"><i class="fa fa-history"></i> User Log History</a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li>
            <a href="#"><i class="fa fa-cog"></i> Settings</a>
            <ul class="submenu">
                <?php if (isset($permissions['quotationpdfsettings.php'])) : ?>
                        <li><a href="quotationpdfsettings.php"><i class="fa fa-file-pdf"></i>Quotation PDF Setup</a></li>
                <?php endif; ?>
                <?php if (isset($permissions['invoicepdfsettings.php'])) : ?>
                        <li><a href="invoicepdfsettings.php"><i class="fa fa-file-invoice"></i>Invoice PDF Setup</a></li>
                <?php endif; ?>
            </ul>    
            

        </li>

        <li style="position: absolute; right: 10px;">
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i></a>
        </li>
    </ul>
</nav>
