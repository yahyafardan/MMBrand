<?php
require 'db.php';

$clientName = isset($_POST['client_name']) ? $_POST['client_name'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';

if (!$clientName || !$month) {
    echo '<p>Invalid client or month specified.</p>';
    exit;
}

try {
    // Fetch records for the specific client and month
    $sql = "SELECT * FROM content WHERE client_name = :client_name AND month = :month";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['client_name' => $clientName, 'month' => $month]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        echo "<p>No records found for $clientName in $month.</p>";
        exit;
    }

    echo '<div class="clients-container">';
    echo "<h2>Content for $clientName - $month</h2>";
    

    // Create a single client container
    echo '<div class="client-container">';

    // Iterate over the records and generate HTML
    foreach ($records as $record) {
        echo '<div class="record-container">';
        echo '<p><strong>Type:</strong> ' . htmlspecialchars($record['type']) . '</p>';
        echo '<p><strong>Concept:</strong> ' . htmlspecialchars($record['concept']) . '</p>';
        echo '<p><strong>Caption:</strong> ' . htmlspecialchars($record['caption']) . '</p>';
        echo '<p><strong>Language:</strong> ' . htmlspecialchars($record['language']) . '</p>';
        echo '<p><strong>Last Updated:</strong> ' . htmlspecialchars($record['updated_at']) . '</p>';
        echo '<p><strong>Status:</strong> ' . htmlspecialchars($record['status']) . '</p>';
        echo '<div class="button-container">';
        echo '<a class="approve-btn" href="approve.php?id=' . htmlspecialchars($record['id']) . '">Approve</a>';
        echo '<a class="reject-btn" href="reject.php?id=' . htmlspecialchars($record['id']) . '">Reject</a>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>'; // Close client-container
    echo '</div>'; // Close clients-container

} catch (PDOException $e) {
    echo '<p>Error fetching records: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
<style>.clients-container {
    display: flex;
    flex-direction: column; /* Display clients in a column */
    padding: 20px;
}

.client-container {
    display: flex;
    flex-wrap: wrap; /* Allow records to wrap to the next line */
    gap: 20px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 20px; /* Space between client containers */
}

.client-container h3 {
    width: 100%; /* Make the client name take the full width of the container */
    margin-top: 0;
    margin-bottom: 10px; /* Space between client name and records */
}

.record-container {
    flex: 0 0 300px; /* Each record takes a fixed width and does not shrink */
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 15px; /* Space between record containers */
}

.record-container p {
    margin: 5px 0;
}

.button-container {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.approve-btn, .reject-btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    border-radius: 5px;
    text-align: center;
}

.approve-btn {
    background-color: #28a745;
}

.approve-btn:hover {
    background-color: #218838;
}

.reject-btn {
    background-color: #dc3545;
}

.reject-btn:hover {
    background-color: #c82333;
}

</style>
