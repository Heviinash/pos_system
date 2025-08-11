<?php
// getChartData.php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'pos'; 
$user = 'root';     
$pass = ''; 

$period = $_GET['period'] ?? 'monthly'; // Default to monthly if not set

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $profits = [];
    $salesData = [];

    // Adjust the query based on the period
    switch ($period) {
        case 'daily':
            $dateFormat = '%Y-%m-%d';
            break;
        case 'weekly':
            $dateFormat = '%Y-%u'; // Week of the year
            break;
        case 'monthly':
            $dateFormat = '%Y-%m'; // Month
            break;
        case 'yearly':
            $dateFormat = '%Y'; // Year
            break;
        default:
            $dateFormat = '%Y-%m';
    }

    // Fetch profit and sales based on the selected period
    $query = $pdo->prepare("
        SELECT SUM(profit) AS total_profit, DATE_FORMAT(sale_datetime, '$dateFormat') AS period
        FROM profit
        GROUP BY period
        ORDER BY period
    ");
    $query->execute();
    $profits = $query->fetchAll(PDO::FETCH_ASSOC);

    $query = $pdo->prepare("
        SELECT SUM(total_price) AS total_sales, DATE_FORMAT(sale_date, '$dateFormat') AS period
        FROM sales_transaction
        GROUP BY period
        ORDER BY period
    ");
    $query->execute();
    $salesData = $query->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response data
    $response = [
        'profits' => $profits,
        'sales' => $salesData
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
