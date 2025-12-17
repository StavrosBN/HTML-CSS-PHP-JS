<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thesisId = isset($_POST['thesis_id']) ? intval($_POST['thesis_id']) : 0;
    $field = isset($_POST['field']) ? mysqli_real_escape_string($conn, $_POST['field']) : '';
    $value = isset($_POST['value']) ? mysqli_real_escape_string($conn, $_POST['value']) : '';

    if ($thesisId > 0 && !empty($field)) {
        // If the value is an empty string, set it as NULL
        if ($value === '') {
            $value = 'NULL';
        } else {
            $value = "'$value'";
        }

        // Update the specific field in the thesis table
        $sql = "UPDATE thesis SET $field = $value WHERE thesis_id = $thesisId";

        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Invalid input data";
    }

    $conn->close();
} else {
    echo "Invalid request method";
}
?>
