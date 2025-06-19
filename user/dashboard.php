<?php

require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];
$prescriptions = [];

// Fetch user's prescriptions
$stmt = $conn->prepare("SELECT * FROM prescriptions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$prescriptions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include '../includes/user_header.php'; ?>

    <div class="container">
        <?php echo displayMessage(); ?>

        <h2>Welcome, <?php echo $_SESSION['user_name']; ?></h2>

        <div class="card">
            <h3>Recent Prescriptions</h3>
            <?php if (empty($prescriptions)): ?>
            <p>No prescriptions uploaded yet.</p>
            <?php else: ?>
            <div class="prescription-list">
                <?php foreach ($prescriptions as $prescription): ?>
                <div class="prescription-item">
                    <h4>Prescription #<?php echo $prescription['id']; ?></h4>
                    <p><strong>Note:</strong> <?php echo $prescription['note']; ?></p>
                    <p><strong>Delivery Address:</strong> <?php echo $prescription['delivery_address']; ?></p>
                    <p><strong>Time Slot:</strong> <?php echo $prescription['delivery_time_slot']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>