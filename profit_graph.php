<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        <!-- Profit chart -->
        <div class="chart-container">
            <canvas id="profitChart"></canvas>
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

        // Prepare data for the chart
        const profits = <?php echo json_encode($profits); ?>;
        const dates = profits.map(profit => profit.sale_datetime);
        const profitValues = profits.map(profit => profit.profit);

        // Create a chart
        const ctx = document.getElementById('profitChart').getContext('2d');
        const profitChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
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
    });
    </script>
</body>
</html>
