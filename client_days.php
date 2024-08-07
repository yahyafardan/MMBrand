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
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'db.php';

$client_name = $_POST['client_name'];

try {
    // Fetch client details
    $sql = "SELECT start_date, end_date, days_of_posting, hashtags, n_of_posts, n_of_videos, language 
            FROM clients 
            WHERE client_name = :client_name";
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

    // Convert months to 'MMMM' format for comparison
    $month_labels = array_map(function($month) {
        $date = DateTime::createFromFormat('m,Y', $month);
        return $date->format('F'); // 'MMMM'
    }, $months);

    // Fetch months from the content table excluding approvalIn status
    $sql_content = "SELECT DISTINCT month
                    FROM content 
                    WHERE client_name = :client_name AND status != 'approvalIn'";
    $stmt_content = $pdo->prepare($sql_content);
    $stmt_content->bindParam(':client_name', $client_name, PDO::PARAM_STR);
    $stmt_content->execute();
    $content_months = $stmt_content->fetchAll(PDO::FETCH_COLUMN);

    // Convert content months to 'MMMM' format for comparison
    $content_month_labels = array_map('ucfirst', $content_months); // Capitalize first letter for consistency

    // Fetch months with approvalIn status
    $sql_approval = "SELECT DISTINCT month
                     FROM content 
                     WHERE client_name = :client_name AND status = 'approvalIn'";
    $stmt_approval = $pdo->prepare($sql_approval);
    $stmt_approval->bindParam(':client_name', $client_name, PDO::PARAM_STR);
    $stmt_approval->execute();
    $approval_months = $stmt_approval->fetchAll(PDO::FETCH_COLUMN);

    // Convert approval months to 'MMMM' format for comparison
    $approval_month_labels = array_map('ucfirst', $approval_months); // Capitalize first letter for consistency

    // Filter months to exclude those with records in the content table or with approvalIn status
    $filtered_month_labels = array_diff($month_labels, $content_month_labels, $approval_month_labels);

    // Convert filtered months back to 'MM,YYYY' format
    $filtered_months = array_values(array_filter($months, function($month) use ($filtered_month_labels) {
        $date = DateTime::createFromFormat('m,Y', $month);
        return in_array($date->format('F'), $filtered_month_labels);
    }));

    // Return data as JSON
    echo json_encode([
        "start_date" => $client_data['start_date'],
        "end_date" => $client_data['end_date'],
        "hashtags" => $client_data['hashtags'],
        "posting_days" => $days_of_posting,
        "months" => $filtered_months, // Ensuring it's an indexed array
        "n_of_posts" => $client_data['n_of_posts'],
        "n_of_videos" => $client_data['n_of_videos'],
        "languages" => $client_data['language']
    ]);

} catch (PDOException $e) {
    // Log and display database query errors
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
