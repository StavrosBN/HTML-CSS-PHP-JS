<?php
session_start();
include("project_base.php");


if (isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
    // SQL ερώτημα
    $sql = "SELECT professor_id as id,
            name,
            surname,
            email,
            topic,
            department,
            university 
        FROM professor";

    // Προετοιμασία και εκτέλεση
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();

        $prof = [];
        while ($row = $result->fetch_assoc()) {
            $prof[] = $row;
        }

        // Επιστροφή δεδομένων σε JSON μορφή
        echo json_encode($prof);

        $stmt->close();
    } else {
        echo json_encode(["error" => "SQL error"]);
    }

    $conn->close();
} else {
    echo json_encode(["error" => "Unauthorized"]);
}
?>
