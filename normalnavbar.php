<!-- navbar.php -->
<nav class="navbar">
    <ul class="nav-links">
        <li><a href="mainpage.php">Home</a></li>
        
        <li>
            <a href="viewinventory.php">Inventory</a>
            <ul class="submenu">
                <li><a href="viewinventory.php">View Inventory</a></li>
                <li><a href="addingproduct.php">Adding Product</a></li>
        <li><a href="inventorytrack.php">Inventory Records</a></li>
                <li>
                    <a href="#">Restock</a>
                    <ul class="submenu">
                        <li><a href="request_restock.php">Request Restock</a></li>
                        <li><a href="restock.php">Manual Restock</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        
        <li>
            <a href="#">Services</a>
            <ul class="submenu">
                <li><a href="add_inventory.php">Add New Service Product/Items</a></li>
                <li><a href="add_service.php">Create Service & Assign Product</a></li>
                <!-- Additional services related links can go here -->
            </ul>
        </li>
        
        <li>
            <a href="#">Customer Management</a>
            <ul class="submenu">

                <li>
                    <a href="#">Registration</a>
                    <ul class="submenu">
                        <li><a href="customerregistration.php">Customer Registration</a></li>
                        <li><a href="companyregistration.php">Company Registration</a></li>
                    </ul>
                </li>
                <li><a href="customer_log.php">Log Customer Visit</a></li>
                <li><a href="viewcustomerlogs.php">View Customer Logs</a></li>
                <li><a href="customer_followup.php">Customer Follow Up</a></li>
                <li><a href="save_id_settings.php">Custom ID Generator</a></li>
            </ul>
        </li>

        <li>
            <a href="#">Financials</a>
            <ul class="submenu">
                <li><a href="financialdashboard.php">Financial Dashboard</a></li>
                <li><a href="trialpaymenttest.php">Trial Payment Test</a></li>
                <li><a href="comissions.php">Employee Commision Table</a></li>
            </ul>
        </li>

        <li>
            <a href="#">Promotions</a>
            <ul class="submenu">
                <li><a href="promo_setup.php">Promo Setup</a></li>
            </ul>
        </li>

        <li>
            <a href="#">User Management</a>
            <ul class="submenu">
                <li><a href="systemuserregistration.php">System User Registration</a></li>
                <li><a href="userrolemanagement.php">Create User Role</a></li>
                <li><a href="manageuserrolemanagement.php">User Role Management</a></li>
                <li><a href="manage_permission.php">Manage Permissions</a></li>
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
