<?php
// Database Setup Script for Ticketix
// Run this file first before using the application

echo "<h2>Ticketix Database Setup</h2>";
echo "<p>Setting up the database for your local environment...</p>";

// Connect to MySQL server (without specifying database)
$conn = new mysqli("localhost", "root", "");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<p>✓ Connected to MySQL server successfully</p>";

// Read and execute the SQL file
$sqlFile = 'ticketix.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    
    // Split the SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement) === TRUE) {
                echo "<p>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p>✗ Error: " . $conn->error . "</p>";
            }
        }
    }
    
    echo "<h3>Database setup completed!</h3>";
    echo "<p>You can now use the Ticketix application.</p>";
    echo "<p><a href='TICKETIX NI CLAIRE.php'>Go to Ticketix Homepage</a></p>";
    
} else {
    echo "<p>✗ Error: ticketix.sql file not found!</p>";
    echo "<p>Make sure the ticketix.sql file is in the same directory as this setup script.</p>";
}

$conn->close();
?>