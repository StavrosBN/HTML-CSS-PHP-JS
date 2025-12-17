<?php 
include("head_student.html");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Έλεγχος εγκυρότητας του session cookie
if (!isset($_COOKIE['user_session']) || $_COOKIE['user_session'] !== session_id()) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'student') {
    echo "Access denied. This page is for students only.";
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>


<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πληροφορίες Εξέτασης</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
   <br>
    <form action="../logout.php" method="post">
        <input class="but" type="submit" name="logout" value="LOGOUT">
    </form>

    <h3>Επεξεργαστείτε τα στοιχεία για την εξέταση της Διπλωματικής Εργασίας</h3>
    <h4>Η επεξεργασία είναι εφικτή μόνο αν η διπλωματική σας βρίσκεται σε REVIEWING στάδιο!</h4>

    <form id="editForm">
        <label for="exam_date">Ημερομηνία Εξέτασης (YYYY-MM-DD HH:MM:SS):</label><br>
        <input type="datetime-local" id="exam_date" name="exam_date" required><br><br>

        <label for="exam_place">Όνομα Αίθουσας/URL Meeting:</label><br>
        <input type="text" id="exam_place" name="exam_place" required><br><br>
        
        <label for="exam_mode">Χώρος (Φυσική ή Ηλεκτρονική Αίθουσα):</label><br>
        <select id="exam_mode" name="exam_mode" required>
            <option value="in_person">in_person</option>
            <option value="online">online</option>
        </select><br><br>
        
        <button type="button" id="saveButton">Save</button>
    </form>

    <p id="message"></p>

    <!-- Κουμπί για εμφάνιση εξετάσεων -->
    <button type="button" id="loadExamsButton">Εμφάνιση Εξετάσεων</button>

    <!-- Πίνακας εξετάσεων -->
    <h3>Πίνακας Εξετάσεων</h3>
    <table border="1" id="examinationTable">
        <thead>
            <tr>
                <th>Thesis ID</th>
                <th>Datetime</th>
                <th>Place</th>
                <th>Mode</th>
            </tr>
        </thead>
        <tbody>
            <!-- Δεδομένα θα προστεθούν δυναμικά μέσω AJAX -->
        </tbody>
    </table>

<script>
$(document).ready(function () {
    let minAllowedDate = null;

    // Ανάκτηση της ημερομηνίας που η διπλωματική μπήκε σε REVIEWING
    $.ajax({
        url: "date_fetch.php",
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const reviewDate = new Date(response.review_date);
                reviewDate.setDate(reviewDate.getDate() + 14); // Προσθήκη 14 ημερών
                minAllowedDate = reviewDate.toISOString().slice(0, 16);
                
                $("#exam_date").attr("min", minAllowedDate);
            } else {
                $("#message").text(response.message).css("color", "red");
            }
        },
        error: function () {
            $("#message").text("Σφάλμα κατά την ανάκτηση της ημερομηνίας.").css("color", "red");
        }
    });

    $("#saveButton").on("click", function () {
        const selectedDate = new Date($("#exam_date").val());

        if (minAllowedDate && selectedDate < new Date(minAllowedDate)) {
            $("#message").text("Η ημερομηνία πρέπει να είναι τουλάχιστον 14 ημέρες μετά την αλλαγή σε REVIEWING.")
                         .css("color", "red");
            return;
        }

        const formData = {
            date: $("#exam_date").val(),
            place: $("#exam_place").val(),
            mode: $("#exam_mode").val()
        };

        $.ajax({
            url: "update_exam_student.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                $("#message").text(response.message).css("color", response.success ? "green" : "red");
            },
            error: function () {
                $("#message").text("Σφάλμα κατά την αποθήκευση.").css("color", "red");
            }
        });
    });

    // Φόρτωση εξετάσεων
    $("#loadExamsButton").on("click", function () {
        $.ajax({
            url: "fetch_examTable_student.php",
            method: "GET",
            dataType: "json",
            success: function (response) {
                const tbody = $("#examinationTable tbody");
                tbody.empty();
                if (response.success) {
                    response.data.forEach(function (exam) {
                        tbody.append(`
                            <tr>
                                <td>${exam.thesis_id}</td>
                                <td>${exam.datetime}</td>
                                <td>${exam.place}</td>
                                <td>${exam.mode}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="4">Δεν υπάρχουν εξετάσεις.</td></tr>');
                }
            },
            error: function () {
                $("#message").text("Σφάλμα κατά την φόρτωση των εξετάσεων.").css("color", "red");
            }
        });
    });
});
</script>

</body>
</html>
