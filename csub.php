<?php
require 'db.php'; // Ensure db.php is correctly configured and includes PDO setup

// Enable error reporting for debugging (remove these lines if you want to disable it)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the raw POST data
$jsonData = file_get_contents('php://input');

// Decode the JSON data into a PHP associative array
$data = json_decode($jsonData, true);

// Prepare the SQL statements
$insertStmt = $pdo->prepare("
    INSERT INTO content (type, concept, caption, language, post_date, month, social_media_platforms, can_be_sponsored, status, client_name)
    VALUES (:type, :concept, :caption, :language, :post_date, :month, :social_media_platforms, :can_be_sponsored, :status, :client_name)
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
            $type = isset($value['type']) ? $value['type'] : '';
            $concept = isset($value['Concept']) ? $value['Concept'] : '';
            $caption = isset($value['caption']) ? $value['caption'] : '';
            $language = isset($value['language']) ? $value['language'] : ''; // Added language field
            $social_media_platforms = isset($value['socialMedia']) && is_array($value['socialMedia']) ? implode(', ', $value['socialMedia']) : '';
            $can_be_sponsored = isset($value['sponsors']) ? $value['sponsors'] : '';
            $status = isset($value['state']) ? $value['state'] : '';

            // Check if status is 'saved'
            if ($status === 'saved') {
                // Update the status to 'approvalIn'
                $updateStmt->execute([
                    ':client_name' => $client_name,
                    ':post_date' => $formatted_date
                ]);

                // Set the status to 'approvalIn' for insertion
                $status = 'approvalIn';
            }

            // Execute the insert statement
            $insertStmt->execute([
                ':type' => $type,
                ':concept' => $concept,
                ':caption' => $caption,
                ':language' => $language, // Added language field
                ':post_date' => $formatted_date,
                ':month' => $month_name,
                ':social_media_platforms' => $social_media_platforms,
                ':can_be_sponsored' => $can_be_sponsored,
                ':status' => $status,
                ':client_name' => $client_name
            ]);

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
?>
