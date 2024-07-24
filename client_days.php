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
if (!isset($_REQUEST['client_name'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["error" => "Client name is required."]);
    exit;
}

$client_name = $_POST['client_name'];

try {
    // Fetch client dates
    $sql = "SELECT start_date, end_date FROM clients WHERE client_name = :client_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':client_name', $client_name, PDO::PARAM_STR);
    $stmt->execute();
    $dates = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dates) {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["error" => "Client not found."]);
        exit;
    }

    // Fetch posting days
    $sql_days = "SELECT days_of_posting FROM clients WHERE client_name = :client_name";
    $stmt_days = $pdo->prepare($sql_days);
    $stmt_days->bindParam(':client_name', $client_name, PDO::PARAM_STR);
    $stmt_days->execute();
    $posting_days_row = $stmt_days->fetch(PDO::FETCH_ASSOC);
    
    // Split days_of_posting into an array
    $days_of_posting = explode('-', $posting_days_row['days_of_posting']);
    
    // Return data as JSON
    echo json_encode([
        "start_date" => $dates['start_date'],
        "end_date" => $dates['end_date'],
        "posting_days" => $days_of_posting
    ]);

} catch (PDOException $e) {
    // Log and display database query errors
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>
