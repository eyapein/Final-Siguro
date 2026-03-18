<?php

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

require_once __DIR__ . "/config.php";
$mysqli = getDBConnection();

$sql = "SELECT * FROM user_account WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("token not found");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("token expired");
}

// Token is valid; redirect to the password reset form page carrying the token
header('Location: password-reset.php?token=' . urlencode($token));
exit;