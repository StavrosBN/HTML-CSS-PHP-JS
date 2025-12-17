<?php
// Include database connection
include 'db_connect.php';

// Validate query parameters (start_date, end_date, format)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$format = isset($_GET['format']) ? $_GET['format'] : 'json'; // Default to JSON if no format is specified

// Validate the format
if ($format !== 'json' && $format !== 'xml') {
    header("HTTP/1.1 400 Bad Request");
    echo "Invalid format. Please specify either 'json' or 'xml'.";
    exit();
}

// Prepare the SQL query to fetch announcements within the specified time range
$sql = "SELECT anc_id, title, description, datetime FROM anouncements WHERE 1=1";

if ($start_date) {
    $sql .= " AND datetime >= ?";
}
if ($end_date) {
    $sql .= " AND datetime <= ?";
}

$stmt = $conn->prepare($sql);

// Bind parameters if time range is provided
if ($start_date && $end_date) {
    $stmt->bind_param("ss", $start_date, $end_date);
} elseif ($start_date) {
    $stmt->bind_param("s", $start_date);
} elseif ($end_date) {
    $stmt->bind_param("s", $end_date);
}

$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// Now we start the output with the correct headers to prevent additional output
if ($format == 'json') {
    header('Content-Type: application/json');
    echo json_encode($announcements);
} elseif ($format == 'xml') {
    header('Content-Type: application/xml');
    
    // Start the XML structure
    $xml = new SimpleXMLElement('<announcements/>');

    // Add announcements to the XML structure
    foreach ($announcements as $announcement) {
        $announcement_xml = $xml->addChild('announcement');
        $announcement_xml->addChild('anc_id', $announcement['anc_id']);
        $announcement_xml->addChild('title', htmlspecialchars($announcement['title']));
        $announcement_xml->addChild('description', htmlspecialchars($announcement['description']));
        $announcement_xml->addChild('datetime', $announcement['datetime']);
    }

    // example localhost/secretary/endpoint.php?start_date=2025-01-01&end_date=2025-01-12&format=json
    echo $xml->asXML();
}
?>
