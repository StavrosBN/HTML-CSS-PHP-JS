<?php
include 'db_connect.php';
header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thesis_id = intval($_POST['thesis_id']); // Ensure thesis_id is an integer

    if ($thesis_id > 0) {
        // Single SQL query to update both text and state
        $updateQuery = "UPDATE thesis SET text = 'Requested by Professor', state = 'cancelled' WHERE thesis_id = ?";
        $stmt = $conn->prepare($updateQuery);

        if ($stmt) {
            $stmt->bind_param("i", $thesis_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["success" => true, "message" => "Thesis updated successfully."]);
                } else {
                    echo json_encode(["success" => false, "message" => "No rows updated. Thesis ID might not exist."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Error executing the query: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to prepare the SQL statement."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid thesis ID."]);
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
