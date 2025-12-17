<?php
include("project_base.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Ο χρήστης δεν είναι συνδεδεμένος."]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Βρίσκουμε το thesis_id του φοιτητή
$sql = "SELECT thesis_id FROM thesis WHERE student_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($thesis_id);
    
    if ($stmt->fetch()) {
        $stmt->close();

        // Παίρνουμε όλες τις προσκλήσεις για το συγκεκριμένο thesis_id
        $sql_invitations = "SELECT c.professor_id, c.time, c.approved
                            FROM committee_invitations c
                            WHERE c.thesis_id = ?
                            ORDER BY c.time ASC";
        if ($stmt_inv = $conn->prepare($sql_invitations)) {
            $stmt_inv->bind_param("i", $thesis_id);
            $stmt_inv->execute();
            $result = $stmt_inv->get_result();
            
            $invitations = [];
            while ($row = $result->fetch_assoc()) {
                $invitations[] = $row;
            }
            
            echo json_encode($invitations);
            $stmt_inv->close();
        } else {
            echo json_encode(["error" => "Σφάλμα κατά την εκτέλεση του query."]);
        }
    } else {
        echo json_encode(["error" => "Δεν βρέθηκε thesis για τον φοιτητή."]);
    }
} else {
    echo json_encode(["error" => "Σφάλμα κατά την εκτέλεση του query."]);
}

$conn->close();
?>
