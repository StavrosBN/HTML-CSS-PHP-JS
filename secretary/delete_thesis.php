<?php
include 'db_connect.php';
header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['thesis_id']) || !isset($_POST['arithmos_protokolou'])) {
        echo json_encode(["success" => false, "message" => "Missing thesis ID or Arithmos Protokolou."]);
        exit;
    }

    $thesis_id = intval($_POST['thesis_id']);
    $arithmos_protokolou = intval($_POST['arithmos_protokolou']);

    if ($thesis_id <= 0 || $arithmos_protokolou <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid thesis ID or AP."]);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Enable bypass trigger
        if (!$conn->query("SET @bypass_trigger = 1;")) {
            throw new Exception("Failed to set bypass trigger: " . $conn->error);
        }

        // Single query to update necessary columns
        $updateQuery = "UPDATE thesis SET text = 'Requested By Student', state = 'cancelled', ap = ? WHERE thesis_id = ?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ii", $arithmos_protokolou, $thesis_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows updated. Thesis ID might not exist.");
        }

        $stmt->close();

        // Disable bypass trigger
        if (!$conn->query("SET @bypass_trigger = NULL;")) {
            throw new Exception("Failed to reset bypass trigger: " . $conn->error);
        }

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Thesis cancelled and AP updated successfully."]);

    } catch (Exception $e) {
        $conn->rollback();
        
        // Ensure bypass trigger is reset even if an error occurs
        $conn->query("SET @bypass_trigger = NULL;");
        
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
