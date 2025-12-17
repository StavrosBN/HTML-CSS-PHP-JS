<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thesis_id = intval($_POST['thesis_id']);
    $new_state = $_POST['new_state'];

    // Update the state in the database
    $stmt = $conn->prepare("UPDATE thesis SET state = ? WHERE thesis_id = ?");
    $stmt->bind_param('si', $new_state, $thesis_id);

    if ($stmt->execute()) {
        echo "State updated successfully.";
        header("Location: index.php");
    } else {
        echo "Error updating state: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
