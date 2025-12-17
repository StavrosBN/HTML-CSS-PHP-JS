<?php
include '../db_connect.php';
include '../../init.php';

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        ci.inv_id,
        ci.thesis_id,
        ci.time,
        t.title AS thesis_title,
        s.name AS student_name,
        s.surname AS student_surname,
        s.student_id
    FROM 
        committee_invitations ci
    JOIN 
        thesis t ON ci.thesis_id = t.thesis_id
    JOIN 
        student s ON t.student_id = s.student_id
    WHERE 
        ci.approved IS NULL
    AND ci.professor_id = $user_id;
";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitations</title>
    <link rel="stylesheet" href="style_INV.css?v=<?= time() ?>" defer>
    <script src="script_inv.js?v=<?= time() ?>" defer></script>
</head>
<body>

 <!-- Navigation Bar -->
 <nav>
        <button onclick="location.href='../index.php'">Home</button>
        <button onclick="location.href='invitations.php'">Invitations</button>
        <button onclick="location.href='../statistics/statistics.php'">Statistics</button>
</nav>
<form action='../logout.php' method='POST'>
            <button type='submit' class='logout-button'>Logout</button>
        </form>
    <h1>Committee Invitations</h1>
    <table>
        <thead>
            <tr>
                <th>Thesis Name</th>
                <th>Time</th>
                <th>Student Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr id='row-{$row['inv_id']}'>
                <td>{$row['thesis_title']}</td>
                <td>{$row['time']}</td>
                <td>{$row['student_name']} {$row['student_surname']} - {$row['student_id']}</td>
                <td>
                    <button class='green-btn' onclick='updateApproval({$row['inv_id']}, 1)'>✔</button>
                    <button class='red-btn' onclick='updateApproval({$row['inv_id']}, 0)'>✖</button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No invitations found.</td></tr>";
    }
    ?>
</tbody>

    </table>
</body>
</html>
