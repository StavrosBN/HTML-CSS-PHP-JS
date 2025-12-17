<?php
include 'db_connect.php';

if (isset($_POST['thesis_id']) && isset($_POST['description'])) {
    $thesis_id = $_POST['thesis_id']; 
    $desc = $_POST['description'];

    error_log("Received thesis_id: $thesis_id, desc: $desc");

    $sql = "UPDATE thesis SET description = ? WHERE thesis_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        // Log and display SQL preparation error
        $error = $conn->error;
        error_log("Failed to prepare SQL statement: $error");
        echo "Failed to prepare SQL statement: " . htmlspecialchars($error);
        exit;
    }

    $stmt->bind_param('si', $desc, $thesis_id);

    if ($stmt->execute()) {
        echo "Description saved successfully!";
    } else {
        // Capture and display SQL execution error, including trigger errors
        $error = $stmt->error;
        error_log("SQL error during execution: $error");
        echo "" . htmlspecialchars($error);
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid data received.";
}
?>
