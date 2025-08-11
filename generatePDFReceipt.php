
<?php

require_once('tcpdf/tcpdf.php'); // Include TCPDF library

function generatePDFReceipt($conn, $transaction_id) {
    // Step 1: Fetch transaction details from sales_transaction table
    $query = "SELECT barcode, quantity, total_price FROM sales_transaction WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create a new PDF document
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Receipt for Transaction ' . $transaction_id);

    // Set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Sales Receipt', 'Transaction ID: ' . $transaction_id);

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Step 2: Create the receipt content
    $html = '<h1>Sales Receipt</h1>';
    $html .= '<p><strong>Transaction ID:</strong> ' . $transaction_id . '</p>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0">';
    $html .= '<thead><tr><th>Barcode</th><th>Quantity</th><th>Total Price (RM)</th></tr></thead><tbody>';

    // Loop through the transaction details and add rows to the table
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['barcode'] . '</td>';
        $html .= '<td>' . $row['quantity'] . '</td>';
        $html .= '<td>' . number_format($row['total_price'], 2) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    // Step 3: Output the receipt content into the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Step 4: Output PDF to the browser for download
    $pdf->Output('receipt_' . $transaction_id . '.pdf', 'D'); // 'D' forces download

    // Close the statement
    $stmt->close();
}


?>
