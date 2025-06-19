<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$pharmacy_id = $_SESSION['user_id'];
$pending_prescriptions = [];
$recent_quotations = [];

// Fetch pending prescriptions
$stmt = $conn->prepare("SELECT p.*, u.name AS user_name 
                        FROM prescriptions p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE NOT EXISTS (
                            SELECT 1 FROM quotations q 
                            WHERE q.prescription_id = p.id AND q.pharmacy_id = ?
                        )
                        ORDER BY p.created_at DESC 
                        LIMIT 5");
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_prescriptions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch recent quotations
$stmt = $conn->prepare("SELECT q.*, p.note, u.name AS user_name 
                        FROM quotations q 
                        JOIN prescriptions p ON q.prescription_id = p.id 
                        JOIN users u ON p.user_id = u.id 
                        WHERE q.pharmacy_id = ? 
                        ORDER BY q.created_at DESC 
                        LIMIT 5");
$stmt->bind_param("i", $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_quotations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <?php include '../includes/pharmacy_header.php'; ?>


    <div class="container">
        <?php echo displayMessage(); ?>

        <h2>Welcome, <?php echo $_SESSION['user_name']; ?></h2>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Pending Prescriptions</h3>
                <?php if (empty($pending_prescriptions)): ?>
                <p>No pending prescriptions.</p>
                <?php else: ?>
                <ul class="prescription-list">
                    <?php foreach ($pending_prescriptions as $prescription): ?>
                    <li>
                        <a href="prepare_quotation.php?prescription_id=<?php echo $prescription['id']; ?>">
                            Prescription #<?php echo $prescription['id']; ?> - <?php echo $prescription['user_name']; ?>
                        </a>
                        <span class="date"><?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="prescriptions.php" class="btn">View All Prescriptions</a>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h3>Recent Quotations</h3>
                <?php if (empty($recent_quotations)): ?>
                <p>No recent quotations.</p>
                <?php else: ?>
                <ul class="quotation-list">
                    <?php foreach ($recent_quotations as $quotation): ?>
                    <li>
                        <div class="quotation-info">
                            <span>#<?php echo $quotation['id']; ?> - <?php echo $quotation['user_name']; ?></span>
                            <span class="amount">$<?php echo number_format($quotation['total_amount'], 2); ?></span>
                        </div>
                        <div class="status status-<?php echo $quotation['status']; ?>">
                            <?php echo ucfirst($quotation['status']); ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>