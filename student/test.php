<?php include("head_student.html"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Details</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
   <br>
    <form action="../logout.php" method="post">
        <input class="but" type="submit" name="logout" value="LOGOUT">
    </form>

    <h3>Επεξεργαστείτε τα στοιχεία για την εξέταση της Διπλωματικής Εργασίας</h3>
<form id="editForm">
    <label for="thesis_id">Thesis ID:</label><br>
    <input type="number" id="thesis_id" name="thesis_id" required><br><br>

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
    <br>
</form>

<p id="message"></p>

<script>
$(document).ready(function () {
    $("#saveButton").on("click", function () {
        const formData = {
            thesis_id: $("#thesis_id").val(),
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
});
</script>

</body>
</html>
