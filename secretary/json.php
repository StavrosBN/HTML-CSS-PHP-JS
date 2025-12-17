
<?php
session_start();

include 'db_connect.php';

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



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['jsonFile'])) {
    $fileTmpPath = $_FILES['jsonFile']['tmp_name'];
    $jsonData = file_get_contents($fileTmpPath);
    $data = json_decode($jsonData, true);
    
    if ($data) {
        echo "<h2>Students</h2><table border='1'>
              <tr><th>ID</th><th>Name</th><th>Surname</th><th>Email</th></tr>";
        
        foreach ($data['students'] as $student) {
            $sql = "INSERT INTO student (student_id, student_AM, name, surname, street, number, city, postcode, father_name, landline, mobile, email, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
        student_AM = VALUES(student_AM), 
        name = VALUES(name), 
        surname = VALUES(surname), 
        street = VALUES(street), 
        number = VALUES(number), 
        city = VALUES(city), 
        postcode = VALUES(postcode), 
        father_name = VALUES(father_name), 
        landline = VALUES(landline), 
        mobile = VALUES(mobile), 
        email = VALUES(email), 
        password = VALUES(password)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssssssss", $student['id'], $student['student_number'], $student['name'], $student['surname'], 
                $student['street'], $student['number'], $student['city'], $student['postcode'], $student['father_name'], 
                $student['landline_telephone'], $student['mobile_telephone'], $student['email'], $student['email']);
            $stmt->execute();
            
            echo "<tr><td>{$student['id']}</td><td>{$student['name']}</td><td>{$student['surname']}</td><td>{$student['email']}</td></tr>";
        }
        echo "</table>";
        
        echo "<h2>Professors</h2><table border='1'>
              <tr><th>ID</th><th>Name</th><th>Surname</th><th>Email</th></tr>";
        
        foreach ($data['professors'] as $professor) {
            $sql = "INSERT INTO professor (professor_id, name, surname, email, topic, landline, mobile, department, university, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
        name = VALUES(name), 
        surname = VALUES(surname), 
        email = VALUES(email), 
        topic = VALUES(topic), 
        landline = VALUES(landline), 
        mobile = VALUES(mobile), 
        department = VALUES(department), 
        university = VALUES(university), 
        password = VALUES(password)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssssss", $professor['id'], $professor['name'], $professor['surname'], $professor['email'], 
                $professor['topic'], $professor['landline'], $professor['mobile'], $professor['department'], $professor['university'], 
                $professor['email']);
            $stmt->execute();
            
            echo "<tr><td>{$professor['id']}</td><td>{$professor['name']}</td><td>{$professor['surname']}</td><td>{$professor['email']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Invalid JSON file.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="secretary.css?v=<?= time() ?>">
    <script src="script.js?v=<?= time() ?>" defer></script>
</head>
    <title>Upload JSON</title>
</head>
<body>

<nav>
    <button onclick="location.href='secretary.php'">Home</button>
    <button onclick="location.href='json.php'">Students/Professors Datasheet Upload</button>
    <button onclick="location.href='anouncements.php'">Thesis Anouncements</button>
</nav>

    <h1>Upload JSON File</h1>
    
    
    <form action='../logout.php' method='POST'>
    <button type='submit' class='logout-button'>Logout</button>
</form>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="jsonFile" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
