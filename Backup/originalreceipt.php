<?php
require_once('tcpdf/tcpdf.php'); // Include TCPDF library

function generatePDFReceipt($conn, $transaction_id) {
    // Step 1: Fetch transaction details from sales_transaction table, including IC number
    $query = "SELECT barcode, quantity, total_price, icnumber FROM sales_transaction WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the first row to get the IC number
    $transaction_details = $result->fetch_assoc();
    if (!$transaction_details) {
        echo "Transaction not found.";
        return;
    }
    $icnumber = $transaction_details['icnumber'];

    // Fetch customer name based on IC number
    $customer_name_query = "SELECT name FROM customerregistration WHERE icnumber = ?";
    $customer_stmt = $conn->prepare($customer_name_query);
    $customer_stmt->bind_param("s", $icnumber);
    $customer_stmt->execute();
    $customer_stmt->store_result(); // Store result to avoid out of sync
    $customer_stmt->bind_result($customer_name);
    $customer_stmt->fetch();
    $customer_stmt->close();

    // Fetch business information
    $business_query = "SELECT * FROM businessregistration LIMIT 1";
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->execute();
    $business_info = $business_stmt->get_result()->fetch_assoc();
    $business_stmt->close();

    // Create a new PDF document
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Receipt for Transaction ' . $transaction_id);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    // Company Details Section

    // Prepare the logo path
    $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/DevelopmentProject/' . $business_info['companylogo'];

    // Check if the logo file exists
    if (file_exists($logo_path)) {
        // Set desired dimensions for the logo (smaller size for receipt)
        $logo_width = 30; // Desired width for receipt
        $logo_height = 30; // Fixed height for receipt

        // Centering the logo horizontally
        $x_position = ($pdf->getPageWidth() - $logo_width) / 2; 
        $y_position = $pdf->GetY(); // Get current Y position for placing the logo

        // Display the logo in the PDF at the center
        $pdf->Image($logo_path, $x_position, $y_position, $logo_width, $logo_height, '', '', '', false, 300, '', false, false, 0, false, false, false);

        // Move the cursor down to avoid overlap with the logo
        $pdf->Ln(35); // Adjust spacing after the logo
    } else {
        // Display a message if the logo is not found
        //$pdf->Cell(0, 10, 'Logo not found: ' . htmlspecialchars($logo_path), 0, 1, 'C');
    }



    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->MultiCell(0, 10, 'Company Information', 0, 'L', 0, 1);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->MultiCell(0, 10, 
        'Company Name: ' . htmlspecialchars($business_info['business_name']) . "\n" .
        'Address: ' . htmlspecialchars($business_info['address']) . "\n" .
        'Phone: ' . htmlspecialchars($business_info['contact_number']) . "\n" .
        'Email: ' . htmlspecialchars($business_info['email_address']), 
        0, 'L', 0, 1);

    $pdf->Ln(10);
    
    // Customer Details Section
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->MultiCell(0, 10, 'Customer Information', 0, 'L', 0, 1);
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->MultiCell(0, 10, 
        'Transaction ID: ' . $transaction_id . "\n" .
        'Customer Name: ' . htmlspecialchars($customer_name) . "\n" .
        'IC Number: ' . htmlspecialchars($icnumber), 
        0, 'L', 0, 1);

    $pdf->Ln(20);
    
    // Product Details Table
    $html = '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr><th>Barcode</th><th>Product Name</th><th>Quantity</th><th>Total Price (RM)</th></tr></thead><tbody>';
    
    // Reset pointer to the result set to iterate over all transactions
    $result->data_seek(0); // Go back to the first row

    // Loop through each transaction and add to the HTML table
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['barcode']) . '</td>';
        
        // Fetch product name based on barcode
        $barcode = $row['barcode'];
        $product_query = "SELECT brand, producttype, variant FROM productinventorytable WHERE barcode = ?";
        $product_stmt = $conn->prepare($product_query);
        $product_stmt->bind_param("s", $barcode);
        $product_stmt->execute();
        $product_stmt->store_result(); // Store the result to avoid out of sync
        $product_stmt->bind_result($brand, $producttype, $variant);
        $product_stmt->fetch();
        
        // Combine brand, product type, and variant into one product name
        $product_name = htmlspecialchars($brand . ' - ' . $producttype . ' - ' . $variant);
        
        // Add product name, quantity, and total price
        $html .= '<td>' . $product_name . '</td>';
        $html .= '<td>' . $row['quantity'] . '</td>';
        $html .= '<td>' . number_format($row['total_price'], 2) . '</td>';
        $html .= '</tr>';

        // Close the product statement after fetching data
        $product_stmt->close();
    }

    $html .= '</tbody></table>';

    // Output the receipt content into the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Add space after the table
    $pdf->Ln(10); 

    $pdf->SetAutoPageBreak(false, 0);

    // Move QR code to the bottom of the page
    $pdf->SetY(-80); // Move to 80 units from the bottom

    // Generate the QR code
    $qr_code_data = "http://localhost/DevelopmentProject/transaction_details.php?transaction_id=" . $transaction_id; // URL or data for QR code
    $pdf->write2DBarcode($qr_code_data, 'QRCODE,L', '', '', 30, 30, null, 'N'); // Generate QR code

    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

    // Output PDF to the browser for download
    $pdf->Output('receipt_' . $transaction_id . '.pdf', 'D'); // 'D' forces download

    // Close the initial statement
    $stmt->close();
}

// Call the function somewhere in your code, passing the database connection and transaction ID
// For example:
// generatePDFReceipt($conn, 'some_transaction_id');

?>
