<?php

include 'db_connect.php';

// Check if a file was uploaded
if (isset($_POST['thesis_id']) && isset($_FILES['pdf_file'])) {
    $thesis_id = intval($_POST['thesis_id']);
    $file = $_FILES['pdf_file'];

    // Fetch the current state of the thesis from the database
    $stateQuery = "SELECT state FROM thesis WHERE thesis_id = $thesis_id";
    $stateResult = $conn->query($stateQuery);

    if ($stateResult && $stateResult->num_rows > 0) {
        $row = $stateResult->fetch_assoc();
        $state = $row['state'];

        // Check if the state is "pending"
        if ($state !== 'pending') {
            die("File upload is not allowed. The thesis state must be 'pending'.");
        }

        // Check if the file is a valid PDF
        if ($file['type'] !== 'application/pdf') {
            die("Invalid file type. Please upload a PDF.");
        }

        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        // Generate a unique file name
        $fileName = $uploadDir . "thesis_pdf_" . $thesis_id . ".pdf";

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $fileName)) {
            // Update the database with the new file path
            $sql = "UPDATE thesis SET prof_path = '$fileName' WHERE thesis_id = $thesis_id";
            if ($conn->query($sql) === TRUE) {
                echo "File uploaded successfully.";
                header("Location: index.php");
            } else {
                echo "Error updating database: " . $conn->error;
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        die("Invalid thesis ID or thesis not found.");
    }
} else {
    echo "No file uploaded.";
}

$conn->close();
?>
