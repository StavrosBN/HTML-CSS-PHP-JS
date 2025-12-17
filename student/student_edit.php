<?php
include("head_student.html");
include("project_base.php");

// Έλεγχος αν υπάρχει ήδη ενεργή συνεδρία
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Εξασφάλιση ότι ο χρήστης είναι συνδεδεμένος και είναι φοιτητής
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit;
}

// Παίρνουμε το student_id από τη συνεδρία
$student_id = $_SESSION['user_id'];

// Φέρνουμε τα υπάρχοντα δεδομένα του φοιτητή
$sql = "SELECT * FROM student WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <h1>Ελέγξετε/Επεξεργαστείτε τα στοιχεία σας</h1>
    <form id="editForm">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>"><br><br>

        <label for="landline">Landline:</label><br>
        <input type="tel" id="landline" name="landline" value="<?php echo htmlspecialchars($student['landline']); ?>"><br><br>
        
        <label for="mobile">Mobile Number:</label><br>
        <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($student['mobile']); ?>"><br><br>
        
        <label for="street">Street:</label><br>
        <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($student['street']); ?>"><br><br>
        
        <label for="number">Number:</label><br>
        <input type="number" id="number" name="number" value="<?php echo htmlspecialchars($student['number']); ?>"><br><br>
        
        <label for="city">City:</label><br>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($student['city']); ?>"><br><br>
        
        <label for="postcode">Post Code:</label><br>
        <input type="text" id="postcode" name="postcode" value="<?php echo htmlspecialchars($student['postcode']); ?>"><br><br>
        
        <button type="button" id="saveButton">Save</button>
        <br>
        
    </form>
    <form action="../logout.php" method="post">
        <input class="but" type="submit" name="logout" value="LOGOUT">
    </form>
    
    <p id="message"></p>

    <script>
        $(document).ready(function() {
            $("#saveButton").on("click", function() {
                // Παίρνουμε τα δεδομένα από τη φόρμα
                const formData = {
                    email: $("#email").val(),
                    landline: $("#landline").val(),
                    mobile: $("#mobile").val(),
                    street: $("#street").val(),
                    number: $("#number").val(),
                    city: $("#city").val(),
                    postcode: $("#postcode").val(),

                    student_id: <?php echo $student_id; ?>
                };

                // Κλήση AJAX
                $.ajax({
                    url: "update_student.php",
                    method: "POST",
                    data: formData,
                    dataType: "json", // Αναμένουμε απάντηση JSON
                    success: function(response) {
                        // Εμφάνιση μηνύματος
                        $("#message").text(response.message).css("color", response.success ? "green" : "red");
                    },
                    error: function() {
                        $("#message").text("An error occurred. Please try again.").css("color", "red");
                    }
                });
            });
        });                                    
    </script>
</body>
</html>
