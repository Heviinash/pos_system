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
