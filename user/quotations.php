<?php

require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];
$quotations = [];

// Fetch user's quotations
$stmt = $conn->prepare("SELECT q.*, p.note, p.delivery_address, p.delivery_time_slot, u.name AS pharmacy_name 
                        FROM quotations q 
                        JOIN prescriptions p ON q.prescription_id = p.id 
                        JOIN users u ON q.pharmacy_id = u.id 
                        WHERE p.user_id = ? 
                        ORDER BY q.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$quotations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch items for each quotation
foreach ($quotations as &$quotation) {
    $stmt = $conn->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
    $stmt->bind_param("i", $quotation['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $quotation['items'] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
unset($quotation);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotations - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include '../includes/user_header.php'; ?>

    <div class="container">
        <?php echo displayMessage(); ?>

        <h2>Your Quotations</h2>

        <?php if (empty($quotations)): ?>
        <p>No quotations received yet.</p>
        <?php else: ?>
        <div class="quotation-list">
            <?php foreach ($quotations as $quotation): ?>
            <div class="quotation-card">
                <h3>Quotation #<?php echo $quotation['id']; ?></h3>
                <p><strong>Pharmacy:</strong> <?php echo $quotation['pharmacy_name']; ?></p>
                <p><strong>Prescription Note:</strong> <?php echo $quotation['note']; ?></p>
                <p><strong>Delivery Address:</strong> <?php echo $quotation['delivery_address']; ?></p>
                <p><strong>Time Slot:</strong> <?php echo $quotation['delivery_time_slot']; ?></p>

                <h4>Items</h4>
                <table class="quotation-table">
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotation['items'] as $item): ?>
                        <tr>
                            <td><?php echo $item['drug_name']; ?></td>
                            <td><?php echo $item['quantity_description']; ?></td>
                            <td>$<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2">Total</td>
                            <td>$<?php echo number_format($quotation['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="quotation-status">
                    <p><strong>Status:</strong>
                        <span class="status-<?php echo $quotation['status']; ?>">
                            <?php echo ucfirst($quotation['status']); ?>
                        </span>
                    </p>

                    <?php if ($quotation['status'] == 'pending'): ?>
                    <div class="actions">
                        <a href="accept_quotation.php?id=<?php echo $quotation['id']; ?>&action=accept"
                            class="btn accept">Accept</a>
                        <a href="accept_quotation.php?id=<?php echo $quotation['id']; ?>&action=reject"
                            class="btn reject">Reject</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>