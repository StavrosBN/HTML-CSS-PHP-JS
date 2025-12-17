<?php
include 'db_connect.php';

$thesis_id = $_GET['thesis_id']; 


$sql = "SELECT text FROM thesis WHERE thesis_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $thesis_id);
$stmt->execute();
$result = $stmt->get_result();

$response = '';
if ($row = $result->fetch_assoc()) {
    $response = $row['text'];  
}

$stmt->close();
$conn->close();

echo $response;
?>
