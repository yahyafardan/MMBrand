<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    include "invaild.html";
    exit;
}

// Check if the user is an app1
if ($_SESSION['role_name'] !== 'content') {
    include "acessdenied.html";
    exit;
}
require 'db.php'; // Ensure db.php is correctly configured and includes PDO setup

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the raw POST data
$jsonData = file_get_contents('php://input');

// Decode the JSON data into a PHP associative array
$data = json_decode($jsonData, true);

// Prepare the SQL statements
$insertStmt = $pdo->prepare("
    INSERT INTO content (type, concept, title, caption, language, post_date, month, social_media_platforms, sponsored, status, client_name, contributors)
    VALUES (:type, :concept, :title, :caption, :language, :post_date, :month, :social_media_platforms, :sponsored, :status, :client_name, :contributors)
");

$updateStmt = $pdo->prepare("
    UPDATE content
    SET status = 'approvalIn'
    WHERE client_name = :client_name AND post_date = :post_date AND status = 'saved'
");

// Initialize an array to hold formatted data for success responses
$formattedData = [];

// Check if JSON decoding is successful
if (json_last_error() === JSON_ERROR_NONE) {
    // Loop through each key in the decoded data
    foreach ($data as $key => $value) {
        // Extract client name and event date from the key
        list($client_name, $event_date) = explode('_', $key, 2);

        // Convert the event_date to a DateTime object
        $dateTime = DateTime::createFromFormat('Y-m-d', $event_date);

        if ($dateTime) {
            // Format the date as DD-MM-YYYY
            $formatted_date = $dateTime->format('d-m-Y');

            // Extract month in MMMM format
            $month_name = $dateTime->format('F');

            // Extract and format the necessary values
            $type = $value['type'] ?? '';
            $concept = $value['Concept'] ?? '';
            $title = $value['title'] ?? ''; // Added title extraction
            $caption = $value['caption'] ?? '';
            $language = $value['language'] ?? '';
            $social_media_platforms = isset($value['socialMedia']) && is_array($value['socialMedia']) ? implode(', ', $value['socialMedia']) : '';
            $sponsored = $value['sponsors'] ?? 'no'; // Default to 'no'
            $status = $value['state'] ?? '';

            // Extract contributors data from the value array
            $role_name = $value['role_name'] ?? 'default_role';
            $username = $value['username'] ?? 'default_username';
            $contributors = json_encode([$role_name => $username]);

            // Debugging: Print extracted values
            error_log("Debug - Type: $type, Concept: $concept, Title: $title, Caption: $caption, Language: $language, Social Media: $social_media_platforms, Sponsored: $sponsored, Status: $status, Contributors: $contributors");

            // Check if status is 'saved'
            if ($status === 'saved') {
                // Update the status to 'app1'
                $updateStmt->execute([
                    ':client_name' => $client_name,
                    ':post_date' => $formatted_date
                ]);

                // Set the status to 'app1' for insertion
                $status = 'app1';
            }

            // Execute the insert statement
            $insertStmt->execute([
                ':type' => $type,
                ':concept' => $concept,
                ':title' => $title, // Insert title
                ':caption' => $caption,
                ':language' => $language,
                ':post_date' => $formatted_date,
                ':month' => $month_name,
                ':social_media_platforms' => $social_media_platforms,
                ':sponsored' => $sponsored,
                ':status' => $status,
                ':client_name' => $client_name,
                ':contributors' => $contributors
            ]);

            // Check for SQL errors
            if ($insertStmt->errorCode() !== '00000') {
                error_log("Insert Error: " . implode(' ', $insertStmt->errorInfo()));
                echo json_encode(['status' => 'error', 'message' => 'Failed to insert data']);
                exit;
            }

            // Add data to the formattedData array
            $formattedData[] = [
                'client_name' => $client_name,
                'post_date' => $formatted_date,
                'status' => $status
            ];
        } else {
            // Handle the case where the date could not be parsed
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
            exit;
        }
    }

    // Send a JSON response with status and formatted data
    $response = [
        'status' => 'success',
        'message' => 'Data processed successfully',
        'formattedData' => $formattedData
    ];
    echo json_encode($response);
} else {
    // Send an error response if JSON decoding failed
    $errorMessage = json_last_error_msg(); // Get the JSON error message
    echo json_encode(['status' => 'error', 'message' => 'Failed to decode JSON', 'error_message' => $errorMessage]);
}