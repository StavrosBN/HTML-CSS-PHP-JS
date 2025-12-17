<?php
session_start();

include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the session cookie exists and is valid
if (!isset($_COOKIE['user_session']) || $_COOKIE['user_session'] !== session_id()) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'professor') {
    echo "Access denied. This page is for professors only.";
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$filterState = isset($_GET['state']) ? $_GET['state'] : '';
$filterRole = isset($_GET['role']) ? $_GET['role'] : '';

$stateFilterSQL = $filterState ? "AND t.state = '$filterState'" : '';
$roleFilterSQL = $filterRole === 'MASTER' ? "AND t.professor_id = $user_id" : 
                ($filterRole === 'MEMBER' ? "AND (t.member1_id = $user_id OR t.member2_id = $user_id)" : '');

$sql = "
    SELECT 
    t.*, 
    p.name AS professor_name,
    p.surname AS professor_surname,
    p1.name AS member1_name, 
    p1.surname AS member1_surname, 
    p2.name AS member2_name, 
    p2.surname AS member2_surname,
    CASE 
        WHEN t.professor_id = $user_id THEN 'MASTER'
        WHEN t.member1_id = $user_id THEN 'MEMBER'
        WHEN t.member2_id = $user_id THEN 'MEMBER'
        ELSE 'none'
    END AS user_role
FROM 
    thesis t
LEFT JOIN 
    professor p1 ON t.member1_id = p1.professor_id
LEFT JOIN 
    professor p ON t.professor_id = p.professor_id
LEFT JOIN 
    professor p2 ON t.member2_id = p2.professor_id
WHERE 
    (t.professor_id = $user_id OR t.member1_id = $user_id OR t.member2_id = $user_id)
    $stateFilterSQL
    $roleFilterSQL;
";
$result = $conn->query($sql);

// Fetch students who don't have a thesis assigned
$studentQuery = "SELECT s.student_id, s.name FROM student s LEFT JOIN thesis t ON s.student_id = t.student_id WHERE t.student_id IS NULL;";
$studentResult = $conn->query($studentQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Management</title>
    <link rel="stylesheet" href="style_PROF.css?v=<?= time() ?>">
    <script src="script.js?v=<?= time() ?>" defer></script>
</head>
<body>

 <!-- Navigation Bar -->
 <nav>
        <button onclick="location.href='index.php'">Home</button>
        <button onclick="location.href='invitations/invitations.php'">Invitations</button>
        <button onclick="location.href='statistics/statistics.php'">Statistics</button>
</nav>

    <h1>Thesis Management</h1>
    
    <a href="export.php?format=csv" class="export-btn csv-btn">Export in CSV</a>
<a href="export.php?format=json" class="export-btn json-btn">Export in JSON</a>
<form action='../logout.php' method='POST'>
            <button type='submit' class='logout-button'>Logout</button>
        </form>

        <form method="GET" action="index.php" id="filterForm">
    <label for="stateFilter">State:</label>
    <select name="state" id="stateFilter" onchange="document.getElementById('filterForm').submit()">
        <option value="">All</option>
        <option value="pending" <?= $filterState === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="active" <?= $filterState === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="reviewing" <?= $filterState === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
        <option value="completed" <?= $filterState === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="cancelled" <?= $filterState === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <label for="roleFilter">Role:</label>
    <select name="role" id="roleFilter" onchange="document.getElementById('filterForm').submit()">
        <option value="">All</option>
        <option value="MASTER" <?= $filterRole === 'MASTER' ? 'selected' : '' ?>>MASTER</option>
        <option value="MEMBER" <?= $filterRole === 'MEMBER' ? 'selected' : '' ?>>MEMBER</option>
    </select>
</form>

    <table>
        <thead>
            <tr>
                <th></th>
                <th>Role</th>
                <th>Title</th>
                <th>Description</th>
                <th>Full Name</th>
                <th>Student</th>
                <th>State</th>
                <th>Master's Grade</th>
                <th>Member 1 Grade</th>
                <th>Member 2 Grade</th>
                <th>Grade</th>
                <th>Committee Member 1</th>
                <th>Committee Member 2</th>
                <th>Presentation</th>
                <th>Student's File</th>
                <th>Praktiko</th>
                <th>Student's Links</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>


    <?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Check if the presentation path is set
        $prof_path = $row['prof_path'] 
            ? "<a href='{$row['prof_path']}' target='_blank'>View PDF</a>" 
            : "No File";

        $presentationPath = $row['presentation_path'] 
            ? "<a href='../student/{$row['presentation_path']}' target='_blank'>View PDF</a>" 
            : "No File";

        
        $praktiko = ($row['state'] === 'completed') 
            ? "<a href='praktiko.php?thesis_id={$row['thesis_id']}' target='_blank'>View Praktiko</a>" 
            : "No File";
        


        $studentName = "";
        if ($row['student_id'] > 0) {
            $studentQuery = "SELECT name, surname FROM student WHERE student_id = {$row['student_id']}";
            $studentResultForThesis = $conn->query($studentQuery);
            if ($studentResultForThesis && $studentResultForThesis->num_rows > 0) {
                $studentData = $studentResultForThesis->fetch_assoc();
                $studentName = $studentData['name'] . ' ' . $studentData['surname'];
            }
        }

        $studentDropdown = "<label for='student_search_{$row['thesis_id']}'>Search by ID: </label>
        <input type='text' id='student_search_{$row['thesis_id']}' onkeyup='filterStudentDropdown({$row['thesis_id']})' placeholder='Search by ID'>
        <select id='student_dropdown_{$row['thesis_id']}' onchange='updateStudent({$row['thesis_id']})'>
            <option value=''>Select Student</option>
            <option value='NULL'>-- No Student --</option>";

        $availableStudentsQuery = "SELECT student_id, name, surname FROM student WHERE student_id NOT IN (SELECT student_id FROM thesis WHERE student_id IS NOT NULL)";
        $availableStudentsResult = $conn->query($availableStudentsQuery);
            
        if ($availableStudentsResult && $availableStudentsResult->num_rows > 0) {
            while ($student = $availableStudentsResult->fetch_assoc()) {
                $selected = ($row['student_id'] == $student['student_id']) ? "selected" : "";
                $studentDropdown .= "<option value='{$student['student_id']}' {$selected}>{$student['name']} {$student['surname']} - {$student['student_id']}</option>";
            }
        }
        
        $studentDropdown .= "</select>";

        // Add a Notes icon (pen)
        $notesButton = "<button class='notes-btn' onclick='showNotesPopup({$row['thesis_id']})'>&#9998;</button>";
        $descButton = "<button class='desc-btn' onclick='showDescPopup({$row['thesis_id']})'>&#9998;</button>";

        echo "
        <tr>
                <td>
                    <button onclick=\"showThesisHistory({$row['thesis_id']})\" class='history-btn' title='Show History'>H</button>
                    <button onclick=\"showPopup({$row['thesis_id']})\">In</button>
                </td>
                <td><strong>{$row['user_role']}</strong></td>
                <td onclick=\"makeEditable(this, {$row['thesis_id']}, 'title')\">{$row['title']}</td>
                <td>
                    {$descButton}
                </td>
                <td>{$row['professor_name']} {$row['professor_surname']}</td>
                <td>
                    <strong>{$studentName}</strong>
                    <form id='updateStudentForm_{$row['thesis_id']}' action='update_student.php' method='POST'>
                        <span style='font-size: 12px;'>{$studentDropdown}</span>
                        <input type='hidden' name='thesis_id' value='{$row['thesis_id']}' />
                    </form>
                </td>
                <td>
                    " . ($row['state'] === 'active' ? "
                    <form action='update_state.php' method='POST' style='display: inline;'>
                        <input type='hidden' name='thesis_id' value='{$row['thesis_id']}' />
                        <input type='hidden' name='new_state' value='reviewing' />
                        {$row['state']}
                        <button type='submit' class='state-btn green'>Toggle to Reviewing</button>
                    </form>
                    " : "
                    <button class='state-btn gray' disabled>" . htmlspecialchars(ucfirst($row['state'])) . "</button>
                    ") . "
                </td>
                <td " . ($row['user_role'] == 'MASTER' ? "onclick=\"makeEditable(this, '{$row['thesis_id']}', 'grade1')\"" : "") . ">
                    {$row['grade1']}
                </td>
                <td " . ($row['user_role'] == 'MEMBER' ? "onclick=\"makeEditable(this, '{$row['thesis_id']}', 'grade2')\"" : "") . ">
                    {$row['grade2']}
                </td>
                <td " . ($row['user_role'] == 'MEMBER' && $row['grade2'] >= 0 && $row['grade2'] <= 100 ? "onclick=\"makeEditable(this, '{$row['thesis_id']}', 'grade3')\"" : "") . ">
                    {$row['grade3']}
                </td>
                <td>{$row['grade']}</td>
                <td><strong>{$row['member1_name']} {$row['member1_surname']}</strong></td>
                <td><strong>{$row['member2_name']} {$row['member2_surname']}</strong></td>
                <td>
                    {$prof_path}
                    <form action='upload_pdf.php' method='POST' enctype='multipart/form-data' style='display:inline;'>
                        <input type='hidden' name='thesis_id' value='{$row['thesis_id']}' />
                        <input type='file' name='pdf_file' accept='application/pdf' style='display:inline;' />
                        <button type='submit'>Upload</button>
                    </form>
                </td>
                <td>
                    {$presentationPath}
                </td>
                <td>
                    {$praktiko}
                </td>
                 <td>
                    {$row['links']}
                </td>
                <td>
                    {$notesButton}
                </td>
                 <td>
        <button 
            class='delete-btn' 
            onclick='deleteThesis({$row["thesis_id"]})' 
            title='Delete Thesis'>X
        </button>
    </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='13'>No data found</td></tr>";
}


echo "<tr>
    <form id='addNewRowForm' enctype='multipart/form-data'>
        <td></td>
        <td><input type='text' name='title' placeholder='Enter Title' required></td>
        <td><input type='text' name='description' placeholder='Enter Description' required></td>
        <td>
            <select name='student_id'>
                <option value=''>Select Student</option>
                <option value=''>-- No Student --</option>";
$studentResult->data_seek(0); // Reset result pointer
while ($student = $studentResult->fetch_assoc()) {
    echo "<option value='{$student['student_id']}'>{$student['name']} - {$student['student_id']}</option>";
}
echo "      </select>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            <input type='file' name='pdf_file' accept='application/pdf' style='display:inline;' />
            <button type='button' id='addThesisButton'>Add</button>
        </td>
    </form>
  </tr>";
?>

</tbody>
</table>
<div id="popup" class="popup">
    <span class="close-btn" onclick="closePopup()">×</span>
    <h3>Invitations</h3>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Approved</th>
                <th>Datetime of Invitation</th>
                <th>Datetime of Answer</th>
            </tr>
        </thead>
        <tbody id="popup-content">
        </tbody>
    </table>
</div>

<div id="thesis-history-popup" class="thesis-history-popup">
    <span class="thesis-history-close-btn" onclick="closeThesisHistoryPopup()">×</span>
    <div id="thesis-history-popup-content"></div>
</div>

<div id="thesis-history-popup-overlay" class="thesis-history-popup-overlay"></div>


<div id="notes-popup" class="popup">
    <span class="close-btn" onclick="closeNotesPopup()">×</span>
    <h3>Add Notes</h3>
    <textarea id="notes-textarea" placeholder="Write your notes here..." rows="5" cols="50"></textarea>
    <button onclick="saveNotes()">Save Notes</button>
</div>

<div id="desc-popup" class="popup">
    <span class="close-btn" onclick="closeDescPopup()">×</span>
    <h3>Add Description</h3>
    <textarea id="desc-textarea" placeholder="Write your description here..." rows="5" cols="50"></textarea>
    <button onclick="saveDesc()">Save Description</button>
</div>

</body>
</html>

<?php
?>
