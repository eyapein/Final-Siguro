<?php
require_once __DIR__ . '/config.php'; // your getDBConnection() file

$conn = getDBConnection();

$userSelectedBranch = isset($_GET['branch']) ? $_GET['branch'] : null;
$userSelectedMovie = isset($_GET['movie']) ? $_GET['movie'] : null;

// Fetch all branches for display
$branchesResult = $conn->query("SELECT * FROM BRANCH ORDER BY branch_name ASC");
$branches = [];
while ($row = $branchesResult->fetch_assoc()) {
    $branches[$row['branch_id']] = $row;
}

// If a branch is selected, fetch its Now Showing movies
$moviesByBranch = [];
if ($userSelectedBranch) {
    // Get branch_id by name
    $stmt = $conn->prepare("SELECT branch_id FROM BRANCH WHERE branch_name = ?");
    $stmt->bind_param('s', $userSelectedBranch);
    $stmt->execute();
    $branchIdResult = $stmt->get_result();
    $branchRow = $branchIdResult->fetch_assoc();
    $branchId = $branchRow['branch_id'] ?? null;

    if ($branchId) {
        // Check if branch_id column exists in MOVIE_SCHEDULE
        $column_check = $conn->query("SHOW COLUMNS FROM MOVIE_SCHEDULE LIKE 'branch_id'");
        $has_branch_id = $column_check && $column_check->num_rows > 0;
        
        if ($has_branch_id) {
            // Use branch_id to filter movies by branch
            $stmt = $conn->prepare("
                SELECT DISTINCT m.title
                FROM MOVIE m
                JOIN MOVIE_SCHEDULE ms ON m.movie_show_id = ms.movie_show_id
                WHERE m.now_showing = 1 AND ms.branch_id = ?
                ORDER BY m.title ASC
            ");
            $stmt->bind_param('i', $branchId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $moviesByBranch[] = $row['title'];
            }
            $stmt->close();
        } else {
            // branch_id column doesn't exist - show all now_showing movies
            $stmt = $conn->prepare("
                SELECT DISTINCT m.title
                FROM MOVIE m
                JOIN MOVIE_SCHEDULE ms ON m.movie_show_id = ms.movie_show_id
                WHERE m.now_showing = 1
                ORDER BY m.title ASC
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $moviesByBranch[] = $row['title'];
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Selection</title>
    <link rel="stylesheet" href="css/select-movie-branch.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="images/brand x.png">
</head>
<body>
<div class="container">
<?php
// If user selected a branch, show its Now Showing movies
if ($userSelectedBranch) {
    echo "<h2>Selected Branch: <strong>" . htmlspecialchars($userSelectedBranch) . "</strong></h2>";
    echo "<h3>Select a Movie</h3>";

    if (!empty($moviesByBranch)) {
        echo "<ul>";
        foreach ($moviesByBranch as $movie) {
            echo "<li><a href='seat-reservation.php?branch=" . urlencode($userSelectedBranch) . "&movie=" . urlencode($movie) . "'>" . htmlspecialchars($movie) . "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No Now Showing movies for this branch.</p>";
    }
} else {
    // Show all branches to start
    echo "<h2>All Cinema Locations</h2>";
    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($branch['branch_name']) . "</strong><br>";
        echo "<span class='address'>" . htmlspecialchars($branch['branch_location']) . "</span><br>";
        echo "<a href='select-movie-branch.php?branch=" . urlencode($branch['branch_name']) . "'>See What's Playing</a>";
        echo "</li>";
    }
    echo "</ul>";
}
?>
</div>
</body>
</html>