<?php
// Ensure headers are set to handle JSON response
header('Content-Type: application/json');

// Get POST data
$data = file_get_contents('php://input');

// Debugging output
error_log("Raw POST Data: $data");

// Decode JSON data
$decodedData = json_decode($data, true);

// Check for JSON decode errors
if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Decode Error: " . json_last_error_msg());
}

// Initialize the response array
$response = [
    'status' => 'error',
    'message' => 'No data received',
    'formattedData' => ''
];

// Check if data contains the 'data' key
if (isset($decodedData['data'])) {
    // Data from JavaScript
    $jsonData = $decodedData['data'];

    // Decode JSON data
    $decodedData = json_decode($jsonData, true);

    // Check for JSON decode errors
    if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error (data): " . json_last_error_msg());
    }

    // Prepare an array to hold the formatted data
    $formattedData = [];

    // Loop through each item in the decoded data
    foreach ($decodedData as $key => $value) {
        // Format the key and value
        $formattedData[] = "Key: $key\n" . print_r($value, true) . "\n----------";
    }

    // Convert the formatted data array to a string
    $formattedDataString = implode("\n", $formattedData);

    // Update response array
    $response = [
        'status' => 'success',
        'formattedData' => $formattedDataString
    ];

} else {
    error_log("No 'data' key found in POST data");
}

// Print the response as JSON
echo json_encode($response);
?>
