<?php
// Include database connection
include 'db_connect.php';

// Check if the form has been submitted via POST and contains the necessary parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesis_id']) && isset($_POST['arithmos_protokolou'])) {
    // Sanitize and validate inputs
    $thesis_id = intval($_POST['thesis_id']); // Ensure the thesis_id is an integer
    $arithmos_protokolou = trim($_POST['arithmos_protokolou']); // Remove whitespace

    if ($thesis_id > 0 && !empty($arithmos_protokolou)) {
        // Prepare the update statement
        $new_state = 'Completed'; // Hardcoded state update to 'Completed'

        // Prepare the SQL query to update the thesis state and protocol number
        $stmt = $conn->prepare("UPDATE thesis SET state = ?, ap = ? WHERE thesis_id = ?");
        $stmt->bind_param('ssi', $new_state, $arithmos_protokolou, $thesis_id);

        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            // Redirect back to the secretary page with success status
            header("Location: secretary.php?status=success");
            exit();
        } else {
            // Output error if the update fails
            echo "Error updating state: " . $conn->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        // Handle invalid input
        echo "Invalid thesis ID or protocol number.";
    }
} else {
    // If the request method is not POST or required fields are missing, show an error
    echo "Invalid request. Request method: " . $_SERVER['REQUEST_METHOD'];
}
?>
