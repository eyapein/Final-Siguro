<?php
require_once __DIR__ . '/config.php';
$conn = getDBConnection();

if (isset($_POST['id']) && isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    $now_showing = ($action === 'now_showing') ? 1 : 0;
    $coming_soon = ($action === 'coming_soon') ? 1 : 0;

    $query = $conn->prepare("UPDATE MOVIE SET now_showing = ?, coming_soon = ? WHERE movie_show_id = ?");
    $query->bind_param("iii", $now_showing, $coming_soon, $id);

    if ($query->execute()) {
        echo json_encode(["success" => true, "message" => "Movie status updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update movie status."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>