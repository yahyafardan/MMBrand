<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to the database
        require 'db.php';


        $id = $_POST['id'];
        $action = $_POST['action'];

        if ($action === 'approve') {
            $status = 'design';
        } else if ($action === 'reject') {
            $status = 'rejected';
        } else {
            echo 'Invalid action';
            exit;
        }

        $sql = "UPDATE content SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['status' => $status, 'id' => $id]);

        echo 'success';
    } catch (PDOException $e) {
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
?>
