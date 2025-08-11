<?php
// Connection to the database
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch approved expenses data grouped by expense_type
$query_approved = "SELECT expense_type, SUM(amount) AS total_amount FROM expenses WHERE status = 'approved' GROUP BY expense_type";
$result_approved = $conn->query($query_approved);

// Prepare data for output
$expenses_data = [];
if ($result_approved->num_rows > 0) {
    while ($row = $result_approved->fetch_assoc()) {
        $expenses_data[] = $row;
    }
}

// Fetch expenses with Pending status
$query_pending = "SELECT expense_type, amount, date FROM expenses WHERE status = 'Pending'";
$result_pending = $conn->query($query_pending);

// Fetch expenses with Acknowledge status
$query_acknowledge = "SELECT expense_type, amount, date FROM expenses WHERE status = 'Acknowledge'";
$result_acknowledge = $conn->query($query_acknowledge);

// Prepare data for pending and acknowledge expenses
$pending_data = [];
if ($result_pending->num_rows > 0) {
    while ($row = $result_pending->fetch_assoc()) {
        $pending_data[] = $row;
    }
}

$acknowledge_data = [];
if ($result_acknowledge->num_rows > 0) {
    while ($row = $result_acknowledge->fetch_assoc()) {
        $acknowledge_data[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Display Cards</title>
<style>
    /* General body styles for dark theme */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: url('darkwallpaper.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f4f4f9;
        display: flex;
    }

    /* Sidebar styles */
    #sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #2c3e50; /* Darker shade */
        overflow-x: hidden;
        padding-top: 20px;
        transition: 0.3s;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
    }

    #sidebar a {
        padding: 15px 20px;
        text-decoration: none;
        font-size: 18px;
        color: #ecf0f1;
        display: block;
        transition: 0.3s;
    }

    #sidebar a:hover {
        background-color: #1abc9c; /* Greenish hover effect */
    }

    #sidebar .sidebar-header {
        font-size: 24px;
        color: #fff;
        text-align: center;
        margin-bottom: 10px;
        border-bottom: 2px solid #f39c12; /* Orange accent */
        padding-bottom: 10px;
    }

    /* Main content styles */
    .main-content {
        flex-grow: 1;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
        margin-left: 270px; /* Adjusted to make space for the sidebar */
        background-color: rgba(0, 0, 0, 0.6); /* Slight overlay to enhance contrast */
        border-radius: 10px;
    }

    .card {
        background-color: #2c3e50; /* Darker card background */
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        margin: 16px;
        padding: 20px;
        width: 300px;
        text-align: center;
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        background-color: #1abc9c; /* Greenish hover effect */
    }

    .card h2 {
        color: blanchedalmond; /* Orange color for heading */
        font-size: 24px;
        margin-bottom: 10px;
    }

    .card p {
        color: ghostwhite; /* Light grey text */
        font-size: 18px;
    }

    /* Side panel styles */
    .side-panel {
        width: 320px;
        background-color: darkcyan; /* Dark blue-gray for a more professional dark theme */
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); /* Slightly heavier shadow for depth */
        padding: 20px;
        position: fixed;
        top: 20px;
        right: -340px; /* Initially hidden off-screen */
        height: 80%;
        overflow-y: auto;
        transition: right 0.3s ease-in-out;
        z-index: 10; /* Ensures the side panel is above other content */
    }

    .side-panel.open {
        right: 20px; /* Slide into view when the 'open' class is added */
    }

    /* Heading style */
    .side-panel h3 {
        color: ghostwhite; /* Light color for headings */
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        text-transform: uppercase;
        border-bottom: 2px solid #e67e22; /* Orange accent color */
        padding-bottom: 10px;
    }

    /* Status section for better readability */
    .side-panel .status-section {
        margin-bottom: 30px;
    }

    /* Expense item styling */
    .side-panel .expense-item {
        background-color: #34495e; /* Slightly lighter background for each expense item */
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    .side-panel .expense-item h5 {
        color: #ecf0f1;
        font-size: 18px;
        margin-bottom: 8px;
    }

    .side-panel .expense-item p {
        color: #95a5a6; /* Light grayish text */
        font-size: 14px;
        margin: 5px 0;
    }

    .side-panel .expense-item p strong {
        color: #ffffff; /* White for important text */
    }

    .side-panel .status-section h4 {
        color: #ecf0f1;
        font-size: 20px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    /* Acknowledged and Pending Expenses */
    .side-panel .status-section .expense-item p {
        font-size: 16px;
        color: #7f8c8d; /* Lighter gray */
    }

    .side-panel .status-section .expense-item p.date {
        font-style: italic;
        color: #7f8c8d; /* Lighter color for date */
    }

    /* Close button for the side panel */
    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: transparent;
        color: #ecf0f1;
        border: none;
        font-size: 22px;
        cursor: pointer;
        z-index: 15; /* Ensure it's above other elements */
    }

    .close-btn:hover {
        color: #e67e22; /* Orange color when hovered */
    }

    /* Button to toggle the side panel */
    .toggle-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #9b59b6; /* Purple color for the button */
        color: white;
        border: none;
        padding: 15px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s;
    }

    .toggle-btn:hover {
        background-color: #8e44ad; /* Darker purple on hover */
    }

</style>


</head>
<body>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <div id="sidebar">
        <div class="sidebar-header"><i class="fa fa-calculator"></i> <i class="fa fa-chart-line"></i>
          <i class="fa fa-money-bill-wave"></i></div>
        <a href="accdashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
        <a href="acccashflowstatement.php"><i class="fa fa-money-bill-wave"></i> Cash Flow Statement</a>
        <a href="accexpensesform.php"><i class="fa fa-edit"></i> Expenses Form</a>
        <a href="accexpensesapproval.php"><i class="fa fa-check-square"></i> Expenses Approval</a>
        <a href="accviewexpenses.php"><i class="fa fa-file-invoice-dollar"></i> View Expenses</a>
        <a href="accledger.php"><i class="fa fa-bookmark"></i> View Ledger</a>
        <a href="acctaxreport.php"><i class="fa fa-calculator"></i> Tax Report Table</a>
        <a href="mainpage.php"><i class="fa fa-sign-out-alt"></i> Exit</a>
    </div>

    <div class="main-content">
        <?php foreach ($expenses_data as $expense): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($expense['expense_type']); ?></h2>
                <p>Total Spent: RM <?php echo number_format($expense['total_amount'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="side-panel" id="sidePanel">
        <div class="status-section">
            <button class="close-btn">×</button>
            <h4>Pending Expenses</h4>
            <?php if (empty($pending_data)): ?>
                <p>No pending expenses.</p>
            <?php else: ?>
                <?php foreach ($pending_data as $expense): ?>
                    <div class="expense-item">
                        <h5><?php echo htmlspecialchars($expense['expense_type']); ?></h5>
                        <p>Amount: RM <?php echo number_format($expense['amount'], 2); ?></p>
                        <p>Date: <?php echo date("Y-m-d", strtotime($expense['date'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>


        <!--- if needed to show acknowldge remove comment

        <div class="status-section">
            <h4>Acknowledge Expenses</h4>
                
                /*

            <?php if (empty($acknowledge_data)): ?>
                <p>No acknowledged expenses.</p>
            <?php else: ?>
                <?php foreach ($acknowledge_data as $expense): ?>
                    <div class="expense-item">
                        <h5><?php echo htmlspecialchars($expense['expense_type']); ?></h5>
                        <p>Amount: RM <?php echo number_format($expense['amount'], 2); ?></p>
                        <p>Date: <?php echo date("Y-m-d", strtotime($expense['date'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            */


        </div>

        --->


    </div>

    <!-- Button to toggle the side panel -->
    <button class="toggle-btn" onclick="toggleSidePanel()">☰</button>

    <script>
        // JavaScript to toggle the side panel
        function toggleSidePanel() {
            const sidePanel = document.getElementById('sidePanel');
            sidePanel.classList.toggle('open');
        }


        // Get the side panel and the close button
        const sidePanel = document.querySelector('.side-panel');
        const closeBtn = document.querySelector('.close-btn');

        // Function to close the side panel
        closeBtn.addEventListener('click', function() {
            sidePanel.classList.remove('open');
        });

        // Function to open the side panel (optional, for other events like button click)
        function openSidePanel() {
            sidePanel.classList.add('open');
        }


    </script>
</body>
</html>
