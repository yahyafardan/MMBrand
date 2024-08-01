<?php

// Include the database connection file
require 'db.php';

try {
    // Fetch records with status 'waiting for approval'
    $sql = "SELECT *, name FROM content WHERE status = 'waiting for approvalIn'";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        echo "<p>No records waiting for approvalIn.</p>";
        exit;
    }

    echo '<div class="container">';

    // Iterate over the records and generate HTML
    foreach ($records as $record) {
        echo '<div class="approvalIn-container">';
        echo '<p>' . htmlspecialchars($record['name']) . '</p>'; // Display record name
        echo '<div class="button-container">';
        echo '<a class="elementor-button elementor-button-link elementor-size-sm" href="#">Approved</a>';
        echo '<a class="elementor-button elementor-button-link elementor-size-sm" href="#">Reject</a>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

} catch (PDOException $e) {
    // Log and display database query errors
    echo '<p>Error fetching records: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
