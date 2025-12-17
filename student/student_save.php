<?php
include("project_base.php");

// Ξεκινάμε την session μόνο αν δεν έχει ήδη ξεκινήσει
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Έλεγχος αν το email ή τα δεδομένα αποστέλλονται μέσω POST
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Βρίσκουμε το professor_id από το email του καθηγητή
    $sql = "SELECT professor_id FROM professor WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($professor_id);

        if ($stmt->fetch()) {
            $stmt->close(); // Κλείνουμε το statement αφού ολοκληρωθεί

            // Αν βρούμε το professor_id, προχωράμε κατευθείαν στην εισαγωγή στο committee_invitations
            if (isset($_SESSION['user_id'])) {
                $student_id = $_SESSION['user_id'];

                // Θεωρούμε ότι το thesis_id υπάρχει ήδη για τον φοιτητή και τον καθηγητή
                // Βρίσκουμε το thesis_id από τον πίνακα thesis
                $sql_thesis = "SELECT thesis_id FROM thesis WHERE student_id = ?";
                if ($stmt_thesis = $conn->prepare($sql_thesis)) {
                    $stmt_thesis->bind_param("i", $student_id);
                    $stmt_thesis->execute();
                    $stmt_thesis->bind_result($thesis_id);

                    if ($stmt_thesis->fetch()) {
                        $stmt_thesis->close(); // Κλείνουμε το statement αφού ολοκληρωθεί

                        // Εισαγωγή στην table committee_invitations με το professor_id και thesis_id
                        $sql_insert = "INSERT INTO committee_invitations (professor_id, thesis_id) VALUES (?, ?)";
                        if ($stmt_insert = $conn->prepare($sql_insert)) {
                            $stmt_insert->bind_param("ii", $professor_id, $thesis_id);
                            try {
                                $stmt_insert->execute();
                                echo "Η πρόσκληση του καθηγητή έγινε επιτυχώς.";
                            } catch (mysqli_sql_exception $e) {
                                // Έλεγχος για διπλή εγγραφή
                                if ($e->getCode() == 1062) { // 1062: Duplicate entry
                                    echo "Έχετε στείλει ήδη στον συγκεκριμένο χρήστη!";
                                } else {
                                    echo "Σφάλμα κατά την εισαγωγή της πρόσκλησης. Προσκλήσεις επιτρέπονται μόνο για PENDING καταστάσεις.";
                                }
                            }
                            $stmt_insert->close(); // Κλείσιμο του statement μετά την εκτέλεση
                        }
                    } else {
                        echo "Δεν βρέθηκε thesis για αυτόν τον χρήστη.";
                    }
                }
            } else {
                echo "Δεν είναι συνδεδεμένος ο χρήστης.";
            }
        } else {
            echo "Ο καθηγητής με αυτό το email δεν βρέθηκε.";
        }
    } else {
        echo "Σφάλμα κατά την προετοιμασία του SQL query.";
    }
} else {
    echo "Δεν ελήφθησαν δεδομένα.";
}

$conn->close(); // Κλείνουμε τη σύνδεση με τη βάση δεδομένων
?>
