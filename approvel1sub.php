<?php

// Include the database connection file
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the record ID and action from the POST request
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Validate inputs
    if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
        echo '<p>Invalid request.</p>';
        exit;
    }

    try {
        if ($action === 'approve') {
            $sql = "UPDATE content SET status = 'approvalIn2' WHERE id = :id";
        } elseif ($action === 'reject') {
            $sql = "UPDATE content SET status = 'rejected', notes = :notes WHERE id = :id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($action === 'reject') {
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        }

        $stmt->execute();

        echo '<p>Status updated successfully.</p>';

    } catch (PDOException $e) {
        // Log and display database query errors
        echo '<p>Error updating status: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p>Invalid request method.</p>';
}
?>
