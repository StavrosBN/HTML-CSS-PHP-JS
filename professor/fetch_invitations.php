<?php
include 'db_connect.php';
include '../init.php';

$thesisId = isset($_GET['thesis_id']) ? intval($_GET['thesis_id']) : 0;

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];


$sql = "
    SELECT 
        CONCAT(p.name, ' ', p.surname) AS Full_Name, 
        ci.approved AS Approved, 
        ci.time AS Invitation_Time, 
        COALESCE(
            (SELECT cl.datetime 
             FROM committee_log cl 
             WHERE cl.inv_id = ci.inv_id 
               AND cl.field_of_change = 'approved'
             ORDER BY cl.datetime DESC
             LIMIT 1), 
            ci.time
        ) AS Answer_Time
    FROM 
        committee_invitations ci
    LEFT JOIN 
        professor p ON ci.professor_id = p.professor_id
    WHERE 
        ci.thesis_id = $thesisId 
    ORDER BY 
        Answer_Time DESC;
";


$result = $conn->query($sql);

if ($result === false) {
    echo 'Error executing query: ' . $conn->error;
    exit;
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approved = $row['Approved'] == '1' ? 'Yes' : ($row['Approved'] == '0' ? 'No' : 'Not Yet');
        echo "<tr>
                <td>{$row['Full_Name']}</td>
                <td>{$approved}</td>
                <td>{$row['Invitation_Time']}</td>
                <td>{$row['Answer_Time']}</td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No invitations found</td></tr>";
}
?>
