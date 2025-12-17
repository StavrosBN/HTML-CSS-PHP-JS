<?php
include 'db_connect.php';

// Ensure UTF-8 encoding
$conn->set_charset("utf8");

// Get thesis_id from the URL (default to null if not provided)
$thesis_id = isset($_GET['thesis_id']) ? intval($_GET['thesis_id']) : null;

// Check if thesis_id is valid
if ($thesis_id) {
    $sql = "SELECT 
                t.title, t.ap, t.grade, t.grade1, t.grade2, t.grade3, 
                s.name AS student_name, s.surname AS student_surname, 
                p1.name AS advisor_name, p1.surname AS advisor_surname, 
                p2.name AS committee1_name, p2.surname AS committee1_surname, 
                p3.name AS committee2_name, p3.surname AS committee2_surname, 
                e.datetime, e.place 
            FROM thesis t
            JOIN student s ON t.student_id = s.student_id
            JOIN professor p1 ON t.professor_id = p1.professor_id
            JOIN professor p2 ON t.member1_id = p2.professor_id
            JOIN professor p3 ON t.member2_id = p3.professor_id
            JOIN examination e ON t.thesis_id = e.thesis_id
            WHERE t.thesis_id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc(); // Fetch data only once

    $student_name = $row["student_name"] . " " . $row["student_surname"];
    $room = $row["place"];
    $date = date("d-m-Y", strtotime($row["datetime"]));
    $day = date("l", strtotime($row["datetime"]));
    $time = date("H:i", strtotime($row["datetime"]));
    $thesis_title = $row["title"];
    $advisor = $row["advisor_name"] . " " . $row["advisor_surname"];
    $committee1 = $row["committee1_name"] . " " . $row["committee1_surname"];
    $committee2 = $row["committee2_name"] . " " . $row["committee2_surname"];
    $decision = ($row["grade"] >= 5) ? "Εγκρίνεται" : "Απορρίπτεται";
    $grade = $row["grade"];
    $session_number = $row["ap"];
} else {
    // Placeholder values if no data found
    $student_name = $session_number = $room = $date = $day = $time = $thesis_title = $advisor = $committee1 = $committee2 = "................................";
    $decision = "................................";
    $grade = "................................";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πρακτικό Συνεδρίασης</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            line-height: 1.6;
        }
        .center {
            text-align: center;
            font-weight: bold;
        }
        .underline {
            border-bottom: 1px dotted black;
            display: inline-block;
            min-width: 200px;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            padding: 10px;
            border: 1px solid black;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="center">ΠΡΑΚΤΙΚΟ ΣΥΝΕΔΡΙΑΣΗΣ</div>
    <div class="center">ΤΗΣ ΤΡΙΜΕΛΟΥΣ ΕΠΙΤΡΟΠΗΣ</div>
    <div class="center">ΓΙΑ ΤΗΝ ΠΑΡΟΥΣΙΑΣΗ ΚΑΙ ΚΡΙΣΗ ΤΗΣ ΔΙΠΛΩΜΑΤΙΚΗΣ ΕΡΓΑΣΙΑΣ</div>
    <br>
    <p>του/της φοιτητή/φοιτήτρια <span class="underline"><?php echo $student_name; ?></span></p>
    <p>Η συνεδρίαση πραγματοποιήθηκε στην αίθουσα <span class="underline"><?php echo $room; ?></span> στις <span class="underline"><?php echo $date; ?></span> ημέρα και ώρα <span class="underline"><?php echo $time; ?></span>.</p>
    <p>Στην συνεδρίαση είναι παρόντα τα μέλη της Τριμελούς Επιτροπής:</p>
    <ol>
        <li><span class="underline"><?php echo $advisor; ?></span></li>
        <li><span class="underline"><?php echo $committee1; ?></span></li>
        <li><span class="underline"><?php echo $committee2; ?></span></li>
    </ol>
    <p>οι οποίοι ορίσθηκαν από την Συνέλευση του ΤΜΗΥΠ, στην συνεδρίασή της με αριθμό <span class="underline"><?php echo $session_number; ?></span>.</p>
    <p>Ο/Η φοιτητής/φοιτήτρια <span class="underline"><?php echo $student_name; ?></span> ανέπτυξε το θέμα της Διπλωματικής του/της Εργασίας, με τίτλο <span class="underline"><?php echo $thesis_title; ?></span>.</p>
    <p>Στην συνέχεια υποβλήθηκαν ερωτήσεις στον υποψήφιο από τα μέλη της Τριμελούς Επιτροπής και τους άλλους 
παρευρισκόμενους, προκειμένου να διαμορφώσουν σαφή άποψη για το περιεχόμενο της εργασίας, για την 
επιστημονική συγκρότηση του μεταπτυχιακού φοιτητή. </p>
    <p>Μετά το τέλος της ανάπτυξης της εργασίας και των ερωτήσεων, ο υποψήφιος αποχωρεί.</p>
    <p>Ο Επιβλέπων καθηγητής κ. <span class="underline"><?php echo $advisor; ?></span> προτείνει στα μέλη της Τριμελούς Επιτροπής να ψηφίσουν για το αν εγκρίνεται η διπλωματική εργασία του <span class="underline"><?php echo $student_name; ?>.</p>
    <p>Τα μέλη της Τριμελούς Επιτροπής, ψηφίζουν κατά αλφαβητική σειρά:</p>
    <ol>
        <li><span class="underline"><?php echo $advisor; ?></span></li>
        <li><span class="underline"><?php echo $committee1; ?></span></li>
        <li><span class="underline"><?php echo $committee2; ?></span></li>
    </ol>
    <p>υπέρ της έγκρισης της Διπλωματικής Εργασίας του φοιτητή <span class="underline"><?php echo $student_name; ?></span>επειδή θεωρούν επιστημονικά επαρκή και το 
    περιεχόμενό της ανταποκρίνεται στο θέμα που του δόθηκε. </p>
    <p>Μετά την έγκριση, ο εισηγητής κ. <span class="underline"><?php echo $advisor; ?></span> προτείνει στα μέλη της Τριμελούς Επιτροπής να απονεμηθεί στον/στην φοιτητή/φοιτήτρια κ. <span class="underline"><?php echo $student_name; ?></span> ο βαθμός <span class="underline"><?php echo $grade; ?></span>.</p>
    <table class="signature-table">
        <tr>
            <td class="center">ΟΝΟΜΑΤΕΠΩΝΥΜΟ</td>
            <td class="center">ΙΔΙΟΤΗΤΑ</td>
        </tr>
        <tr>
            <td><span class="underline"><?php echo $advisor; ?></span></td>
            <td>Επιβλέπων Καθηγητής</td>
        </tr>
        <tr>
            <td><span class="underline"><?php echo $committee1; ?></span></td>
            <td>Μέλος Επιτροπής</td>
        </tr>
        <tr>
            <td><span class="underline"><?php echo $committee2; ?></span></td>
            <td>Μέλος Επιτροπής</td>
        </tr>
    </table>
    <p>Μετά την έγκριση και την απονομή του βαθμού <span class="underline"><?php echo $grade; ?></span>, η Τριμελής Επιτροπή προτείνει να προχωρήσει στη διαδικασία για να ανακυρήξει τον κ. <span class="underline"><?php echo $student_name; ?></span>, σε 
διπλωματούχο του Προγράμματος Σπουδών του «ΤΜΗΜΑΤΟΣ ΜΗΧΑΝΙΚΩΝ, ΗΛΕΚΤΡΟΝΙΚΩΝ 
ΥΠΟΛΟΓΙΣΤΩΝ ΚΑΙ ΠΛΗΡΟΦΟΡΙΚΗΣ ΠΑΝΕΠΙΣΤΗΜΙΟΥ ΠΑΤΡΩΝ» και να του απονέμει το Δίπλωμα 
Μηχανικού Η/Υ το οποίο αναγνωρίζεται ως Ενιαίος Τίτλος Σπουδών Μεταπτυχιακού Επιπέδου.</p>
</body>
</html>

