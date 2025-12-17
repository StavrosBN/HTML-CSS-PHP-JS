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

// SQL query corrected
$sql = "
    SELECT 
        t.*,
        p.name AS professor_name,
        p.surname AS professor_surname,
        p1.name AS member1_name, 
        p1.surname AS member1_surname, 
        p2.name AS member2_name, 
        p2.surname AS member2_surname,
        l.datetime AS log_datetime
    FROM 
        thesis t
    LEFT JOIN 
        professor p ON t.professor_id = p.professor_id
    LEFT JOIN 
        professor p1 ON t.member1_id = p1.professor_id
    LEFT JOIN 
        professor p2 ON t.member2_id = p2.professor_id
    LEFT JOIN 
        (
            SELECT 
                thesis_id, MAX(datetime) AS datetime
            FROM 
                thesis_log
            WHERE 
                new_value = 'active'
            GROUP BY 
                thesis_id
        ) l ON t.thesis_id = l.thesis_id
    WHERE 
        t.state = 'active' OR t.state = 'reviewing';
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary View</title>
    <link rel="stylesheet" href="secretary.css?v=<?= time() ?>">
    <script src="script.js?v=<?= time() ?>" defer></script>
</head>
<body>

<nav>
    <button onclick="location.href='secretary.php'">Home</button>
    <button onclick="location.href='json.php'">Students/Professors Datasheet Upload</button>
    <button onclick="location.href='anouncements.php'">Thesis Anouncements</button>
</nav>

<h1>Secretary View</h1>

<form action='../logout.php' method='POST'>
    <button type='submit' class='logout-button'>Logout</button>
</form>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Student</th>
            <th>State</th>
            <th>Professor Name</th>
            <th>Committee Member 1</th>
            <th>Committee Member 2</th>
            <th>Active Runtime</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $studentName = "N/A";
        if ($row['student_id'] > 0) {
            $studentQuery = "SELECT name, surname FROM student WHERE student_id = {$row['student_id']}";
            $studentResultForThesis = $conn->query($studentQuery);
            if ($studentResultForThesis && $studentResultForThesis->num_rows > 0) {
                $studentData = $studentResultForThesis->fetch_assoc();
                $studentName = $studentData['name'] . ' ' . $studentData['surname'];
            }
        }

        // Description button
        $descButton = "<button class='desc-btn' onclick='showDescPopup({$row['thesis_id']})'>&#9998;</button>";

        echo "
        <tr>
            <td>{$row['title']}</td>
            <td>{$descButton}</td>
            <td>{$studentName}</td>
            <td>";

        // If state is "Reviewing", show button
        if ($row['state'] == 'reviewing') {
            echo "
                {$row['state']}
                <form method='post' style='display:inline;' onsubmit='submitForm({$row['thesis_id']}); return false;'>
                    <input type='hidden' name='thesis_id' value='{$row['thesis_id']}'>
                    <button type='submit' style='background-color: green; color: white; border: none; padding: 5px 10px; cursor: pointer;'>
                        Toggle to Completed
                    </button>
                </form>";
        } else {
            echo $row['state'];
        }

        echo "</td>
            <td>{$row['professor_name']} {$row['professor_surname']}</td>
            <td>{$row['member1_name']} {$row['member1_surname']}</td>
            <td>{$row['member2_name']} {$row['member2_surname']}</td>
            <td>
                <span class='runtime' data-start='{$row['log_datetime']}'></span>
            </td>
            <td>
                <button class='delete-thesis-button' title='Delete Thesis' data-thesis-id={$row['thesis_id']}>X</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No data found</td></tr>";
}
?>


<div id="desc-popup" class="popup">
    <span class="close-btn" onclick="closeDescPopup()">×</span>
    <h3>Description</h3>
    <p id="desc-textarea" style="white-space: pre-wrap;"></p>
</div>

    </tbody>
</table>

</body>
</html>
