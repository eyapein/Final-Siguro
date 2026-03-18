<?php
// Test page to see what data is being submitted
session_start();
echo "<h1>Payment Form Data Test</h1>";
echo "<pre>";
echo "POST Data:\n";
print_r($_POST);
echo "\n\nSession Data:\n";
print_r($_SESSION);
echo "</pre>";

if (isset($_POST['booking_data'])) {
    $bookingData = json_decode($_POST['booking_data'], true);
    echo "<h2>Decoded Booking Data:</h2>";
    echo "<pre>";
    print_r($bookingData);
    echo "</pre>";
}
?>
