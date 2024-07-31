<?php
require "db.php"; // Ensure this file contains your database connection setup

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $selectedClient = $_POST['selectedClient'] ?? '';
    $eventID = $_POST['eventID'] ?? '';
    $eventDate = $_POST['eventDate'] ?? '';

    // Process the data from local storage
    $localStorageData = [];
    foreach ($_POST as $key => $value) {
        // Skip keys that are not part of the local storage data
        if (!in_array($key, ['selectedClient', 'eventID', 'eventDate'])) {
            $localStorageData[$key] = $value;
        }
    }

    // For debugging purposes (remove in production)
    error_log('Selected Client: ' . $selectedClient);
    error_log('Event ID: ' . $eventID);
    error_log('Event Date: ' . $eventDate);
    error_log('Local Storage Data: ' . print_r($localStorageData, true));

    // Example of processing data: Insert into database
    $conn = new mysqli('hostname', 'username', 'password', 'database');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Prepare an SQL statement to insert the data
    $stmt = $conn->prepare("INSERT INTO your_table (selected_client, event_id, event_date, local_storage_key, local_storage_value) VALUES (?, ?, ?, ?, ?)");
    foreach ($localStorageData as $key => $value) {
        $stmt->bind_param('sssss', $selectedClient, $eventID, $eventDate, $key, $value);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();

    // Return a success response
    echo 'Data received and processed successfully.';

    // Optionally redirect
    // header('Location: success_page.html');
} else {
    // Handle error for non-POST requests
    echo 'Invalid request method.';
}
?>
