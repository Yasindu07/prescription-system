<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <header>
        <h1>User Dashboard</h1>
        <nav>
            <a href="../user/dashboard.php">Dashboard</a>
            <a href="../user/upload_prescription.php">Upload Prescription</a>
            <a href="../user/quotations.php">Quotations</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <?php echo displayMessage(); ?>