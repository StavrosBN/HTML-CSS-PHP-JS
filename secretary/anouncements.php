<?php
session_start();

include 'db_connect.php'; // Ensure this file connects to your database

// Validate session and user role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the session cookie exists and is valid
if (!isset($_COOKIE['user_session']) || $_COOKIE['user_session'] !== session_id()) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'secretary') {
    echo "Access denied. This page is for the secretary only.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission to upload announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_announcement'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!empty($title) && !empty($description)) {
        // Prepare the statement
        $stmt = $conn->prepare("INSERT INTO anouncements (title, description, user) VALUES (?, ?, ?)");
        
        // Check if preparation failed
        if ($stmt === false) {
            // Output error and stop execution if there's an issue with the query
            die('MySQL prepare error: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssi", $title, $description, $user_id);

        if ($stmt->execute()) {
            echo "<p>Announcement uploaded successfully!</p>";
            header("Location: anouncements.php");
        } else {
            echo "<p>Error uploading announcement: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>Both title and description are required.</p>";
    }
}

// Handle deletion of announcements
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM anouncements WHERE anc_id = ?");
    
    // Check if preparation failed
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<p>Announcement deleted successfully!</p>";
    } else {
        echo "<p>Error deleting announcement: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload and Display Announcements</title>
    <link rel="stylesheet" href="secretary.css">
</head>
<body>

    <nav>
        <button onclick="location.href='secretary.php'">Home</button>
        <button onclick="location.href='json.php'">Students/Professors Datasheet Upload</button>
        <button onclick="location.href='anouncements.php'">Thesis Announcements</button>
    </nav>
    
    <form action='../logout.php' method='POST'>
        <button type='submit' class='logout-button'>Logout</button>
    </form>

    <h1>Upload Announcement</h1>
    <form method="POST" action="">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="5" required></textarea>
        <br>
        <button type="submit" name="upload_announcement">Upload</button>
    </form>

    <h1>Existing Announcements</h1>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT anc_id, title, description FROM anouncements");
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>";
                echo "<a href='?delete_id=" . $row['anc_id'] . "' onclick='return confirm(\"Are you sure?\");'>Delete</a>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>
