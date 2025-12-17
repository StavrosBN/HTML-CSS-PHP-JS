<?php
session_start();
include("project_base.php");

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Παίρνουμε τα δεδομένα από τη φόρμα
$date = $_POST['date'] ?? '';
$place = $_POST['place'] ?? '';
$mode = $_POST['mode'] ?? '';

// Ελέγχουμε αν όλα τα πεδία είναι συμπληρωμένα
if (empty($date) || empty($place) || empty($mode)) {
    echo json_encode(["success" => false, "message" => "Παρακαλώ συμπληρώστε όλα τα πεδία."]);
    exit;
}

// Εύρεση thesis_id και state από τον πίνακα thesis
$sql = "SELECT thesis_id, state FROM thesis WHERE student_id = ?";
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
$state = $row['state'];

$stmt->close();

// Έλεγχος αν το state είναι reviewing
if ($state !== 'reviewing') {
    echo json_encode(["success" => false, "message" => "Η διπλωματική εργασία δεν είναι στη φάση 'reviewing'."]);
    $conn->close();
    exit;
}

// SQL για εισαγωγή ή ενημέρωση
$sql = "INSERT INTO examination (thesis_id, datetime, place, mode)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        datetime = VALUES(datetime),
        place = VALUES(place),
        mode = VALUES(mode)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL preparation error: " . $conn->error]);
    exit;
}

$stmt->bind_param("isss", $thesis_id, $date, $place, $mode);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Τα στοιχεία αποθηκεύτηκαν/ενημερώθηκαν επιτυχώς!"]);
} else {
    echo json_encode(["success" => false, "message" => "Execution error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
