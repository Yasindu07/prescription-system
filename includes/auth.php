<?php
ob_start();
session_start();
require_once __DIR__ . '/functions.php';



if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    redirect('auth/login.php', 'Please login to access this page.');
}


if (isLoggedIn()) {
    if (isPharmacy() && strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
        redirect('pharmacy/dashboard.php', 'Access denied. Redirected to pharmacy dashboard.');
    }
    if (isRegularUser() && strpos($_SERVER['REQUEST_URI'], '/pharmacy/') !== false) {
        redirect('user/dashboard.php', 'Access denied. Redirected to user dashboard.');
    }
}