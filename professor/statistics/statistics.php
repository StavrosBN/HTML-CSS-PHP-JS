<?php
include '../db_connect.php';
include '../../init.php';

// Fetch data for statistics
$user_id = $_SESSION['user_id'];

// Average completion time (in days) for theses supervised or as a committee member
$completion_query = "
    SELECT 
        AVG(TIMESTAMPDIFF(DAY, 
            (SELECT l1.datetime 
             FROM thesis_log l1 
             WHERE l1.thesis_id = t.thesis_id AND l1.action = 'insert' 
             ORDER BY l1.datetime ASC LIMIT 1),
            (SELECT l2.datetime 
             FROM thesis_log l2 
             WHERE l2.thesis_id = t.thesis_id AND l2.action = 'update' AND l2.new_value = 'completed' 
             ORDER BY l2.datetime DESC LIMIT 1)
        )) AS avg_completion_time
    FROM thesis t
    WHERE t.professor_id = ? OR t.member1_id = ? OR t.member2_id = ?
";
$stmt = $conn->prepare($completion_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$completion_result = $stmt->get_result()->fetch_assoc();
$avg_completion_time = $completion_result['avg_completion_time'] ?? 0;
$stmt->close();

// Average grades for theses supervised or as a committee member
$grade_query = "
    SELECT 
        AVG(t.grade) AS avg_grade
    FROM thesis t
    WHERE t.grade IS NOT NULL AND (t.professor_id = ? OR t.member1_id = ? OR t.member2_id = ?)
";
$stmt = $conn->prepare($grade_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$grade_result = $stmt->get_result()->fetch_assoc();
$avg_grade = $grade_result['avg_grade'] ?? 0;
$stmt->close();

// Total count of theses supervised or as a committee member
$count_query = "
    SELECT 
        COUNT(*) AS total_theses
    FROM thesis t
    WHERE t.professor_id = ? OR t.member1_id = ? OR t.member2_id = ?
";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$count_result = $stmt->get_result()->fetch_assoc();
$total_theses = $count_result['total_theses'] ?? 0;
$stmt->close();

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Statistics</title>
    <link rel="stylesheet" href="style_STATS1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</head>
<body>

    <nav>
        <button onclick="location.href='../index.php'">Home</button>
        <button onclick="location.href='../invitations/invitations.php'">Invitations</button>
        <button onclick="location.href='statistics.php'">Statistics</button>
    </nav>

    <h1>Thesis Statistics</h1>

    <div>
        <canvas id="completionChart"></canvas>
    </div>
    <div>
        <canvas id="gradeChart"></canvas>
    </div>
    <div>
        <canvas id="countChart"></canvas>
    </div>

    <script>
        const avgCompletionTime = <?php echo $avg_completion_time; ?>;
        const avgGrade = <?php echo $avg_grade; ?>;
        const totalTheses = <?php echo $total_theses; ?>;
        console.log(avgCompletionTime, avgGrade, totalTheses); // Check the values
renderCharts(avgCompletionTime, avgGrade, totalTheses);

        renderCharts(avgCompletionTime, avgGrade, totalTheses);
    </script>
</body>
</html>
