<?php
require_once("C:\\xampp\\htdocs\\DevelopmentProject\\tcpdf\\tcpdf.php");

class MYPDF extends TCPDF {
    // Custom header
    public function Header() {
        $this->Ln(10); // Adjust this value to control the space from the top
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 15, 'Restock List', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }

    // Custom footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetY(-20);
        $this->Write(0, 'Generated on: ' . date('Y-m-d H:i:s'), '', 0, 'C', true, 0, false, false, 0);
    }
}

// Function to generate PDF
function generatePDF($selected_products, $selected_quantities, $supplier_info, $conn) {
    $pdf = new MYPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Heviinash Parugavelu');
    $pdf->SetTitle('Restock List');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Draw borders
    $pdf->SetDrawColor(0, 0, 0); // Black color for the border
    $pdf->Rect(5, 5, 200, 287, 'D'); // x, y, width, height, style (D for draw)

    // Load company logo from database
    $business_query = "SELECT * FROM businessregistration LIMIT 1"; // Assuming you only need one logo
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->execute();
    $business_info = $business_stmt->get_result()->fetch_assoc();

    // Display company logo in the center
    $logo_path = $business_info['companylogo']; // Ensure this path is correct and accessible
    $pdf->Image($logo_path, 75, 20, 60, 0, '', '', '', false, 300, '', false, false, 0, false, false, false); // Adjust dimensions and position as needed

    // Add space below the logo
    $pdf->Ln(60); // Adjust to position sender and receiver sections

    // Sender Details (Top Left)
    $pdf->SetY(45); // Set the Y-coordinate to a higher position (e.g., 30)
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Sender:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, htmlspecialchars($business_info['business_name']), '', 0, 'L', true, 0, false, false, 0);
    $pdf->Write(0, htmlspecialchars($business_info['address']), '', 0, 'L', true, 0, false, false, 0);
    $pdf->Write(0, htmlspecialchars($business_info['contact_number']), '', 0, 'L', true, 0, false, false, 0);
    $pdf->Write(0, htmlspecialchars($business_info['email_address']), '', 0, 'L', true, 0, false, false, 0);
    $pdf->Ln(10); // Add space between sender and receiver

    // Receiver Details (Top Right)
    $pdf->SetY(60); // Adjust Y to position it correctly below the logo
    $pdf->SetX(150); // Position it on the right
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Receiver:', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, htmlspecialchars($supplier_info['name']), '', 0, 'R', true, 0, false, false, 0);
    $pdf->Write(0, htmlspecialchars($supplier_info['email']), '', 0, 'R', true, 0, false, false, 0);
    $pdf->Ln(20); // Add space before the table

    // Add a table with borders
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(100, 10, 'Product Name', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Barcode', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Quantity', 1, 1, 'C');

    // Loop through selected products and add them to the PDF
    foreach ($selected_products as $barcode => $selected) {
        $quantity = $selected_quantities[$barcode] ?? 0;
        if ($quantity > 0) {
            $product_query = "SELECT * FROM productinventorytable WHERE barcode = ?";
            $product_stmt = $conn->prepare($product_query);
            $product_stmt->bind_param("s", $barcode);
            $product_stmt->execute();
            $product_info = $product_stmt->get_result()->fetch_assoc();

            $pdf->Cell(100, 10, htmlspecialchars($product_info['brand']) . ' - ' . htmlspecialchars($product_info['producttype']) . ' - ' . htmlspecialchars($product_info['variant']), 1, 0, 'L');
            $pdf->Cell(30, 10, htmlspecialchars($barcode), 1, 0, 'C');
            $pdf->Cell(30, 10, htmlspecialchars($quantity), 1, 1, 'C');
        }
    }

    // Add Terms and Conditions section
    $pdf->Ln(10); // Add space before terms and conditions
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Terms and Conditions', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 10, "1. All items must be inspected upon receipt.\n2. Notify us within 48 hours for any discrepancies.\n3. Subject to our standard terms and conditions.", 0, 'L', 0, true);

    $supplier_name = preg_replace('/[^A-Za-z0-9]/', '_', $supplier_info['name']); // Replace non-alphanumeric characters
    
    $current_date = date('Y-m-d'); // Format the date as YYYY-MM-DD

    // Set filename as "suppliername_currentdate.pdf"
    $filename = $supplier_name . '_' . $current_date . '.pdf';

    // Output the PDF with the custom filename
    $pdf->Output($filename, 'D'); // 'D' will prompt a download with the specified filename
}

?>
