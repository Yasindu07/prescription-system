<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Ensure user is logged in and is a regular user
if (!isLoggedIn() || isPharmacy()) {
    redirect('../auth/login.php', 'Please login as a user to perform this action.');
}

// Get parameters
$quotation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) && in_array($_GET['action'], ['accept', 'reject']) ? $_GET['action'] : '';

// Validate parameters
if ($quotation_id <= 0 || empty($action)) {
    redirect('quotations.php', 'Invalid request.');
}

// Verify the quotation belongs to the user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT q.id, q.total_amount, p.note, u.email AS pharmacy_email, u.name AS pharmacy_name 
                        FROM quotations q 
                        JOIN prescriptions p ON q.prescription_id = p.id 
                        JOIN users u ON q.pharmacy_id = u.id 
                        WHERE q.id = ? AND p.user_id = ?");
$stmt->bind_param("ii", $quotation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$quotation = $result->fetch_assoc();
$stmt->close();

if (!$quotation) {
    redirect('quotations.php', 'Invalid quotation or access denied.');
}

// Update quotation status
$status = ($action === 'accept') ? 'accepted' : 'rejected';
$stmt = $conn->prepare("UPDATE quotations SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $quotation_id);
if ($stmt->execute()) {
    $message = "Quotation #$quotation_id has been " . ucfirst($status) . " successfully.";
    redirect('quotations.php', $message);
} else {
    redirect('quotations.php', 'Error updating quotation: ' . $conn->error);
}
$stmt->close();