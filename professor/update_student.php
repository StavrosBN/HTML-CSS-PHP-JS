<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the thesis_id and student_id from the POST request
    $thesis_id = isset($_POST['thesis_id']) ? (int)$_POST['thesis_id'] : null;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;

    if ($student_id === '' || $student_id === 'NULL') {
        $student_id = null;
    }

    if ($thesis_id !== null) {
        $sql = "UPDATE thesis SET student_id = ? WHERE thesis_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            if ($student_id === null) {
                $stmt->bind_param("si", $student_id, $thesis_id);
            } else {
                $stmt->bind_param("ii", $student_id, $thesis_id);
            }

            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                echo "Error updating thesis: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Invalid data. Thesis ID is required.";
    }
}

$conn->close();
?>
