<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';



function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}


function redirect($url, $message = null)
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}



function displayMessage()
{
    if (!empty($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return "<div class='alert alert-info'>$msg</div>";
    }
    return '';
}


function generateTimeSlots()
{
    $slots = [];
    $start = strtotime('8:00 AM');
    $end = strtotime('8:00 PM');

    for ($i = $start; $i <= $end; $i += 7200) {
        $slots[] = date('h:i A', $i) . ' - ' . date('h:i A', $i + 7200);
    }

    return $slots;
}


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


function isPharmacy()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'pharmacy';
}


function isRegularUser()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'user';
}