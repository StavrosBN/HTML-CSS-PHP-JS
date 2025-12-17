<?php
include 'db_connect.php';

if (isset($_POST['thesis_id']) && isset($_POST['notes'])) {
    $thesis_id = $_POST['thesis_id']; 
    $notes = $_POST['notes'];         

    error_log("Received thesis_id: $thesis_id, notes: $notes");

    $sql = "UPDATE thesis SET text = ? WHERE thesis_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $notes, $thesis_id);

    $response = false;
    if ($stmt->execute()) {
        $response = true;  
    }

    $stmt->close();
    $conn->close();

    if ($response) {
        echo "Notes saved successfully!";
    } else {
        echo "Failed to save notes.";
    }
} else {
    echo "Invalid data received.";
}
?>
