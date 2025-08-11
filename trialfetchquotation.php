<?php
if (isset($_POST['log_id'])) {
    $log_id = $_POST['log_id'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'pos');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Fetch quotation based on log_id
    $sql = "SELECT products, services FROM quotation WHERE log_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $log_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $products = json_decode($row['products'], true);
        $services = json_decode($row['services'], true);

        // Process products to add barcode based on product name
        foreach ($products as &$product) {
            $product_name = $product['product_needed'];  // Assuming 'product_needed' has the product name

            $productQuery = "SELECT barcode FROM productinventorytable WHERE CONCAT(brand, ' ', producttype, ' ', variant) = ?";
            $productStmt = $conn->prepare($productQuery);
            $productStmt->bind_param('s', $product_name);
            $productStmt->execute();
            $productResult = $productStmt->get_result();

            if ($productResult->num_rows > 0) {
                $productRow = $productResult->fetch_assoc();
                $product['barcode'] = $productRow['barcode'];  // Add barcode to the product array
            } else {
                $product['barcode'] = null;  // If no matching barcode found
            }

            $productStmt->close();
        }

        // Process services to add service_id based on service_name
        foreach ($services as &$service) {
            $service_name = $service['service_needed'];

            $serviceQuery = "SELECT service_id FROM services WHERE service_name = ?";
            $serviceStmt = $conn->prepare($serviceQuery);
            $serviceStmt->bind_param('s', $service_name);
            $serviceStmt->execute();
            $serviceResult = $serviceStmt->get_result();

            if ($serviceResult->num_rows > 0) {
                $serviceRow = $serviceResult->fetch_assoc();
                $service['service_id'] = $serviceRow['service_id'];  // Add service_id to the service array
            } else {
                $service['service_id'] = null;  // If no matching service_id found
            }

            $serviceStmt->close();
        }

        // Respond with both products and services, now with barcode or service_id included
        echo json_encode([
            'success' => true,
            'products' => $products,
            'services' => $services
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No quotation found for this Log ID']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Log ID not provided']);
}
?>
