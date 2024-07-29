<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and has the correct role
// if (!isset($_SESSION['username']) || $_SESSION['role_name'] !== 'content') {
//     header("HTTP/1.1 403 Forbidden");
//     echo json_encode(["error" => "Access denied."]);
//     exit;
// }

// Include the database connection file
require 'db.php';

// Check if the PDO connection was successful
// if (!isset($pdo)) {
//     header("HTTP/1.1 500 Internal Server Error");
//     echo json_encode(["error" => "Database connection failed."]);
//     exit;
// }

// Check if the client_name parameter is set via POST or GET
// if (!isset($_REQUEST['client_name'])) {
//     header("HTTP/1.1 400 Bad Request");
//     echo json_encode(["error" => "Client name is required."]);
//     exit;
// }

$client_name = //"yahya"; 
 $_POST['client_name'];
try {
    // Fetch client dates and hashtag
    $sql = "SELECT start_date, end_date, days_of_posting, hashtags FROM clients WHERE client_name = :client_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':client_name', $client_name, PDO::PARAM_STR);
    $stmt->execute();
    $client_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client_data) {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["error" => "Client not found."]);
        exit;
    }

    // Split days_of_posting into an array
    $days_of_posting = explode(',', $client_data['days_of_posting']);

    // Process start_date and end_date to get a list of months in MM,YYYY format
    $start_date = new DateTime($client_data['start_date']);
    $end_date = new DateTime($client_data['end_date']);
    $months = [];

    // Adjust end_date to the end of the month
    $end_date->modify('last day of this month');

    while ($start_date <= $end_date) {
        $months[] = $start_date->format('m,Y'); // Format as 'MM,YYYY'
        $start_date->modify('first day of next month');
    }

    // Return data as JSON
    echo json_encode([
        "start_date" => $client_data['start_date'],
        "end_date" => $client_data['end_date'],
        "hashtags" => $client_data['hashtags'],
        "posting_days" => $days_of_posting,
        "months" => $months // Updated format
    ]);

} catch (PDOException $e) {
    // Log and display database query errors
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
