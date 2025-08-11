<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit and Sales Overview</title>
    <style>
            /* Body and Container Styling */
/* Body and Container Styling */
        body {
            background: url('darkwallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #121212; /* Dark background for the dark theme */
            font-family: 'Arial', sans-serif;
            color: #f0f0f0; /* Light text color for contrast */
        }

        /* Create space between the top of the page and the container */
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            align-items: flex-start;
            justify-content: center;
            max-width: 1200px;
            margin: 50px auto; /* Adjust this value to move the cards down */
        }

        /* Card Styling */
        .card {
            background-color: rgba(40, 40, 40, 0.8); /* Dark translucent background */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.7); /* Strong shadow for dark theme */
            width: 300px;
            flex: 0 1 auto;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        h3 {
            width: 100%;
            border-bottom: 2px solid rgba(255, 255, 255, 0.5); /* Subtle bottom border */
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #ffffff; /* White for better visibility */
        }

        p {
            font-size: 1.2rem;
            margin: 10px 0;
            color: #cccccc; /* Light gray for paragraph text */
        }

        /* Chart Container Styling */
        .chart-container {
            background-color: #ffffff; /* White background for the chart containers */
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6); /* Softer shadow for charts */
            width: 600px;
            height: 400px;
            color: #000; /* Dark text color for charts */
        }

        /* Canvas Element */
        canvas {
            width: 100%;
            height: 100%;
        }

        /* Add hover effect on chart containers */
        .chart-container:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.8);
            transform: scale(1.02); /* Slightly scale up on hover for interaction effect */
        }

        /* Chart.js text color styling */
        .chartjs-render-monitor {
            color: #ffffff !important; /* Force text color to white for chart labels */
        }

        .chart-container .chartjs-title,
        .chart-container .chartjs-label {
            fill: #ffffff !important; /* Ensure text labels inside the charts are white */
        }

        .chartjs-render-monitor .chartjs-legend,
        .chartjs-render-monitor .chartjs-label {
            color: #ffffff !important; /* Make sure legend and labels are white */
        }

        /* Text Styling */
        h3, p {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.7); /* Subtle text shadow for readability */
        }

        /* Dark Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #121212; /* Background of the scrollbar */
        }

        ::-webkit-scrollbar-thumb {
            background-color: #333333; /* Scrollbar color */
            border-radius: 10px;
            border: 2px solid #121212; /* Adds space between track and scrollbar */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chart-container {
                width: 100%; /* Make charts take full width on smaller screens */
                height: 300px; /* Adjust height for better fit */
            }

            .card {
                width: 100%; /* Make cards take full width on smaller screens */
            }
        }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <link rel="stylesheet" href="navbar.css">
    <br>
    <br>
    <br>
    <div class="container" style="align-self: center;">
        <!-- Total profit card -->
        <div class="card">
            <h3>Total Profit</h3>
            <p id="totalProfit">Loading...</p>
        </div>

        <!-- Total sales card -->
        <div class="card">
            <h3>Total Sales</h3>
            <p id="totalSales">Loading...</p>
        </div>

        <!-- Profit chart -->
        <div class="chart-container">
            <canvas id="profitChart"></canvas>
        </div>

        <!-- Sales chart -->
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Most Sold Products Chart -->
        <div class="chart-container">
            <canvas id="mostSoldChart"></canvas>
        </div>

        <!-- Most Revenue Generating Products Chart -->
        <div class="chart-container">
            <canvas id="mostRevenueChart"></canvas>
        </div>
    </div>

<?php
// Database connection
$host = 'localhost';
$dbname = 'pos'; // Change this
$user = 'root';     // Change this
$pass = '';     // Change this if needed




try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch total profit
    $query = $pdo->prepare("SELECT SUM(profit) AS totalProfit FROM profit");
    $query->execute();
    $totalProfit = $query->fetch(PDO::FETCH_ASSOC)['totalProfit'];

    // Fetch profit over time (aggregated by month)
    $query = $pdo->prepare("
        SELECT SUM(profit) AS total_profit, DATE_FORMAT(sale_datetime, '%Y-%m') AS month
        FROM profit
        GROUP BY month
        ORDER BY month
    ");
    $query->execute();
    $profits = $query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total sales (aggregated by month)
    $query = $pdo->prepare("
        SELECT SUM(total_price) AS total_sales, DATE_FORMAT(sale_date, '%Y-%m') AS month
        FROM sales_transaction
        GROUP BY month
        ORDER BY month
    ");
    $query->execute();
    $salesData = $query->fetchAll(PDO::FETCH_ASSOC);

    // Initialize variables for most sold and revenue-generating products
    $mostSoldProductNames = [];
    $mostRevenueProductNames = [];

    // Fetch most sold products
    $queryMostSold = $pdo->prepare("
        SELECT barcode, SUM(quantity) AS total_quantity
        FROM sales_transaction
        GROUP BY barcode
        ORDER BY total_quantity DESC
        LIMIT 5
    ");
    $queryMostSold->execute();
    $mostSoldProducts = $queryMostSold->fetchAll(PDO::FETCH_ASSOC);

    // Fetch product names for most sold products
    foreach ($mostSoldProducts as $product) {
        $query = $pdo->prepare("
            SELECT CONCAT(brand, ' ', producttype, ' ', variant) AS name 
            FROM productinventorytable 
            WHERE barcode = ?
        ");
        $query->execute([$product['barcode']]);
        $name = $query->fetch(PDO::FETCH_ASSOC)['name'] ?? 'Unknown Product';
        $mostSoldProductNames[] = ['name' => $name, 'total_quantity' => $product['total_quantity']];
    }

    // Fetch most revenue-generating products
    $queryMostRevenue = $pdo->prepare("
        SELECT barcode, SUM(total_price) AS total_revenue
        FROM sales_transaction
        GROUP BY barcode
        ORDER BY total_revenue DESC
        LIMIT 5
    ");
    $queryMostRevenue->execute();
    $mostRevenueProducts = $queryMostRevenue->fetchAll(PDO::FETCH_ASSOC);

    // Fetch product names for most revenue-generating products
    foreach ($mostRevenueProducts as $product) {
        $query = $pdo->prepare("
            SELECT CONCAT(brand, ' ', producttype, ' ', variant) AS name 
            FROM productinventorytable 
            WHERE barcode = ?
        ");
        $query->execute([$product['barcode']]);
        $name = $query->fetch(PDO::FETCH_ASSOC)['name'] ?? 'Unknown Product';
        $mostRevenueProductNames[] = ['name' => $name, 'total_revenue' => $product['total_revenue']];
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Update total profit
            const totalProfit = <?php echo json_encode($totalProfit); ?>;
            document.getElementById('totalProfit').textContent = `RM${parseFloat(totalProfit).toFixed(2)}`;

            // Prepare data for the profit chart
            const profits = <?php echo json_encode($profits); ?>;
            const profitDates = profits.map(profit => profit.month);
            const profitValues = profits.map(profit => profit.total_profit);

            // Create profit chart
            const ctxProfit = document.getElementById('profitChart').getContext('2d');
            const profitChart = new Chart(ctxProfit, {
                type: 'line',
                data: {
                    labels: profitDates,
                    datasets: [{
                        label: 'Profit Over Time RM',
                        data: profitValues,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Profit Made (RM)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Update total sales
            const salesData = <?php echo json_encode($salesData); ?>;
            const totalSales = salesData.reduce((sum, sale) => sum + parseFloat(sale.total_sales), 0);
            document.getElementById('totalSales').textContent = `RM${parseFloat(totalSales).toFixed(2)}`;

            // Prepare data for the sales chart
            const salesDates = salesData.map(sale => sale.month);
            const salesValues = salesData.map(sale => sale.total_sales);

            // Create sales chart as a line graph
            const ctxSales = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: salesDates,
                    datasets: [{
                        label: 'Sales Over Time RM',
                        data: salesValues,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Total Sales Made(RM)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Prepare data for the most sold products chart
            const mostSoldProducts = <?php echo json_encode($mostSoldProductNames); ?>;
            const mostSoldLabels = mostSoldProducts.map(product => product.name);
            const mostSoldQuantities = mostSoldProducts.map(product => product.total_quantity);

            // Create most sold products chart
            const ctxMostSold = document.getElementById('mostSoldChart').getContext('2d');
            const mostSoldChart = new Chart(ctxMostSold, {
                type: 'bar',
                data: {
                    labels: mostSoldLabels,
                    datasets: [{
                        label: 'Most Sold Products',
                        data: mostSoldQuantities,
                        backgroundColor: 'rgba(255, 206, 86, 0.5)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity Sold'
                            }
                        }
                    }
                }
            });

            // Prepare data for the most revenue-generating products chart
            const mostRevenueProducts = <?php echo json_encode($mostRevenueProductNames); ?>;
            const mostRevenueLabels = mostRevenueProducts.map(product => product.name);
            const mostRevenueValues = mostRevenueProducts.map(product => product.total_revenue);

            // Create most revenue-generating products chart
            const ctxMostRevenue = document.getElementById('mostRevenueChart').getContext('2d');
            const mostRevenueChart = new Chart(ctxMostRevenue, {
                type: 'bar',
                data: {
                    labels: mostRevenueLabels,
                    datasets: [{
                        label: 'Most Revenue Generating Products RM',
                        data: mostRevenueValues,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Revenue Made (RM)'
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>
</html>
