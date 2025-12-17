<?php
session_start();
include("project_base.php");

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Εύρεση thesis_id για τον φοιτητή
$sql = "SELECT thesis_id FROM thesis WHERE student_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL preparation error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε διπλωματική εργασία για τον φοιτητή."]);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$thesis_id = $row['thesis_id'];

$stmt->close();

// Ανάκτηση εξετάσεων για το συγκεκριμένο thesis_id
$sql = "SELECT thesis_id, datetime, place, mode FROM examination WHERE thesis_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL preparation error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

$stmt->close();
$conn->close();

// Επιστροφή των εξετάσεων
echo json_encode(["success" => true, "data" => $exams]);
?>
