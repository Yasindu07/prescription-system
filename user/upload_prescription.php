<?php

require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$errors = [];
$note = $delivery_address = $delivery_time_slot = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = sanitize($_POST['note']);
    $delivery_address = sanitize($_POST['delivery_address']);
    $delivery_time_slot = sanitize($_POST['delivery_time_slot']);
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($delivery_address)) $errors[] = "Delivery address is required";
    if (empty($delivery_time_slot)) $errors[] = "Delivery time slot is required";

    // Validate file uploads
    $imageCount = count($_FILES['images']['name']);
    if ($imageCount < 1) $errors[] = "At least one image is required";
    if ($imageCount > 5) $errors[] = "Maximum 5 images allowed";

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert prescription
            $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, note, delivery_address, delivery_time_slot) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $note, $delivery_address, $delivery_time_slot);
            $stmt->execute();
            $prescription_id = $stmt->insert_id;
            $stmt->close();

            // Upload images 
            for ($i = 0; $i < $imageCount; $i++) {
                if ($_FILES['images']['error'][$i] == 0) {
                    $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                    $filename = "prescription_{$prescription_id}_" . uniqid() . ".$ext";
                    $targetPath = UPLOAD_DIR . $filename;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetPath)) {
                        $stmt = $conn->prepare("INSERT INTO prescription_images (prescription_id, image_path) VALUES (?, ?)");
                        $imagePath = 'uploads/' . $filename;
                        $stmt->bind_param("is", $prescription_id, $imagePath);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }

            // Commit transaction
            $conn->commit();
            redirect('dashboard.php', 'Prescription uploaded successfully!');
        } catch (Exception $e) {
            // Rollback transaction on error
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
    <title>Upload Prescription - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php include '../includes/user_header.php'; ?>

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

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Note (Optional)</label>
                <textarea name="note"><?php echo $note; ?></textarea>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="delivery_address" required><?php echo $delivery_address; ?></textarea>
            </div>
            <div class="form-group">
                <label>Delivery Time Slot</label>
                <select name="delivery_time_slot" required>
                    <option value="">Select a time slot</option>
                    <?php foreach (generateTimeSlots() as $slot): ?>
                    <option value="<?php echo $slot; ?>" <?php echo $delivery_time_slot == $slot ? 'selected' : ''; ?>>
                        <?php echo $slot; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Upload Prescription Images (Max 5)</label>
                <input type="file" name="images[]" id="image-input" multiple accept="image/*" required>
            </div>
            <button type="submit" class="btn">Upload Prescription</button>
        </form>
    </div>
</body>

</html>