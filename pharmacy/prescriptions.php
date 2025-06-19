<?php

require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$pharmacy_id = $_SESSION['user_id'];
$prescriptions = [];

// Fetch all prescriptions
$stmt = $conn->prepare("SELECT p.*, u.name AS user_name 
                        FROM prescriptions p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$prescriptions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if pharmacy has already quoted on each prescription
foreach ($prescriptions as &$prescription) {
    $stmt = $conn->prepare("SELECT id FROM quotations 
                            WHERE prescription_id = ? AND pharmacy_id = ?");
    $stmt->bind_param("ii", $prescription['id'], $pharmacy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription['has_quoted'] = $result->num_rows > 0;
    $stmt->close();
}
unset($prescription);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Pharmacy</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include '../includes/pharmacy_header.php'; ?>

    <div class="container">
        <?php echo displayMessage(); ?>

        <h2>All Prescriptions</h2>

        <?php if (empty($prescriptions)): ?>
        <p>No prescriptions available.</p>
        <?php else: ?>
        <div class="prescription-list">
            <?php foreach ($prescriptions as $prescription): ?>
            <div class="prescription-card">
                <h3>Prescription #<?php echo $prescription['id']; ?></h3>
                <p><strong>User:</strong> <?php echo $prescription['user_name']; ?></p>
                <p><strong>Note:</strong> <?php echo $prescription['note']; ?></p>
                <p><strong>Delivery Address:</strong> <?php echo $prescription['delivery_address']; ?></p>
                <p><strong>Time Slot:</strong> <?php echo $prescription['delivery_time_slot']; ?></p>
                <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></p>

                <div class="actions">
                    <?php if ($prescription['has_quoted']): ?>
                    <span class="quoted">Already Quoted</span>
                    <?php else: ?>
                    <a href="prepare_quotation.php?prescription_id=<?php echo $prescription['id']; ?>" class="btn">
                        Prepare Quotation
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>