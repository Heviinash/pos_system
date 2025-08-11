<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$filter = $_GET['filter'] ?? 'day'; // Default filter is 'day'
$data_points_revenue = [];
$data_points_sales = [];
$data_points_tax = [];
$data_points_profit = []; // Variable to store profit data points
$total_revenue = 0; // Variable to store total revenue
$total_sales = 0; // Variable to store total sales
$total_tax = 0; // Variable to store total tax
$total_profit = 0; // Variable to store total profit
$running_balance = 0; // Variable to store running balance

// Query to get the latest running balance
$balance_query = "SELECT balance FROM running_balance ORDER BY id DESC LIMIT 1"; // Assuming 'id' is the primary key
$balance_result = $conn->query($balance_query);
if ($balance_result->num_rows > 0) {
    $balance_row = $balance_result->fetch_assoc();
    $running_balance = $balance_row['balance']; // Get the latest balance
}
// Query to get Outstanding Revenue (based on the filter)
if ($filter === 'day') {
    $query_revenue = "SELECT DATE_FORMAT(sale_date, '%d %b') AS period, SUM(total_price) AS revenue 
                      FROM sales_transaction 
                      GROUP BY DATE(sale_date)";
    $query_sales = "SELECT DATE_FORMAT(created_at, '%d %b') AS period, SUM(grand_total) AS total_sales 
                    FROM sales_payments 
                    GROUP BY DATE(created_at)";
    $query_sales_tax = "SELECT DATE_FORMAT(created_at, '%d %b') AS period, SUM(grand_total) AS total_sales, SUM(grand_total) * 0.06 AS tax 
                        FROM sales_payments 
                        GROUP BY DATE(created_at)";
    $query_profit = "SELECT DATE_FORMAT(sale_datetime, '%d %b') AS period, SUM(profit) AS profit 
                     FROM profit 
                     GROUP BY DATE(sale_datetime)";
} elseif ($filter === 'month') {
    $query_revenue = "SELECT DATE_FORMAT(sale_date, '%b') AS period, SUM(total_price) AS revenue 
                      FROM sales_transaction 
                      GROUP BY DATE_FORMAT(sale_date, '%Y-%m')";
    $query_sales = "SELECT DATE_FORMAT(created_at, '%b') AS period, SUM(grand_total) AS total_sales 
                    FROM sales_payments 
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    $query_sales_tax = "SELECT DATE_FORMAT(created_at, '%b') AS period, SUM(grand_total) AS total_sales, SUM(grand_total) * 0.06 AS tax 
                        FROM sales_payments 
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    $query_profit = "SELECT DATE_FORMAT(sale_datetime, '%b') AS period, SUM(profit) AS profit 
                     FROM profit 
                     GROUP BY DATE_FORMAT(sale_datetime, '%Y-%m')";
} elseif ($filter === 'year') {
    $query_revenue = "SELECT DATE_FORMAT(sale_date, '%Y') AS period, SUM(total_price) AS revenue 
                      FROM sales_transaction 
                      GROUP BY YEAR(sale_date)";
    $query_sales = "SELECT DATE_FORMAT(created_at, '%Y') AS period, SUM(grand_total) AS total_sales 
                    FROM sales_payments 
                    GROUP BY YEAR(created_at)";
    $query_sales_tax = "SELECT DATE_FORMAT(created_at, '%Y') AS period, SUM(grand_total) AS total_sales, SUM(grand_total) * 0.06 AS tax 
                        FROM sales_payments 
                        GROUP BY YEAR(created_at)";
    $query_profit = "SELECT DATE_FORMAT(sale_datetime, '%Y') AS period, SUM(profit) AS profit 
                     FROM profit 
                     GROUP BY YEAR(sale_datetime)";
}

$result_revenue = $conn->query($query_revenue);
if ($result_revenue->num_rows > 0) {
    while ($row = $result_revenue->fetch_assoc()) {
        $data_points_revenue[] = $row;
        $total_revenue += $row['revenue']; // Add revenue to total
    }
}

$result_sales = $conn->query($query_sales);
if ($result_sales->num_rows > 0) {
    while ($row = $result_sales->fetch_assoc()) {
        $data_points_sales[] = $row;
        $total_sales += $row['total_sales']; // Add sales to total
    }
}

$result_sales_tax = $conn->query($query_sales_tax);
if ($result_sales_tax->num_rows > 0) {
    while ($row = $result_sales_tax->fetch_assoc()) {
        $data_points_tax[] = $row;
        $total_tax += $row['tax']; // Add tax to total
    }
}

$result_profit = $conn->query($query_profit);
if ($result_profit->num_rows > 0) {
    while ($row = $result_profit->fetch_assoc()) {
        $data_points_profit[] = $row;
        $total_profit += $row['profit']; // Add profit to total
    }
}

// Initialize variables for most sold and revenue-generating products
$mostSoldProductNames = [];
$mostRevenueProductNames = [];

// Fetch most sold products
$queryMostSold = $conn->prepare("SELECT barcode, SUM(quantity) AS total_quantity
                                 FROM sales_transaction
                                 GROUP BY barcode
                                 ORDER BY total_quantity DESC
                                 LIMIT 5");
$queryMostSold->execute();
$mostSoldProducts = $queryMostSold->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch product names for most sold products
foreach ($mostSoldProducts as $product) {
    $query = $conn->prepare("SELECT CONCAT(brand, ' ', producttype, ' ', variant) AS name 
                             FROM productinventorytable 
                             WHERE barcode = ?");
    $query->bind_param('s', $product['barcode']);
    $query->execute();
    $name = $query->get_result()->fetch_assoc()['name'] ?? 'Unknown Product';
    $mostSoldProductNames[] = ['name' => $name, 'total_quantity' => $product['total_quantity']];
}

// Fetch most revenue-generating products
$queryMostRevenue = $conn->prepare("SELECT barcode, SUM(total_price) AS total_revenue
                                    FROM sales_transaction
                                    GROUP BY barcode
                                    ORDER BY total_revenue DESC
                                    LIMIT 5");
$queryMostRevenue->execute();
$mostRevenueProducts = $queryMostRevenue->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch product names for most revenue-generating products
foreach ($mostRevenueProducts as $product) {
    $query = $conn->prepare("SELECT CONCAT(brand, ' ', producttype, ' ', variant) AS name 
                             FROM productinventorytable 
                             WHERE barcode = ?");
    $query->bind_param('s', $product['barcode']);
    $query->execute();
    $name = $query->get_result()->fetch_assoc()['name'] ?? 'Unknown Product';
    $mostRevenueProductNames[] = ['name' => $name, 'total_revenue' => $product['total_revenue']];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outstanding Revenue, Total Sales & Running Balance</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* General Body Styling */
/* General Body Styling */
/* General Body Styling */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: white; /* Dark background */
    color: #e0e0e0; /* Light text color */
    background: url('darkwallpaper.jpg') no-repeat center center fixed;
    background-size: cover;
}

canvas {
    width: 100% !important;   /* Make sure the canvas fills its container */
    height: 400px;            /* Set height */
    background-color: #333;   /* Set a background color */
    border-radius: 8px;       /* Optional: round corners */
}

/* Container that holds all the elements */
.container {
    width: 90%;
    max-width: 1200px;
    margin-bottom: 30px;
}

/* Cards Container for Layout */
.cards-container {
    display: flex;
    flex-direction: column; /* Keep running balance on top */
    align-items: center;
    width: 100%;
    margin-bottom: 30px;
}

/* Container for Cards in the Same Row */
.cards-row {
    display: flex;
    justify-content: space-between; /* Distribute space evenly */
    width: 80%; /* Adjust width to fit all cards in one row */
    margin-bottom: 30px; /* Space below the row */
    gap: 20px;
}

/* Individual Card Styling */
.card {
    padding: 20px;
    background-color: #333; /* Dark card background */
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12), 0 4px 6px rgba(0, 0, 0, 0.05);
    width: 220px; /* Adjust width to fit side-by-side */
    text-align: center;
    transition: all 0.3s ease;
    color: #e0e0e0; /* Light text for readability */
}

/* Running Balance Card (Main Card) Styling */
.card.running-balance {
    background-color: #1d7a1d; /* Dark green background */
    color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15), 0 4px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px; /* Extra spacing below to separate from other cards */
}

.card.running-balance h3 {
    font-size: 1.6em;
    font-weight: bold;
    color: #fff;
    margin-bottom: 15px;
}

.card.running-balance .running-balance-value {
    font-size: 2em;
    font-weight: 600;
    color: #fff;
}

/* Total Revenue Card Styling */
.card.total-revenue {
    background-color: #1e88e5; /* Blue background */
    color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15), 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card.total-revenue h3 {
    font-size: 1.4em;
    font-weight: 600;
    margin-bottom: 10px;
    color: #fff; /* Light text */
}

.card .total-revenue {
    font-size: 1.5em;
    font-weight: 500;
    color: #fff; /* Lighter color for the stats */
    margin-top: 10px;
}

/* Total Sales Card Styling */
.card.total-sales {
    background-color: teal; /* Orange background */
    color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15), 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card.total-sales h3 {
    font-size: 1.4em;
    font-weight: 600;
    margin-bottom: 10px;
    color: #fff; /* Light text */
}

.card .total-sales {
    font-size: 1.5em;
    font-weight: 500;
    color: #fff; /* Lighter color for the stats */
    margin-top: 10px;
}

/* Tax Card Styling */
.card.tax {
    background-color: darkorange; /* Red background */
    color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15), 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card.tax h3 {
    font-size: 1.4em;
    font-weight: 600;
    margin-bottom: 10px;
    color: #fff; /* Light text */
}

.card .tax {
    font-size: 1.5em;
    font-weight: 500;
    color: #fff; /* Lighter color for the stats */
    margin-top: 10px;
}

/* Profit Card Styling */
.card.profit {
    background-color: seagreen; /* Green background */
    color: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15), 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card.profit h3 {
    font-size: 1.4em;
    font-weight: 600;
    margin-bottom: 10px;
    color: #fff; /* Light text */
}

.card .profit {
    font-size: 1.5em;
    font-weight: 500;
    color: #fff; /* Lighter color for the stats */
    margin-top: 10px;
}

/* Media Queries for Responsive Design */
@media (max-width: 768px) {
    .cards-container {
        flex-direction: column;
        align-items: center;
    }

    .cards-row {
        flex-direction: column; /* Stack cards on smaller screens */
        align-items: center;
        width: 100%;
    }

    .card {
        width: 80%; /* Adjust card width on smaller screens */
        margin-bottom: 15px;
    }
}

/* Hover effect for cards */
.card:hover {
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
    transform: translateY(-5px);
}

/* Styling for arrows or icons */
.arrow {
    font-size: 1.5em;
    display: inline-block;
    margin-left: 10px;
    vertical-align: middle;
}

/* Color Enhancements for Interactive Elements */

/* Filter Select Dropdown Styling */
.filter select {
    padding: 8px 15px;
    width: 20%;
    border-radius: 6px;
    background-color: #333; /* Dark background for select */
    color: white;
    border: none;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Filter Select Hover Effect */
.filter select:hover {
    background-color: #444; /* Slightly lighter on hover */
}

/* Filter Select Focus Effect */
.filter select:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(45, 135, 240, 0.5);
}

/* Button Styling */
button {
    padding: 8px 15px;
    width: 20%;
    border-radius: 6px;
    background-color: #333; /* Dark button background */
    color: white;
    border: none;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Button Hover Effect */
button:hover {
    background-color: #444; /* Slightly lighter on hover */
}

/* Button Focus Effect */
button:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(45, 135, 240, 0.5);
}

/* Border radius for input fields and select dropdowns */
input, select {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #555; /* Lighter border color */
    font-size: 1em;
    width: 100%;
    margin-top: 5px;
    background-color: #333; /* Dark background for inputs */
    color: #fff; /* White text in inputs */
}

/* Sidebar Styling */
#sidebar {
    height: 100%;
    width: 250px;
    position: fixed;
    z-index: 1;
    top: 0;
    left: -250px; /* Initially hidden */
    background-color: #2c3e50; /* Dark sidebar background */
    transition: 0.3s;
    padding-top: 60px;
}

#sidebar a {
    padding: 15px 20px;
    text-decoration: none;
    font-size: 18px;
    color: white;
    display: block;
    transition: 0.3s;
}

#sidebar a:hover {
    background-color: #34495e; /* Lighter hover effect */
}



</style>
</head>
<body>


<div id="sidebar">
    <a href="accdashboard.php">Dashboard</a>
    <a href="accexpensesform.php">Expenses Form</a>
    <a href="acctaxreport.php">Tax Report Table</a>
    <a href="accexpensesapproval.php">Expenses Approval</a>
    <a href="#logout">Logout</a>
</div>

<div class="container">
<div class="cards-container">
    <!-- Running Balance Card at the top -->
    <div class="card running-balance">
        <h3>Account Status</h3>
        <div class="running-balance-value">
            <?php echo 'RM' . number_format($running_balance, 2); ?>
        </div>
    </div>

    <!-- Cards Row for Revenue, Sales, Profit, and Tax -->
    <div class="cards-row" >
        <div class="card total-revenue">
            <h3>Net Sales</h3>
            <div class="total-revenue" >
                <?php echo 'RM' . number_format($total_revenue, 2); ?>
            </div>
        </div>

        <div class="card total-sales">
            <h3>Net Revenue</h3>
            <div class="total-sales">
                <?php echo 'RM' . number_format($total_sales, 2); ?>
            </div>
        </div>

        <div class="card tax">
            <h3>Total Tax (6%)</h3>
            <div class="tax">
                <?php echo 'RM' . number_format($total_tax, 2); ?>
            </div>
        </div>

        <div class="card profit">
            <h3>Profit Made</h3>
            <div class="profit">
                <?php echo 'RM' . number_format($total_profit, 2); ?>
            </div>
        </div>
    </div>
</div>


    <!-- Filter Form for Day, Month, Year -->
    <div class="filter">
        <form action="" method="GET">
            <label for="filter" style="color: ghostwhite; font-size: 1.2em; margin-right: 10px;">Select Filter: </label>
            <select name="filter" id="filter" style="padding: 10px 15px; width: 200px; border-radius: 6px; background-color: #333; color: #E0E0E0; border: 1px solid #555; font-size: 1.1em; transition: background-color 0.3s ease, border-color 0.3s ease;">
                <option value="day" <?= $filter == 'day' ? 'selected' : ''; ?>>Day</option>
                <option value="month" <?= $filter == 'month' ? 'selected' : ''; ?>>Month</option>
                <option value="year" <?= $filter == 'year' ? 'selected' : ''; ?>>Year</option>
            </select>
            <br><br>
            <button type="submit" style="padding: 10px 20px; background-color: #2980b9; color: #fff; border: none; border-radius: 6px; font-size: 1.1em; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; margin-top: 10px;">
                Apply Filter
            </button>
        </form>
        <script>
            // Hover effect for select dropdown
            document.getElementById("filter").addEventListener("mouseover", function() {
                this.style.backgroundColor = "#444";
                this.style.borderColor = "#888";
            });
            document.getElementById("filter").addEventListener("mouseout", function() {
                this.style.backgroundColor = "#333";
                this.style.borderColor = "#555";
            });

            // Focus effect for select dropdown
            document.getElementById("filter").addEventListener("focus", function() {
                this.style.boxShadow = "0 0 5px rgba(255, 255, 255, 0.6)";
            });
            document.getElementById("filter").addEventListener("blur", function() {
                this.style.boxShadow = "none";
            });

                // Button hover effect
            document.querySelector("button").addEventListener("mouseover", function() {
                this.style.backgroundColor = "#3498db";
                this.style.transform = "scale(1.05)";
            });
            document.querySelector("button").addEventListener("mouseout", function() {
                this.style.backgroundColor = "#2980b9";
                this.style.transform = "scale(1)";
            });

            // Button focus effect
            document.querySelector("button").addEventListener("focus", function() {
                this.style.boxShadow = "0 0 5px rgba(255, 255, 255, 0.6)";
            });
            document.querySelector("button").addEventListener("blur", function() {
                this.style.boxShadow = "none";
            });
        </script>
    </div>

    
    <!-- Net Sales -->
    <h3>Net Sales</h3>
    <canvas id="revenueChart" ></canvas>
    <h3>Net Revenue</h3>
    <canvas id="salesChart"></canvas>
    <h3>Sales Tax</h3>
    <canvas id="taxChart"></canvas>
    <h3>Profit Made</h3>
    <canvas id="profitChart"></canvas> <!-- Profit Chart -->

    <!-- Most Sold Products Chart -->
    <h3>Most Sold Products</h3>
    <canvas id="mostSoldChart"></canvas>

    <!-- Most Revenue-Generating Products Chart -->
    <h3>Most Revenue-Generating Products</h3>
    <canvas id="mostRevenueChart"></canvas>

</div>

<script>
    // Prepare data for Revenue chart
    const labelsRevenue = <?= json_encode(array_column($data_points_revenue, 'period')) ?>;
    const dataRevenue = <?= json_encode(array_column($data_points_revenue, 'revenue')) ?>;

    // Prepare data for Sales chart
    const labelsSales = <?= json_encode(array_column($data_points_sales, 'period')) ?>;
    const dataSales = <?= json_encode(array_column($data_points_sales, 'total_sales')) ?>;

    // Prepare data for Tax chart
    const labelsTax = <?= json_encode(array_column($data_points_tax, 'period')) ?>;
    const dataTax = <?= json_encode(array_column($data_points_tax, 'tax')) ?>;

    // Prepare data for Profit chart
    const labelsProfit = <?= json_encode(array_column($data_points_profit, 'period')) ?>;
    const dataProfit = <?= json_encode(array_column($data_points_profit, 'profit')) ?>;

    // Prepare data for Most Sold Products
    const mostSoldLabels = <?= json_encode(array_column($mostSoldProductNames, 'name')) ?>;
    const mostSoldData = <?= json_encode(array_column($mostSoldProductNames, 'total_quantity')) ?>;

    // Prepare data for Most Revenue-Generating Products
    const mostRevenueLabels = <?= json_encode(array_column($mostRevenueProductNames, 'name')) ?>;
    const mostRevenueData = <?= json_encode(array_column($mostRevenueProductNames, 'total_revenue')) ?>;

// Create Revenue Chart
// Create Revenue Chart
new Chart(document.getElementById("revenueChart"), {
    type: 'line',
    data: {
        labels: labelsRevenue,
        datasets: [{
            label: 'Outstanding Revenue',
            data: dataRevenue,
            borderColor: '#ffffff',  // White border for contrast
            backgroundColor: 'rgba(255, 255, 255, 0.3)', // Light white fill
            fill: true,
            tension: 0.1,
            pointRadius: 6,  // Increase this value to make dots larger
            pointBackgroundColor: '#ffffff',  // White dots for contrast
            pointBorderColor: '#ffffff'  // White border for the dots
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            },
            y: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});

// Create Sales Chart
new Chart(document.getElementById("salesChart"), {
    type: 'line',
    data: {
        labels: labelsSales,
        datasets: [{
            label: 'Total Sales',
            data: dataSales,
            borderColor: '#1cc88a', // Bright green for visibility
            backgroundColor: 'rgba(28, 200, 138, 0.3)', // Light green fill
            fill: true,
            tension: 0.1,
            pointRadius: 6,  // Increase this value to make dots larger
            pointBackgroundColor: '#1cc88a',  // Green dots for contrast
            pointBorderColor: '#1cc88a'  // Green border for the dots
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            },
            y: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});

// Create Tax Chart
new Chart(document.getElementById("taxChart"), {
    type: 'line',
    data: {
        labels: labelsTax,
        datasets: [{
            label: 'Total Tax (6%)',
            data: dataTax,
            borderColor: '#f6c23e',  // Yellow for visibility
            backgroundColor: 'rgba(246, 194, 62, 0.3)', // Light yellow fill
            fill: true,
            tension: 0.1,
            pointRadius: 6,  // Increase this value to make dots larger
            pointBackgroundColor: '#f6c23e',  // Yellow dots for contrast
            pointBorderColor: '#f6c23e'  // Yellow border for the dots
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            },
            y: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});

// Create Profit Chart
new Chart(document.getElementById("profitChart"), {
    type: 'line',
    data: {
        labels: labelsProfit,
        datasets: [{
            label: 'Profit Made',
            data: dataProfit,
            borderColor: '#e74a3b',  // Red for contrast
            backgroundColor: 'rgba(231, 74, 59, 0.3)', // Light red fill
            fill: true,
            tension: 0.1,
            pointRadius: 6,  // Increase this value to make dots larger
            pointBackgroundColor: '#e74a3b',  // Red dots for contrast
            pointBorderColor: '#e74a3b'  // Red border for the dots
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            },
            y: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});

// Create Most Sold Products Chart
new Chart(document.getElementById("mostSoldChart"), {
    type: 'bar',
    data: {
        labels: mostSoldLabels,
        datasets: [{
            label: 'Most Sold Products',
            data: mostSoldData,
            backgroundColor: '#4e73df', // Blue for visibility
            borderColor: '#4e73df',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            },
            y: {
                grid: {
                    color: '#888'
                },
                ticks: {
                    color: '#fff'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});



// Create Most Revenue-Generating Products Chart
new Chart(document.getElementById("mostRevenueChart"), {
    type: 'bar',
    data: {
        labels: mostRevenueLabels,
        datasets: [{
            label: 'Most Revenue-Generating Products',
            data: mostRevenueData,
            backgroundColor: '#f6c23e', // Yellow color
            borderColor: '#f6c23e',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                grid: {
                    color: '#555'
                },
                ticks: {
                    color: '#ccc'
                }
            },
            y: {
                grid: {
                    color: '#555'
                },
                ticks: {
                    color: '#ccc'
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff'
                }
            }
        }
    }
});



        // JavaScript for auto-showing sidebar on hover
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");

    // Show sidebar when mouse moves near the left edge
    document.body.addEventListener("mousemove", (event) => {
        if (event.clientX < 50) { // Cursor near the left edge
            sidebar.style.left = "0";
            mainContent.style.marginLeft = "250px";
        }
    });

    // Hide sidebar when mouse leaves it
    sidebar.addEventListener("mouseleave", () => {
        sidebar.style.left = "-250px";
        mainContent.style.marginLeft = "0";
    });

</script>

</body>
</html>
