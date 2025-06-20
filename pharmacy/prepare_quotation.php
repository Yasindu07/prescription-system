<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$prescription_id = $_GET['prescription_id'] ?? 0;
$pharmacy_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT p.*, u.name AS user_name, u.email AS user_email 
                        FROM prescriptions p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();
$stmt->close();

if (!$prescription) {
    redirect('prescriptions.php', 'Prescription not found');
}


$stmt = $conn->prepare("SELECT id FROM quotations 
                        WHERE prescription_id = ? AND pharmacy_id = ?");
$stmt->bind_param("ii", $prescription_id, $pharmacy_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    redirect('prescriptions.php', 'You have already quoted on this prescription');
}
$stmt->close();


$stmt = $conn->prepare("SELECT * FROM prescription_images 
                        WHERE prescription_id = ?");
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$errors = [];
$items = [['drug_name' => '', 'quantity_description' => '', 'amount' => '']];
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = [];
    $total = 0;


    $drug_names = $_POST['drug_name'];
    $quantity_descriptions = $_POST['quantity_description'];
    $amounts = $_POST['amount'];

    for ($i = 0; $i < count($drug_names); $i++) {
        $drug_name = sanitize($drug_names[$i]);
        $quantity_description = sanitize($quantity_descriptions[$i]);
        $amount = (float) $amounts[$i];

        if (!empty($drug_name)) {
            $items[] = [
                'drug_name' => $drug_name,
                'quantity_description' => $quantity_description,
                'amount' => $amount
            ];
            $total += $amount;
        }
    }


    if (count($items) < 1) $errors[] = "At least one item is required";
    if ($total <= 0) $errors[] = "Total amount must be greater than zero";

    if (empty($errors)) {

        $conn->begin_transaction();

        try {

            $stmt = $conn->prepare("INSERT INTO quotations (prescription_id, pharmacy_id, total_amount) 
                                    VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $prescription_id, $pharmacy_id, $total);
            $stmt->execute();
            $quotation_id = $stmt->insert_id;
            $stmt->close();


            foreach ($items as $item) {
                $stmt = $conn->prepare("INSERT INTO quotation_items (quotation_id, drug_name, quantity_description, amount) 
                                        VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issd", $quotation_id, $item['drug_name'], $item['quantity_description'], $item['amount']);
                $stmt->execute();
                $stmt->close();
            }


            $conn->commit();

            redirect('dashboard.php', 'Quotation prepared successfully!');
        } catch (Exception $e) {

            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepare Quotation - Pharmacy</title>
    <link rel="stylesheet" href="./style.css">
    <script src="../scripts.js" defer></script>
</head>

<body>
    <?php include '../includes/pharmacy_header.php'; ?>
    <div class="container">
        <?php echo displayMessage(); ?>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <div class="prescription-details">
            <h2>Prescription #<?php echo $prescription['id']; ?></h2>
            <p><strong>User:</strong> <?php echo $prescription['user_name']; ?></p>
            <p><strong>Note:</strong> <?php echo $prescription['note']; ?></p>
            <p><strong>Delivery Address:</strong> <?php echo $prescription['delivery_address']; ?></p>
            <p><strong>Time Slot:</strong> <?php echo $prescription['delivery_time_slot']; ?></p>
            <h3>Prescription Images</h3>
            <div class="image-gallery">
                <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <?php
                            $fullPath = "../" . $image['image_path'];

                            ?>
                    <img src="<?php echo $fullPath; ?>" alt="Prescription Image">
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <form method="POST" action="">
            <div class="quotation-form">
                <h3>Prepare Quotation</h3>
                <div id="items-container">
                    <?php foreach ($items as $index => $item): ?>
                    <div class="item-row">
                        <div class="form-group">
                            <label>Drug Name</label>
                            <input type="text" name="drug_name[]" value="<?php echo $item['drug_name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Quantity (e.g., 10.00 x 5)</label>
                            <input type="text" name="quantity_description[]"
                                value="<?php echo htmlspecialchars($item['quantity_description'], ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Amount ($)</label>
                            <input type="number" step="0.01" name="amount[]" value="<?php echo $item['amount']; ?>"
                                required>
                        </div>
                        <?php if ($index === 0): ?>
                        <button type="button" class="btn add-item">+</button>
                        <?php else: ?>
                        <button type="button" class="btn remove-item">-</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="total-row">
                    <strong>Total:</strong>
                    <span id="total-amount">$<?php echo number_format($total, 2); ?></span>
                </div>
                <button type="submit" class="btn submit-btn">Submit Quotation</button>
            </div>
        </form>
    </div>
</body>

</html>