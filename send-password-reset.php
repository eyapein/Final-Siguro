<?php

$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";

if ($email === "") {
    echo "Please enter your email address.";
    exit;
}

$token = bin2hex(random_bytes(16));

//this gives a security of 64 string
$token_hash = hash("sha256", $token);

$expiry = date("Y-m-d H:i:s", time() + 60 * 15);

// Use centralized DB config/connection
require_once __DIR__ . "/config.php";
$mysqli = getDBConnection();

// Ensure the target table columns exist (compatible with MySQL versions without IF NOT EXISTS)
$dbName = DB_NAME;
function ensureColumn($mysqli, $dbName, $table, $column, $definition) {
    $checkSql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $check = $mysqli->prepare($checkSql);
    $check->bind_param("sss", $dbName, $table, $column);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    if ((int)$res['cnt'] === 0) {
        $mysqli->query("ALTER TABLE $table ADD COLUMN $definition");
    }
}

ensureColumn($mysqli, $dbName, 'USER_ACCOUNT', 'reset_token_hash', 'reset_token_hash VARCHAR(64) NULL');
ensureColumn($mysqli, $dbName, 'USER_ACCOUNT', 'reset_token_expires_at', 'reset_token_expires_at DATETIME NULL');

$sql = "UPDATE USER_ACCOUNT
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

// Check if account exists first
$checkStmt = $mysqli->prepare("SELECT acc_id FROM USER_ACCOUNT WHERE email = ? LIMIT 1");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult && $checkResult->num_rows === 1) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $token_hash, $expiry, $email);
    $stmt->execute();

    $mail = require __DIR__ . "/mailer.php";

    // Use the authenticated address as sender for Gmail/SMTP providers
    $fromAddress = isset($mail->Username) ? $mail->Username : "noreply@example.com";
    $mail->setFrom($fromAddress, "Ticketix");
    $mail->addAddress($email);
    // Build a reset URL for the current host (works on localhost and production)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    // Adjust base path if app is in a subfolder like /ticketix
    $basePath = '/ticketix';
    $resetUrl = $scheme . '://' . $host . $basePath . '/reset-password.php?token=' . $token;

    
    $mail->Subject = 'Reset your Ticketix password';
    $mail->Body = <<<HTML
Click <a href="$resetUrl">here</a> to reset your password. If you did not request this, you can ignore this email.
HTML;
    $mail->AltBody = "Open this link to reset your password: $resetUrl";

    try {
        $mail->send();
        echo "Message sent, please check your e-mail's inbox.";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
} else {
    echo "No account found with that email.";
}