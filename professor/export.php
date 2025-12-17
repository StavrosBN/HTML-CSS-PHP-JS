<?php
include 'db_connect.php';

$format = $_GET['format'] ?? 'csv';

// Fetch data
$query = "SELECT * FROM thesis";
$result = $conn->query($query);

if ($format === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=theses.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, array_keys($result->fetch_assoc())); // Add headers
    $result->data_seek(0); // Reset pointer

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
} elseif ($format === 'json') {
    header("Content-Type: application/json");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
}
?>
