<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit and Sales Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column; /* Updated to allow stacking */
        }
        .container {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping */
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px; /* Added width for better layout */
        }
        .card h3 {
            margin: 0;
            font-size: 18px;
        }
        .card p {
            font-size: 24px;
            color: #28a745;
        }
        .chart-container {
            width: 600px;
            height: 400px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
</head>
<body>
    <div class="container">
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
    </div>

    <?php
    // Database connection
    $host = 'localhost';
    $dbname = 'pos'; // Change this
    $user = 'root';     // Change this
    $pass = '';     // Change this

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch total profit
        $query = $pdo->prepare("SELECT SUM(profit) AS totalProfit FROM profit");
        $query->execute();
        $totalProfit = $query->fetch(PDO::FETCH_ASSOC)['totalProfit'];

        // Fetch profit over time
        $query = $pdo->prepare("SELECT profit, sale_datetime FROM profit ORDER BY sale_datetime");
        $query->execute();
        $profits = $query->fetchAll(PDO::FETCH_ASSOC);

        // Fetch total sales
        $query = $pdo->prepare("
            SELECT SUM(total_price) AS totalSales, sale_date
            FROM sales_transaction
            GROUP BY transaction_id, sale_date
            ORDER BY sale_date
        ");
        $query->execute();
        $salesData = $query->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
    ?>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        // Update total profit
        const totalProfit = <?php echo json_encode($totalProfit); ?>;
        document.getElementById('totalProfit').textContent = `$${parseFloat(totalProfit).toFixed(2)}`;

        // Prepare data for the profit chart
        const profits = <?php echo json_encode($profits); ?>;
        const profitDates = profits.map(profit => profit.sale_datetime);
        const profitValues = profits.map(profit => profit.profit);

        // Create profit chart
        const ctxProfit = document.getElementById('profitChart').getContext('2d');
        const profitChart = new Chart(ctxProfit, {
            type: 'line',
            data: {
                labels: profitDates,
                datasets: [{
                    label: 'Profit Over Time',
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
                        type: 'time',
                        time: {
                            unit: 'day'
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Profit ($)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Update total sales
        const salesData = <?php echo json_encode($salesData); ?>;
        const totalSales = salesData.reduce((sum, sale) => sum + parseFloat(sale.totalSales), 0);
        document.getElementById('totalSales').textContent = `$${parseFloat(totalSales).toFixed(2)}`;

        // Prepare data for the sales chart
        const salesDates = salesData.map(sale => sale.sale_date);
        const salesValues = salesData.map(sale => sale.totalSales);

        // Create sales chart
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: salesDates,
                datasets: [{
                    label: 'Total Sales Over Time',
                    data: salesValues,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Sales ($)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
