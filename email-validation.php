<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$email = '';
if ($method === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
} else {
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
}

if ($email === '') {
    echo json_encode([ 'ok' => false, 'reason' => 'missing_email' ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([ 'ok' => false, 'reason' => 'invalid_format' ]);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT acc_id FROM USER_ACCOUNT WHERE email = ? LIMIT 1");
if (!$stmt) {
    echo json_encode([ 'ok' => false, 'reason' => 'server_error' ]);
    $conn->close();
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();
$conn->close();

echo json_encode([
    'ok' => true,
    'available' => !$exists
]);