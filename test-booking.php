<?php
// Test booking process - shows errors directly
session_start();
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("Please log in first. <a href='login.php'>Login</a>");
}

echo "<h1>Test Booking Process</h1>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? $_SESSION['acc_id'] ?? 'NOT SET') . "</p>";

// Test database connection
$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed!");
}
echo "<p>✓ Database connected</p>";

// Test if tables exist
$tables = ['RESERVE', 'PAYMENT', 'TICKET', 'MOVIE', 'MOVIE_SCHEDULE', 'SEAT', 'BRANCH'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✓ Table $table exists</p>";
    } else {
        echo "<p>✗ Table $table DOES NOT EXIST</p>";
    }
}

// Check if user has acc_id
$userId = $_SESSION['user_id'] ?? $_SESSION['acc_id'] ?? null;
if (!$userId) {
    echo "<p style='color:red;'>✗ User ID not found in session. Please check login.</p>";
} else {
    echo "<p>✓ User ID: $userId</p>";
    
    // Verify user exists in database
    $stmt = $conn->prepare("SELECT acc_id, email FROM USER_ACCOUNT WHERE acc_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        echo "<p>✓ User found in database: " . htmlspecialchars($user['email']) . "</p>";
    } else {
        echo "<p style='color:red;'>✗ User NOT found in database!</p>";
    }
}

// Check if there are any movies
$moviesQuery = $conn->query("SELECT COUNT(*) as count FROM MOVIE");
$movieCount = $moviesQuery->fetch_assoc()['count'] ?? 0;
echo "<p>Movies in database: $movieCount</p>";

// Check if there are any branches
$branchesQuery = $conn->query("SELECT COUNT(*) as count FROM BRANCH");
$branchCount = $branchesQuery->fetch_assoc()['count'] ?? 0;
echo "<p>Branches in database: $branchCount</p>";

// Test a simple insert to see if it works
echo "<h2>Testing Database Write</h2>";
try {
    // Test if we can insert (we'll delete it after)
    $testQuery = $conn->query("SELECT 1");
    if ($testQuery) {
        echo "<p>✓ Database queries work</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Database query failed: " . $e->getMessage() . "</p>";
}

echo "<br><a href='admin-panel.php'>Back to Admin Panel</a> | ";
echo "<a href='debug-bookings.php'>View Debug Page</a>";
?>
