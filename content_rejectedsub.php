<?php
require 'db.php'; // Ensure this file contains the PDO connection setup

session_start();

// Ensure this script is only executed for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $recordId = $_POST['record_id'];
    $title = $_POST['title'];    // Use the title value for the concept
    $caption = $_POST['caption'];
    $clientName = $_POST['client_name'];
    $month = $_POST['month'];

    // Update the specific record with new values, using title for concept
    $sql = "UPDATE content SET concept = :concept, title = :title, caption = :caption WHERE id = :record_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':concept', $title);  // Use the title for concept
    $stmt->bindParam(':title', $title);    // Update title as provided
    $stmt->bindParam(':caption', $caption);
    $stmt->bindParam(':record_id', $recordId);

    if ($stmt->execute()) {
        // Update the status of the specific record to 'pendingC'
        $updateStatusSql = "UPDATE content SET status = 'pendingC' WHERE id = :record_id";
        $updateStatusStmt = $pdo->prepare($updateStatusSql);
        $updateStatusStmt->bindParam(':record_id', $recordId);

        if ($updateStatusStmt->execute()) {
            // Check if all records within the month and for the same client are 'pendingC'
            $checkSql = "SELECT COUNT(*) FROM content WHERE month = :month AND client_name = :client_name AND status != 'pendingC'";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':month', $month);
            $checkStmt->bindParam(':client_name', $clientName);
            $checkStmt->execute();

            $remainingRecordsCount = $checkStmt->fetchColumn();

            // If no records are left that are not 'pendingC', update status to 'app1'
            if ($remainingRecordsCount == 0) {
                $updateStatusSql = "UPDATE content SET status = 'app1' WHERE month = :month AND client_name = :client_name";
                $updateStatusStmt = $pdo->prepare($updateStatusSql);
                $updateStatusStmt->bindParam(':month', $month);
                $updateStatusStmt->bindParam(':client_name', $clientName);

                if ($updateStatusStmt->execute()) {
                    // Return success response
                    echo json_encode(['success' => true]);
                } else {
                    // Return error response
                    echo json_encode(['success' => false, 'error' => 'Error updating status to \'app1\'.']);
                }
            } else {
                // Return success response indicating that the update to 'app1' was not necessary
                echo json_encode(['success' => true, 'message' => 'Records updated to \'pendingC\'.']);
            }
        } else {
            // Return error response
            echo json_encode(['success' => false, 'error' => 'Error updating status to \'pendingC\'.']);
        }
    } else {
        // Return error response
        echo json_encode(['success' => false, 'error' => 'Error updating record.']);
    }
    exit;
}

// If not a POST request, respond with an error
echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
exit;
?>
