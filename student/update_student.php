<?php
session_start();
include("project_base.php");

// Εξασφάλιση ότι ο χρήστης είναι συνδεδεμένος και είναι φοιτητής
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Παίρνουμε τα δεδομένα από το AJAX
$student_id = $_POST['student_id'] ?? null;
$email = $_POST['email'] ?? '';
$landline = $_POST['landline'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$street = $_POST['street'] ?? '';
$number = $_POST['number'] ?? '';
$city = $_POST['city'] ?? '';
$postcode = $_POST['postcode'] ?? '';

// Ελέγχουμε αν τα δεδομένα είναι πλήρη
if (!$student_id || empty($email) || empty($landline) || empty($mobile) || empty($city)) {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    exit;
}

// Ενημέρωση της βάσης δεδομένων
$sql = "UPDATE student SET email = ?, landline = ?, mobile = ?, street = ?, number = ?, city = ?, postcode = ? WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssissi", $email, $landline, $mobile, $street, $number, $city, $postcode, $student_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Details updated successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update details."]);
}

$stmt->close();
$conn->close();
?>
