<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isPharmacy()) {
        redirect('../pharmacy/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
} else {
    redirect('../auth/login.php');
}