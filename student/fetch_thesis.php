<?php
session_start();
include("project_base.php");

if (isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
    $student_id = $_SESSION['user_id'];

    // SQL ερώτημα
    $sql = "SELECT thesis.title as title, 
                   thesis.state as state,
                   thesis.description as description,
                   professor.name as prof_name,
                   professor.surname as prof_surname,
                   thesis.thesis_id as thesis_id,
                   thesis.prof_path as prof_file,
                   thesis.links as links,
                   thesis.presentation_path as path,
                   thesis.praktiko as praktiko
            FROM thesis
            JOIN professor ON thesis.professor_id = professor.professor_id
            WHERE thesis.student_id = ?";

    // Προετοιμασία και εκτέλεση
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Επιστροφή δεδομένων σε JSON μορφή
        echo json_encode($data);

        $stmt->close();
    } else {
        echo json_encode(["error" => "SQL error"]);
    }

    $conn->close();
} else {
    echo json_encode(["error" => "Unauthorized"]);
}
?>
