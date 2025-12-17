<?php
include 'db_connect.php';
include '../init.php';

header('Content-Type: application/json'); // Ensure the response is JSON

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $student_id = isset($_POST['student_id']) && $_POST['student_id'] !== '' ? intval($_POST['student_id']) : null;

    $presentationPath = null;
    if (!empty($_FILES['pdf_file']['name'])) {
        $targetDir = "../uploads/";
        $fileName = "thesis_pdf_" . time() . ".pdf";
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetFilePath)) {
            $presentationPath = $targetFilePath;
        } else {
            echo json_encode(["success" => false, "message" => "File upload failed."]);
            exit();
        }
    }

    $sql = "INSERT INTO thesis (title, description, professor_id, student_id, prof_path)
            VALUES ('$title', '$description', $user_id, " . ($student_id ? $student_id : 'NULL') . ", '$presentationPath')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "New thesis added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }

    $conn->close();
    exit();
}

echo json_encode(["success" => false, "message" => "Invalid request."]);
?>
