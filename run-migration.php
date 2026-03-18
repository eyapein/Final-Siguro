<?php
// Run this file ONCE to add the is_deleted column
// Access it by going to: http://localhost/ticketix/run-migration.php

require_once __DIR__ . '/config.php';
$conn = getDBConnection();

echo "<h2>Running Database Migration...</h2>";

// Check if column already exists
$check = $conn->query("SHOW COLUMNS FROM MOVIE LIKE 'is_deleted'");
if ($check && $check->num_rows > 0) {
    echo "<p style='color: orange;'>✓ Column 'is_deleted' already exists. Skipping...</p>";
} else {
    // Add is_deleted column
    if ($conn->query("ALTER TABLE MOVIE ADD COLUMN is_deleted TINYINT(1) DEFAULT 0")) {
        echo "<p style='color: green;'>✓ Added 'is_deleted' column successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding 'is_deleted' column: " . $conn->error . "</p>";
    }
}

// Check if deleted_at column exists
$check2 = $conn->query("SHOW COLUMNS FROM MOVIE LIKE 'deleted_at'");
if ($check2 && $check2->num_rows > 0) {
    echo "<p style='color: orange;'>✓ Column 'deleted_at' already exists. Skipping...</p>";
} else {
    // Add deleted_at column
    if ($conn->query("ALTER TABLE MOVIE ADD COLUMN deleted_at DATETIME NULL")) {
        echo "<p style='color: green;'>✓ Added 'deleted_at' column successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding 'deleted_at' column: " . $conn->error . "</p>";
    }
}

// Add index
$checkIndex = $conn->query("SHOW INDEX FROM MOVIE WHERE Key_name = 'idx_is_deleted'");
if ($checkIndex && $checkIndex->num_rows > 0) {
    echo "<p style='color: orange;'>✓ Index 'idx_is_deleted' already exists. Skipping...</p>";
} else {
    if ($conn->query("CREATE INDEX idx_is_deleted ON MOVIE(is_deleted)")) {
        echo "<p style='color: green;'>✓ Added index successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding index: " . $conn->error . "</p>";
    }
}

$conn->close();

echo "<h3 style='color: green;'>Migration Complete!</h3>";
echo "<p><a href='admin-panel.php'>← Go back to Admin Panel</a></p>";
echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (run-migration.php) after running it for security!</p>";
?>