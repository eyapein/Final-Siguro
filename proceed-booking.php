<?php
session_start();

if (isset($_POST['booking_data'])) {
    $_SESSION['pending_booking'] = $_POST['booking_data'];

    $bookingData = json_decode($_POST['booking_data'], true);
    $movie  = $bookingData['movie'] ?? '';
    $branch = $bookingData['branch'] ?? '';
    $date   = $bookingData['date'] ?? '';
    $time   = $bookingData['time'] ?? '';

    $_SESSION['return_after_login'] = 'checkout.php';
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: checkout.php');
    exit;
}

// Not logged in — go to login page
header('Location: login.php');
exit;