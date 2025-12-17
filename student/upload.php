<?php
session_start();
include("project_base.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 'student' && isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['thesis_id'])) {
    echo "Thesis ID not provided.";
    exit;
}

$thesisId = $_POST['thesis_id'];

$sql = "SELECT state FROM thesis WHERE thesis_id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $thesisId, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No thesis found for this student.";
    exit;
}

$row = $result->fetch_assoc();
$thesisState = $row['state'];

if ($thesisState !== 'reviewing' && $thesisState !== 'active') {
    echo "Δεν επιτρέπεται να ανεβάσετε αρχείο.";
    exit;
}

if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['pdfFile']['name']);
    $targetFilePath = $uploadDir . $fileName;

    $fileType = mime_content_type($_FILES['pdfFile']['tmp_name']);
    if ($fileType === 'application/pdf') {
        if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFilePath)) {
            $relativePath = 'uploads/' . $fileName;

            $sql = "UPDATE thesis SET presentation_path = ? WHERE thesis_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $relativePath, $thesisId);

            if ($stmt->execute()) {
                echo "File uploaded successfully.";
            } else {
                echo "Error updating database: " . $conn->error;
            }

            $stmt->close();
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "Invalid file type. Please upload a PDF.";
    }
} else {
    echo "No file uploaded or error occurred.";
}

$conn->close();
?>
