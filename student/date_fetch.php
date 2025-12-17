<?php
session_start();
include("project_base.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Βρίσκουμε το thesis_id του φοιτητή
$sql = "SELECT thesis_id FROM thesis WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε διπλωματική εργασία για τον φοιτητή."]);
    exit;
}

$row = $result->fetch_assoc();
$thesis_id = $row['thesis_id'];
$stmt->close();

// Βρίσκουμε την τελευταία ημερομηνία που το state έγινε "reviewing"
$sql = "SELECT MAX(datetime) AS review_date FROM thesis_log 
        WHERE thesis_id = ? AND new_value = 'reviewing'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$review_date = $row['review_date'] ?? null;

if (!$review_date) {
    echo json_encode(["success" => false, "message" => "Δεν υπάρχει καταχωρημένη αλλαγή σε reviewing."]);
    exit;
}

echo json_encode(["success" => true, "review_date" => $review_date]);

$stmt->close();
$conn->close();
?>
