<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and save the invoice terms
    $invoice_terms = htmlspecialchars(trim($_POST['invoice_terms']));

    // Update the session with the new invoice terms
    $_SESSION['invoice_terms'] = $invoice_terms;

    // Optionally, you could also save this to a database if needed
    // Assuming you have a database connection setup
    // $stmt = $db->prepare("UPDATE terms_table SET invoice_terms = ? WHERE id = ?");
    // $stmt->execute([$invoice_terms, $id]); // Use your specific logic for ID

    // Redirect back to the referring page (where the form was submitted from)
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
