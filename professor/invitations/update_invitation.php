<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json'); // Ensure response is JSON

// Debugging: Log raw input and $_POST
$rawInput = file_get_contents('php://input');
error_log("Raw Input: $rawInput");
error_log("POST data: " . print_r($_POST, true));

$inv_id = isset($_POST['inv_id']) ? intval($_POST['inv_id']) : 0;
$approval = isset($_POST['approval']) ? intval($_POST['approval']) : NULL;

// Debugging: Check parsed values
error_log("Parsed inv_id: $inv_id, approval: $approval");

if ($inv_id > 0 && ($approval === 0 || $approval === 1)) {
    $conn->begin_transaction();

    try {
        // Update the invitation status
        $sql = "UPDATE committee_invitations SET approved = ? WHERE inv_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement for approval update: " . $conn->error);
        }

        $stmt->bind_param("ii", $approval, $inv_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update the invitation status: " . $stmt->error);
        }
        $stmt->close();

        // Additional logic for approval
        if ($approval === 1) {
            $detailsQuery = "
                SELECT ci.thesis_id, p.name, p.surname, p.professor_id
                FROM committee_invitations ci
                JOIN professor p ON ci.professor_id = p.professor_id
                WHERE ci.inv_id = ?";
            $stmt = $conn->prepare($detailsQuery);

            if (!$stmt) {
                throw new Exception("Failed to prepare statement for fetching details: " . $conn->error);
            }

            $stmt->bind_param("i", $inv_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $details = $result->fetch_assoc();
            $stmt->close();

            if ($details) {
                $thesis_id = $details['thesis_id'];
                $member1_details = $details['professor_id'];

                // Execute the stored procedure
                $updateThesisQuery = "CALL update_member_id($thesis_id, $member1_details)";
                if (!$conn->query($updateThesisQuery)) {
                    throw new Exception("Failed to update the thesis member1_id: " . $conn->error);
                }
            } else {
                throw new Exception("Thesis or professor details not found.");
            }
        }

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Invitation status updated successfully."]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}

$conn->close();
?>
