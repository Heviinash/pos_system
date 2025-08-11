<?php
require_once('tcpdf/tcpdf.php'); // Include the TCPDF library

session_start();

// Use the updated terms, or fall back to the default
$terms = isset($_SESSION['terms']) ? $_SESSION['terms'] : 'Validity: This quotation is valid for 30 days from the date issued.
1. Payment: Payment is due within 30 days of invoice date.
2. Delivery: Delivery dates are estimates and may vary.
3. Acceptance: Please sign and return this quotation to confirm acceptance.
4. Warranty: All products come with a one-year warranty against defects';

// Database connection
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "pos"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$business_query = "SELECT * FROM businessregistration LIMIT 1"; // Assuming you only need one logo
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

// Fetch associated products with prices directly from testcustomer_products
$product_query = "SELECT cp.product_needed, cp.quantity, cp.price 
                  FROM testcustomer_products cp 
                  WHERE cp.log_id = ?";
$stmt_product = $conn->prepare($product_query);
$stmt_product->bind_param("i", $log_id);
$stmt_product->execute();
$product_result = $stmt_product->get_result();
$products_with_prices = $product_result->fetch_all(MYSQLI_ASSOC);

// Fetch associated services with prices from the services table
$service_query = "SELECT cs.service_needed, s.price 
                  FROM testcustomer_services cs 
                  JOIN services s ON cs.service_needed = s.service_name 
                  WHERE cs.log_id = ?";
$stmt_service = $conn->prepare($service_query);
$stmt_service->bind_param("i", $log_id);
$stmt_service->execute();
$service_result = $stmt_service->get_result();
$services = $service_result->fetch_all(MYSQLI_ASSOC);


// Customization for font family and font size

$font_query = "SELECT font_family, font_size FROM pdf_customization_settings LIMIT 1";
$stmt_font = $conn->prepare($font_query);
$stmt_font->execute();
$font_settings = $stmt_font->get_result()->fetch_assoc();




// Customization for company info position

$position_query = "SELECT company_info_position FROM pdf_customization_settings LIMIT 1";
$stmt_position = $conn->prepare($position_query);
$stmt_position->execute();
$position_result = $stmt_position->get_result()->fetch_assoc();



// Customization for customer info position

$sender_position_query = "SELECT sender_info_position FROM pdf_customization_settings LIMIT 1";
$stmt_sender_position = $conn->prepare($sender_position_query);
$stmt_sender_position->execute();
$sender_position_result = $stmt_sender_position->get_result()->fetch_assoc();



// Customization for header text 
$header_text_query = "SELECT header_text FROM pdf_customization_settings LIMIT 1";
$stmt_header_text = $conn->prepare($header_text_query);
$stmt_header_text->execute();
$header_text_result = $stmt_header_text->get_result()->fetch_assoc();


// Customization for watermark text 
$watermark_query = "SELECT watermark_text FROM pdf_customization_settings LIMIT 1";
$stmt_watermark = $conn->prepare($watermark_query);
$stmt_watermark->execute();
$watermark_result = $stmt_watermark->get_result()->fetch_assoc();




// Close the statements and connection
$stmt_log->close();
$stmt_product->close();
$stmt_service->close();


$stmt_font->close();

$stmt_position->close();

$stmt_sender_position->close();

$stmt_header_text->close();

$stmt_watermark->close();

$conn->close();


// Initialize variables for calculating totals
$total_product_price = 0;
$total_service_price = 0;

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nash Enterprise');
$pdf->SetTitle('Quotation');
$pdf->SetSubject('Quotation Details');
$pdf->SetKeywords('TCPDF, PDF, quotation, test');






// Customization for header text
$header_text = isset($header_text_result['header_text']) ? $header_text_result['header_text'] : 'Quotation';

$watermark_text = $watermark_result['watermark_text'];




// Set default header data
$pdf->SetHeaderData('', 0, $header_text, 'Heviinash Parugavelu');





// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins (adjust these values if needed)
$pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT); // Adjust margins as necessary
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
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





// Customization for font family and font size

// Set font family and size
$font_family = isset($font_settings['font_family']) ? $font_settings['font_family'] : 'helvetica'; // Default to 'helvetica' if not set
$font_size = isset($font_settings['font_size']) ? (int)$font_settings['font_size'] : 10; // Default to 10 if not set

// Set the font dynamically based on the values fetched from the database
$pdf->SetFont($font_family, '', $font_size);



// Set the Y position to a smaller value to move content upwards
$pdf->SetY(4); // Adjust this value as needed to control the vertical position of content



// Customization for company info

$company_info_position = isset($position_result['company_info_position']) ? $position_result['company_info_position'] : 'left';


// Customization for sender info
$sender_info_position = isset($sender_position_result['sender_info_position']) ? $sender_position_result['sender_info_position'] : 'left';






$html = <<<EOD
<style>
    body {
        font-family: helvetica;
        color: #333;
    }
    h1 {
        color: #007BFF;
        text-align: center;
    }
    h2 {
        color: #0056b3;
        border-bottom: 1px solid #007BFF;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th {
        background-color: #007BFF;
        color: white;
        padding: 8px;
        text-align: left;
    }
    td {
        padding: 8px;
        border: 1px solid #dddddd;
    }
    .total {
        font-weight: bold;
        color: #FF5733; /* Red for total */
    }


    .terms {
        font-size: 8px; /* Smaller font size for terms */
        color: #777; /* Lighter color for terms */
        line-height: 1.5; /* Improve readability */
    }

</style>
EOD;

// Add space for the logo
$pdf->Ln(10); 

// Get the logo path without leading slashes
$logo_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/DevelopmentProject/' . ltrim($business_info['companylogo'], '/');

// Log the logo path for debugging
error_log("Checking logo at: " . $logo_path);

// Check if the logo file exists
if (file_exists($logo_path)) {
    // Set desired dimensions
    $logo_width = 35; // Desired width
    $logo_height = 30; // Fixed height for testing (adjust if necessary)

    $x_position = ($pdf->getPageWidth() - $logo_width) / 2; // Centering the logo
    $y_position = 10; // Adjust this value as needed to move the logo down

    // Display the logo in the PDF at the center
    $pdf->Image($logo_path, $x_position, $y_position, $logo_width, $logo_height, '', '', '', false, 300, '', false, false, 0, false, false, false);
} else {
    // Display a message if the logo is not found, including the logo path
    $pdf->Cell(0, 10, 'Logo not found: ' . htmlspecialchars($logo_path), 0, 1, 'C');
}

// Add company info and sender info
$html .= '<div class="flex-container">'; // Start of flex container


// Add space below the logo
$pdf->Ln(7); // Adjust to position sender and receiver sections


// Company Info Customize
 
// Build the HTML content dynamically based on the position
$html .= '<div class="company-info" style="text-align: ' . htmlspecialchars($company_info_position) . ';">'; 
$html .= '<h2>Company Info</h2>';
$html .= '<p>' . htmlspecialchars($business_info['business_name']) . '</p>';
$html .= '<p>' . htmlspecialchars($business_info['address']) . '</p>';
$html .= '<p>' . htmlspecialchars($business_info['contact_number']) . '</p>';
$html .= '<p>' . htmlspecialchars($business_info['email_address']) . '</p>';
$html .= '</div>'; // Close company info




// Build the HTML content dynamically based on the position for Sender Info (Customer Info)
$html .= '<div class="sender-info" style="text-align: ' . htmlspecialchars($sender_info_position) . ';">'; 
$html .= '<h2>Customer Info</h2>';
$html .= '<p><strong>Name:</strong> ' . htmlspecialchars($customer_log['name']) . '</p>';
$html .= '<p><strong>Contact Info:</strong> ' . htmlspecialchars($customer_log['contact_info']) . '</p>';
$html .= '</div>'; // Close sender info



// Start the main content without extra whitespace
$html .= '<h1>Quotation</h1>';
$html .= '<p><strong>Problem Description:</strong> ' . nl2br(htmlspecialchars($customer_log['problem_desc'])) . '</p>';
$html .= '<p><strong>Follow-up Date:</strong> ' . htmlspecialchars($customer_log['followupdate']) . '</p>';

// Add products needed with prices
$html .= '<h2>Products Needed</h2>';
$html .= '<table><thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
if (!empty($products_with_prices)) {
    foreach ($products_with_prices as $product) {
        $product_total = $product['quantity'] * $product['price']; // Calculate total for each product
        $total_product_price += $product_total; // Add to total product price

        $html .= '<tr>';
        $html .= '<td style="text-align: center;">' . htmlspecialchars($product['product_needed']) . '</td>'; // Original product name
        $html .= '<td style="text-align: center;">' . htmlspecialchars($product['quantity']) . '</td>';
        $html .= '<td style="text-align: center;">' . htmlspecialchars(number_format($product['price'], 2)) . '</td>';
        $html .= '<td style="text-align: center;">' . htmlspecialchars(number_format($product_total, 2)) . '</td>'; // Display total for each product
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4">No products needed.</td></tr>';
}
$html .= '</tbody></table>';

// Add services needed with prices
$html .= '<h2>Services Needed</h2>';
$html .= '<table><thead><tr><th>Service</th><th>Price</th></tr></thead><tbody>';
if (!empty($services)) {
    foreach ($services as $service) {
        $total_service_price += $service['price']; // Add to total service price

        $html .= '<tr><td>' . htmlspecialchars($service['service_needed']) . '</td>';
        $html .= '<td>' . htmlspecialchars(number_format($service['price'], 2)) . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="2">No services needed.</td></tr>';
}
$html .= '</tbody></table>';

// Calculate Grand Total
$grand_total = $total_product_price + $total_service_price;

// Display Grand Total
$html .= '<h2>Grand Total</h2>';
$html .= '<p class="total"><strong>Total Product Price: RM</strong> ' . number_format($total_product_price, 2) . '</p>';
$html .= '<p class="total"><strong>Total Service Price: RM</strong> ' . number_format($total_service_price, 2) . '</p>';
$html .= '<p class="total"><strong>Grand Total Price: RM</strong> ' . number_format($grand_total, 2) . '</p>';


// Add Terms and Conditions heading with styles
$html .= '<h2>Terms and Conditions</h2>';
$html .= '<div class="terms">' . nl2br(htmlspecialchars($terms)) . '</div>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


$customer_name = htmlspecialchars($customer_log['name']); // Get the customer's name
$customer_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $customer_name); // Replace non-alphanumeric characters with underscores

// 2. Get the current timestamp
$timestamp = date('Y-m-d_H-i-s'); // Format: Year-Month-Day_Hour-Minute-Second

// 3. Construct the filename
$pdf_filename = "quotation_{$customer_name}_{$timestamp}.pdf"; // Create a dynamic filename

// 4. Use the constructed filename in the Output method
$pdf->Output($pdf_filename, 'D'); // Output the PDF with the dynamic name

?>