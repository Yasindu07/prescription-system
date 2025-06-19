<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Prescription System</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="../scripts.js" defer></script>
</head>

<body>
    <header>
        <h1>Pharmacy Dashboard</h1>
        <nav>
            <a href="../pharmacy/dashboard.php">Dashboard</a>
            <a href="../pharmacy/prescriptions.php">Prescriptions</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <?php echo displayMessage(); ?>