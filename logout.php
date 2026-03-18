<?php
session_start();
require_once 'config.php';

// Update user status to offline in database
$userId = $_SESSION['user_id'] ?? $_SESSION['acc_id'] ?? null;
if ($userId) {
    $conn = getDBConnection();
    
    if (!$conn->connect_error) {
        $update_query = "UPDATE USER_ACCOUNT SET user_status = 'offline' WHERE acc_id = " . (int)$userId;
        $conn->query($update_query);
        $conn->close();
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to homepage
header("Location: TICKETIX NI CLAIRE.php");
exit();
?>