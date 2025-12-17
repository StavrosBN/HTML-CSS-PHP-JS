<?php
include("project_base.php");

// Ξεκινάμε τη session
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo "Δεν είστε συνδεδεμένος!";
    exit;
}

$student_id = $_SESSION['user_id']; // Παίρνουμε το student_id από τη session

// Έλεγχος του state του χρήστη πριν αποθηκεύσουμε το URL
$sql = "SELECT state FROM thesis WHERE student_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $stmt->bind_result($state);
        $stmt->fetch();
        $stmt->close();

        // Ελέγχουμε αν το state είναι "active" ή "reviewing"
        if ($state == 'active' || $state == 'reviewing' || $state == 'completed') {
            // Αποθήκευση του URL
            if (isset($_POST['url'])) {
                $url = $_POST['url'];

                // Ενημέρωση του URL στον πίνακα thesis για τον τρέχοντα χρήστη
                $sql_update = "UPDATE thesis SET links = ? WHERE student_id = ?";
                if ($stmt_update = $conn->prepare($sql_update)) {
                    $stmt_update->bind_param("si", $url, $student_id);
                    if ($stmt_update->execute()) {
                        echo "Επιτυχής αποθήκευση του URL!";
                    } else {
                        echo "Σφάλμα κατά την αποθήκευση του URL.";
                    }
                    $stmt_update->close();
                } else {
                    echo "Σφάλμα κατά την προετοιμασία του SQL query.";
                }
            } else {
                echo "Δεν παραλήφθηκε το URL.";
            }
        } else {
            echo "Η διπλωματική εργασία δεν είναι σε κατάσταση 'active'/'reviewing'/'completed', οπότε δεν επιτρέπεται η αποθήκευση του URL.";
        }
    } else {
        echo "Σφάλμα κατά την ανάκτηση του state.";
    }
} else {
    echo "Σφάλμα κατά την προετοιμασία του SQL query.";
}

$conn->close();
?>
