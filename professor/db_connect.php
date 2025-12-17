<?php
$conn = new mysqli('localhost', 'root', '', 'project24');

// Check connection
if ($conn->connect_error) {
    die('Connect Error (' . $conn->connect_errno . ') ' . $conn->connect_error);
}
?>