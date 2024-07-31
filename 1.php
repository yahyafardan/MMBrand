<?php
header('Content-Type: application/json; charset=UTF-8');

// Create an array of data
$responseData = [
    'status' => 'success',
    'message' => 'Data received correctly',
    'data' => [
        'key' => 'value'
    ]
];

// Encode the data as JSON and send it to the client
echo json_encode($responseData);
?>
