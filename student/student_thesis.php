<?php
include("head_student.html");
include("project_base.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theses</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <h1>Η Διπλωματική Μου</h1>
    <table id="thesisTable">
        <thead>
            <tr>
                <th>Thesis ID NUMBER</th>
                <th>Title</th>
                <th>Professor Name</th>
                <th>Professor Surname</th>
                <th>Description</th>
                <th>State</th>
                <th>Thesis File</th>
                <th>Professor File</th>
                <th>URL</th>
                <th>ΠΡΑΚΤΙΚΟ</th>
            </tr>
        </thead>
        <tbody>
            <!-- AJAX θα εισαγάγει εδώ τις εγγραφές -->
        </tbody>
    </table>
    
    <br>
    <form id="uploadForm" enctype="multipart/form-data"> 
        ΕΠΕΛΕΞΕ ΑΡΧΕΙΟ
        <input type="file" id="pdfFile" name="pdfFile" accept="application/pdf" required>
        THESIS ID:
        <input type="text" id="thesis_id" name="thesis_id" placeholder="Enter Thesis ID" required>
        <button type="button" id="uploadButton">UPLOAD PDF FILE</button>
    </form>
    <div id="response"></div>
    <br>
   
    <form id="urlForm">
        <label for="url">Εισαγωγή URL:</label>
        <input type="text" id="url" name="url" required>
        <button type="submit" id="url_save" name="url_save">ΑΠΟΘΗΚΕΥΣΗ</button>
    </form>

    <div id="response_url"></div>


    <br>
    <hr>
    <h2>Επιλέξτε Καθηγητές για Τριμελή Επιτροπή</h2>
    <table id="profTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Topic</th>
                <th>Department</th>
                <th>university</th>
            </tr>
        </thead>
        <tbody>
            <!-- AJAX θα εισαγάγει εδώ τις εγγραφές -->
        </tbody>
    </table>
    
    <form action="../logout.php" method="post">
        <input class="but" type="submit" name="logout" value="LOGOUT">
    </form>

    <br>
    <form id="emailForm">
        <label for="email">Email Καθηγητή:</label>
        <input type="text" id="email" name="email" required>
        <button type="submit">Αποστολή</button>
    </form>
    <br>

    <div id="response_prof"></div>

    <form id="viewInvitationsForm">
        <button type="button" id="viewInvitationsButton">Προβολή Προσκλήσεων Με Χρονολογική Σειρά</button>
    </form>

    <table id="invitationsTable">
        <thead>
            <tr>
                <th>Professor ID</th>
                <th>Time of Invitation</th>
                <th>Approved</th>
            </tr>
        </thead>
        <tbody>
            <!-- Τα δεδομένα θα προστεθούν δυναμικά εδώ -->
        </tbody>
    </table>

    <div id="response"></div>

<script>
    function fetchThesisData() {
        // Κλήση AJAX για φόρτωση δεδομένων
        $.ajax({
            url: "fetch_thesis.php",
            method: "GET",
            dataType: "json",
            success: function(data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                // Γέμισμα πίνακα
                const tbody = $("#thesisTable tbody");
                tbody.empty();

                
                
                data.forEach(row => {
                    tbody.append(`
                        <tr>
                            <td>${row.thesis_id}</td>
                            <td>${row.title}</td>
                            <td>${row.prof_name}</td>
                            <td>${row.prof_surname}</td>
                            <td>${row.description}</td>
                            <td>${row.state}</td>
                            <td>
                                ${row.path ? 
                                    `<a href="${row.path}" target="_blank"><button>View File</button></a>` 
                                    : 
                                    `<p>No file uploaded</p>`}
                            </td>
                            <td>${row.prof_file ? 
                                    `<a href="${row.prof_file}" target="_blank"><button>View File</button></a>` 
                                    : 
                                    `<p>No file uploaded</p>`}</td>
                            <td>${row.links}</td>
                             <td>
                                ${row.state === 'completed' ? 
                    `<a href="../professor/praktiko.php?thesis_id=${row.thesis_id}" target="_blank">View Praktiko</a>` 
                    : 
                    "No File"}
                            </td>
                            </tr>
                    `);
                    if (row.state == 'reviewing' || row.state == 'active') {
                        $("#uploadForm").show(); // Εμφάνιση της φόρμας upload
                        $('#thesis_id').val(row.thesis_id); // Ορισμός του thesis_id
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        });
    }

    $(document).ready(function() {
        // Αρχική φόρτωση δεδομένων
        fetchThesisData();

        // Auto-refresh κάθε 5 δευτερόλεπτα
        setInterval(fetchThesisData, 500); // 500ms = 0.5 seconds
    });
</script>

<script>
    function fetchProfData() {
        // Κλήση AJAX για φόρτωση δεδομένων
        $.ajax({
            url: "fetch_prof.php",
            method: "GET",
            dataType: "json",
            success: function(prof) {
                if (prof.error) {
                    alert(prof.error);
                    return;
                }
                
                // Γέμισμα πίνακα
                const tbody = $("#profTable tbody");
                tbody.empty();
                
                prof.forEach(row => {
                    tbody.append(`
                        <tr>
                            <td>${row.id}</td>
                            <td>${row.name}</td>
                            <td>${row.surname}</td>
                            <td>${row.email}</td>
                            <td>${row.topic}</td>
                            <td>${row.department}</td>
                            <td>${row.university}</td>
                        </tr>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        });
    }

    $(document).ready(function() {
        // Αρχική φόρτωση δεδομένων
        fetchThesisData();
        fetchProfData();

        // Auto-refresh 
        setInterval(fetchProfData, 500); 
    });
</script>

<script>
    $(document).ready(function(){
        $("#emailForm").on("submit", function(e){
            e.preventDefault(); // Αποφυγή της ανανέωσης της σελίδας

            var email = $("#email").val();

            $.ajax({
                url: "student_save.php", // Θα στείλουμε τα δεδομένα στο student_save.php
                type: "POST",
                data: { email: email }, // Στέλνουμε το email στο PHP
                success: function(response){
                    // Εμφανίζουμε την απάντηση από το PHP
                    $("#response_prof").html(response);
                },
                error: function(xhr, status, error){
                    $("#response").html("Σφάλμα κατά τη διαδικασία.");
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("#viewInvitationsButton").on("click", function () {
            $.ajax({
                url: "student_inv_fetch.php", // Το σωστό αρχείο PHP
                method: "GET",
                dataType: "json",
                success: function (data) {
                    if (data.error) {
                        alert(data.error); // Εμφάνιση μηνύματος λάθους
                        return;
                    }

                    // Καθαρισμός του πίνακα
                    const tbody = $("#invitationsTable tbody");
                    tbody.empty();

                    // Γέμισμα πίνακα με τα δεδομένα
                    data.forEach(row => {
                        tbody.append(`
                            <tr>
                                <td>${row.professor_id}</td>
                                <td>${row.time}</td>
                                <td>${row.approved}</td>
                            </tr>
                        `);
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Σφάλμα κατά την προβολή των προσκλήσεων:", error);
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#uploadButton').click(function () {
            var formData = new FormData();
            var fileInput = $('#pdfFile')[0].files[0];
            var thesisId = $('#thesis_id').val();
            
            formData.append('pdfFile', fileInput);
            formData.append('thesis_id', thesisId);

            $.ajax({
                url: 'upload.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#response').html('<p style="color: green;">' + response + '</p>');
                },
                error: function (xhr, status, error) {
                    $('#response').html('<p style="color: red;">An error occurred: ' + error + '</p>');
                }
            });
        });
    });
</script>


<script>
        $(document).ready(function() {
            $("#urlForm").on("submit", function(e) {
            e.preventDefault(); // Αποφυγή ανανέωσης της σελίδας

                var url = $("#url").val();

                // Αποστολή μέσω AJAX στο save_links.php
                $.ajax({
                    url: "save_links.php", // Αποστολή δεδομένων για αποθήκευση
                    type: "POST",
                    data: { url: url },
                    success: function(response) {
                        $("#response_url").html(response); // Εμφάνιση απόκρισης από τον PHP
                    },
                    error: function(xhr, status, error) {
                        $("#response_url").html("Σφάλμα κατά την αποθήκευση του URL.");
                    }
                });
            });
        });
</script>

</body>
</html>