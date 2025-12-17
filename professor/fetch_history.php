<?php
include 'db_connect.php';

if (isset($_GET['thesis_id'])) {
    $thesis_id = $_GET['thesis_id'];

    // SQL query to fetch the history of a specific thesis
    $sql = "SELECT datetime, action, old_value, new_value, field_of_change FROM thesis_log WHERE thesis_id = ? ORDER BY datetime DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch history records
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    // Return history as JSON response
    if (count($history) > 0) {
        echo json_encode(["success" => true, "history" => $history]);
    } else {
        echo json_encode(["success" => false, "message" => "No history found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Thesis ID not provided."]);
}

$conn->close();
?>
