<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
// Function to sanitize input data

// Function to sanitize input data
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to redirect with message
function redirect($url, $message = null)
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}


// Function to display messages
function displayMessage()
{
    if (!empty($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return "<div class='alert alert-info'>$msg</div>";
    }
    return '';
}

// Function to generate time slots
function generateTimeSlots()
{
    $slots = [];
    $start = strtotime('8:00 AM');
    $end = strtotime('8:00 PM');

    for ($i = $start; $i <= $end; $i += 7200) { // 7200 seconds = 2 hours
        $slots[] = date('h:i A', $i) . ' - ' . date('h:i A', $i + 7200);
    }

    return $slots;
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is pharmacy
function isPharmacy()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'pharmacy';
}

// Check if user is regular user
function isRegularUser()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'user';
}