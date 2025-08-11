<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Running Balance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            line-height: 1.6;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Container Styling */
        .container {
            max-width: 600px;
            width: 100%;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Heading Styling */
        .heading {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* Styling for the Balance and Currency */
        .balance {
            font-size: 40px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .currency {
            font-size: 22px;
            font-weight: 500;
            color: #3498db;
        }

        /* Toggle Switch Styling */
        .toggle-switch {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            border-radius: 50%;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
        }

        input:checked + .slider {
            background-color: #3498db;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="heading">Running Balance</h1>
        <div class="balance">
            <span class="currency">RM</span> <span id="balanceInclTax">Loading...</span>
        </div>

        <!-- Toggle Switch for tax inclusion -->
        <div class="toggle-switch">
            <label class="switch">
                <input type="checkbox" id="toggleTax">
                <span class="slider"></span>
            </label>
            <span id="toggleText">Show Balance Excluding Tax</span>
        </div>
    </div>

    <!-- Container for Total Tax -->
    <div class="container">
        <h1 class="heading">Total Tax</h1>
        <div class="balance">
            <span class="currency">RM</span> <span id="totalTax">Loading...</span>
        </div>
    </div>

    <?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'pos');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch the running balance from the database
    $query = "SELECT balance FROM running_balance LIMIT 1"; // Make sure only one row is fetched
    $result = $conn->query($query);

    if (!$result) {
        die("Error fetching data: " . $conn->error);
    }

    $balance = 0;
    if ($row = $result->fetch_assoc()) {
        $balance = $row['balance']; // Fetch balance from the result
    }

    $conn->close();
    ?>

    <script>
        // PHP variable passed to JavaScript
        const balance = <?php echo $balance; ?>;
        const taxRate = 0.06; // 6% tax rate

        // Calculate balance excluding tax and total tax
        const balanceExclTax = balance / (1 + taxRate);
        const totalTax = balance - balanceExclTax;

        // Display balance with tax and total tax by default
        document.getElementById('balanceInclTax').textContent = balance.toFixed(2);
        document.getElementById('totalTax').textContent = totalTax.toFixed(2);

        // Toggle functionality
        document.getElementById('toggleTax').addEventListener('change', function() {
            const isChecked = this.checked;

            if (isChecked) {
                // Show balance excluding tax
                document.getElementById('balanceInclTax').textContent = balanceExclTax.toFixed(2);
                document.getElementById('toggleText').textContent = "Show Balance Including Tax";
            } else {
                // Show balance including tax
                document.getElementById('balanceInclTax').textContent = balance.toFixed(2);
                document.getElementById('toggleText').textContent = "Show Balance Excluding Tax";
            }
        });
    </script>
</body>
</html>
