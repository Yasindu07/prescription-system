<?php
ob_start();
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$errors = [];
$name = $email = $address = $contact_no = $dob = $user_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize($_POST['address']);
    $contact_no = sanitize($_POST['contact_no']);
    $dob = sanitize($_POST['dob']);
    $user_type = sanitize($_POST['user_type']);

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($contact_no)) $errors[] = "Contact number is required";
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($user_type)) $errors[] = "User type is required";

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, address, contact_no, dob, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $address, $contact_no, $dob, $user_type);

        if ($stmt->execute()) {
            redirect('login.php', 'Registration successful! Please login.');
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <div class="container">
        <h1>User Registration</h1>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" required><?php echo $address; ?></textarea>
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_no" value="<?php echo $contact_no; ?>" required>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" value="<?php echo $dob; ?>" required>
            </div>
            <div class="form-group">
                <label>User Type</label>
                <select name="user_type" required>
                    <option value="user" <?php echo $user_type == 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="pharmacy" <?php echo $user_type == 'pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
                </select>
            </div>
            <button type="submit" class="btn">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>

</html>