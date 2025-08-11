<?php

session_start();

require_once('tcpdf/tcpdf.php'); // Include the TCPDF library

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "pos"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$business_query = "SELECT * FROM businessregistration LIMIT 1";
$business_stmt = $conn->prepare($business_query);
$business_stmt->execute();
$business_info = $business_stmt->get_result()->fetch_assoc();

// Get log_id from the URL
$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;

// Fetch customer log details
$log_query = "SELECT * FROM testcustomer_logs WHERE log_id = ?";
$stmt_log = $conn->prepare($log_query);
$stmt_log->bind_param("i", $log_id);
$stmt_log->execute();
$log_result = $stmt_log->get_result();
$customer_log = $log_result->fetch_assoc();

// Fetch associated products with price
$product_query = "SELECT product_needed, quantity, price FROM testcustomer_products WHERE log_id = ?";
$stmt_product = $conn->prepare($product_query);
$stmt_product->bind_param("i", $log_id);
$stmt_product->execute();
$product_result = $stmt_product->get_result();
$products = $product_result->fetch_all(MYSQLI_ASSOC);

// Fetch associated services with price
$service_query = "
    SELECT s.service_needed, srv.price 
    FROM testcustomer_services s 
    LEFT JOIN services srv ON s.service_needed = srv.service_name 
    WHERE s.log_id = ?";
$stmt_service = $conn->prepare($service_query);
$stmt_service->bind_param("i", $log_id);
$stmt_service->execute();
$service_result = $stmt_service->get_result();
$services = $service_result->fetch_all(MYSQLI_ASSOC);






$font_query = "SELECT font_family, font_size FROM quotationpdf_settings LIMIT 1";
$stmt_font = $conn->prepare($font_query);
$stmt_font->execute();
$font_settings = $stmt_font->get_result()->fetch_assoc();
$stmt_font->close();





$query = "SELECT sender_info_position FROM quotationpdf_settings LIMIT 1"; // You can adjust the query if you need more conditions
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $sender_info_position = $row['sender_info_position'];
} else {
    // Default to 'left' if there's no setting found
    $sender_info_position = 'left';
}


// Fetch watermark text
$watermark_query = "SELECT watermark_text FROM quotationpdf_settings LIMIT 1";
$stmt_watermark = $conn->prepare($watermark_query);
$stmt_watermark->execute();
$watermark_result = $stmt_watermark->get_result()->fetch_assoc();
$stmt_watermark->close();


// Check if watermark result exists
$watermark_text = isset($watermark_result['watermark_text']) ? $watermark_result['watermark_text'] : ''; // Default to empty if not set

// Now you can safely use $watermark_text





// Close the statements
$stmt_log->close();
$stmt_product->close();
$stmt_service->close();

// Initialize total variables
$total_product_price = 0;
$total_service_price = 0;

// Calculate total product price
foreach ($products as $product) {
    $total_product_price += $product['quantity'] * $product['price'];
}

// Calculate total service price
foreach ($services as $service) {
    $total_service_price += $service['price'];
}

// Calculate grand total
$grand_total = $total_product_price + $total_service_price;

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 50); // Set font size and bold for watermark-like text

$pdf->SetTextColor(192, 192, 192); // Lighter gray color for watermark text

// This would look like a very light watermark (you can adjust the RGB values to make it even lighter or darker)
$pdf->SetTextColor(240, 240, 240); // Lighter gray, making it look more faded

// Adjust the Y-position to move the watermark up
$pdf->SetXY(55, 90); // Move the watermark 20 units up (adjust 130 to your preferred value)

$pdf->Text(60, 90, $watermark_text); // Add the watermark-like text at the new position

// Set font for regular content
$pdf->SetFont('helvetica', '', 12); // Set font for regular content
$pdf->SetTextColor(0, 0, 0); // Set regular text color to black





// Set font family and size
$font_family = isset($font_settings['font_family']) ? $font_settings['font_family'] : 'helvetica'; // Default to 'helvetica' if not set
$font_size = isset($font_settings['font_size']) ? (int)$font_settings['font_size'] : 10; // Default to 10 if not set

// Set the font dynamically based on the values fetched from the database
$pdf->SetFont($font_family, '', $font_size);



$pdf->SetFont('Helvetica', 'B', 20); // Set font to bold and size 20
$pdf->SetY(0); // Set Y position for the title
$pdf->Ln(15);
$pdf->Cell(0, 15, 'QUOTATION', 0, 1, 'C'); // Centered title
$pdf->Ln(2); // Add a little space after the title


// Move the cursor down after the logo

$invoice_number = 'QUO-' . date('Ymd') . '-' . strtoupper(uniqid());

// Display the invoice number in the PDF
$pdf->SetFont($font_family, 'B', $font_size);
$pdf->Cell(0, 10, 'Quotation Number: ' . $invoice_number, 0, 1);

$pdf->SetFont($font_family, 'B', $font_size);   
$pdf->Cell(0, 10, 'Date: ' . date('d-m-Y'), 0, 1);

// Add spacing as needed before other content
$pdf->Ln(10);



$pdf->SetFont($font_family, 'B', $font_size);
$pdf->Cell(100, 10, 'Issued To:', 0, 1); // Customer Details Label

// Customer Details Section
$pdf->SetFont($font_family, '', $font_size);
$pdf->MultiCell(100, 10, 
    'Customer Name: ' . htmlspecialchars($customer_log['name']) . "\n" .
    'Customer ID: ' . htmlspecialchars($customer_log['icnumber']) . "\n" .
    'Contact Info: ' . htmlspecialchars($customer_log['contact_info']) . "\n" .
    'Follow Up Date: ' . htmlspecialchars($customer_log['followupdate']),
    0, 'L'
);

$pdf->Ln(4);


$y_position = 100; // Set default Y position for all positions
// Company is the sender in this situation

// Determine X position based on alignment
if ($sender_info_position == 'left') {
    $x_position = 10; // Left side
} elseif ($sender_info_position == 'center') {
    $x_position = ($pdf->GetPageWidth() - 40) / 2; // Centered
} elseif ($sender_info_position == 'right') {
    $x_position = $pdf->GetPageWidth() - 80; // Right side
} elseif ($sender_info_position == 'topright') {
    $x_position = $pdf->GetPageWidth() - 80; // Right side
    $y_position = 60; // Define Y position only for "topright"
} else {
    $x_position = 10; // Default to left if no valid position found
}

// Move to the calculated X position
$pdf->SetX($x_position);

// Conditionally set Y position only for "topright"
if ($sender_info_position == 'topright') {
    $pdf->SetY($y_position); // Set Y for topright
}


$pdf->SetXY($x_position, $y_position);

// Display "Pay To:" label with adjusted position
$pdf->SetFont($font_family, 'B', $font_size);
$pdf->Cell(100, 10, 'Pay To:', 0, 1);

// Set font for company details
$pdf->SetFont($font_family, '', $font_size);

// Reset X position for MultiCell to align properly
$pdf->SetX($x_position);
$pdf->MultiCell(100, 10, 
    htmlspecialchars($business_info['business_name']) . "\n" . 
    htmlspecialchars($business_info['bank']) . "\n" . 
    htmlspecialchars($business_info['account_number']) . "\n" . 
    'Contact: ' . htmlspecialchars($business_info['contact_number']) . "\n" . 
    'Email: ' . htmlspecialchars($business_info['email_address']),
    0, 'L' // Keep left alignment for consistent text flow within MultiCell
);

// Space after customer and company details


$pdf->Ln(10);

// Products Section
$pdf->SetFont($font_family, '', $font_size);
//$pdf->SetFillColor(200, 220, 255); // Set fill color for header
$pdf->SetFillColor(255, 255, 255); // Light gray
$pdf->Cell(80, 10, 'Product', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Qty', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Price (RM)', 1, 1, 'C', 1);

$pdf->SetFont($font_family, '', $font_size);
foreach ($products as $product) {
    // Get the current Y position before MultiCell
    $y_before = $pdf->GetY();

    // Use MultiCell for the product name to enable word wrap
    $pdf->MultiCell(80, 10, htmlspecialchars($product['product_needed']), 1, 'L', 0);

    // Get the current Y position after MultiCell and calculate the height of the MultiCell
    $y_after = $pdf->GetY();
    $height = $y_after - $y_before;

    // Set the Y position for the next cells (Quantity and Price) below the MultiCell content
    $pdf->SetY($y_before);

    // Move the cursor down by the height of the MultiCell
    $pdf->Cell(80, $height, '', 0); // Empty cell to match the height of MultiCell

    // Now, print the Quantity and Price on the same line
    $pdf->Cell(40, $height, htmlspecialchars($product['quantity']), 1, 0, 'C');
    $pdf->Cell(40, $height, number_format($product['quantity'] * $product['price'], 2), 1, 0, 'C');

    // Move to the next line
    $pdf->Ln();
}

// Services Section
$pdf->Ln(10);
$pdf->SetFont($font_family, '', $font_size);
$pdf->Cell(80, 10, 'Service', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Price (RM)', 1, 1, 'C', 1);

$pdf->SetFont($font_family, '', $font_size);
foreach ($services as $service) {
    $pdf->Cell(80, 10, htmlspecialchars($service['service_needed']), 1);
    $pdf->Cell(40, 10, number_format($service['price'], 2), 1, 0, 'C');
    $pdf->Ln();
}

// Totals Section
$pdf->Ln(10);
$pdf->SetFont($font_family, '', $font_size);
$pdf->Cell(120, 10, 'Total Product Price', 1);
$pdf->Cell(40, 10, 'RM ' . number_format($total_product_price, 2), 1);
$pdf->Ln();
$pdf->Cell(120, 10, 'Total Service Price', 1);
$pdf->Cell(40, 10, 'RM ' . number_format($total_service_price, 2), 1);
$pdf->Ln();
$pdf->SetFont($font_family, 'B', $font_size);
$pdf->Cell(120, 10, 'Grand Total', 1);
$pdf->Cell(40, 10, 'RM ' . number_format($grand_total, 2), 1);

// Add terms and conditions
$pdf->Ln(10); 
$pdf->SetTextColor(169, 169, 169); // Light gray color (RGB: 169, 169, 169)
$pdf->SetFont('Helvetica', '', 8); // Set font to light and size 10
$pdf->Cell(0, 10, 'Terms and Conditions', 0, 1); // Title in regular font
$pdf->SetFont('Helvetica', '', 7); // Set italic font and smaller size for terms


// Use terms from session or default
// Use the updated terms, or fall back to the default
$terms = isset($_SESSION['terms']) ? $_SESSION['terms'] : 'Validity: This quotation is valid for 30 days from the date issued.
1. Payment: Payment is due within 30 days of invoice date.
2. Delivery: Delivery dates are estimates and may vary.
3. Acceptance: Please sign and return this quotation to confirm acceptance.
4. Warranty: All products come with a one-year warranty against defects';

$pdf->MultiCell(0, 8, $terms, 0, 'L'); // Adjust line height for better spacing

$pdf->SetTextColor(0, 0, 0);

// Output the PDF
$customer_name = htmlspecialchars($customer_log['name']); 
$customer_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $customer_name);
$timestamp = date('Y-m-d_H-i-s'); 
$pdf_filename = "quotation_{$customer_name}_{$timestamp}.pdf"; 
$pdf->Output($pdf_filename, 'D'); 

$conn->close();

?>
