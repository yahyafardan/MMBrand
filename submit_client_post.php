<?php
// Database connection settings
require"db.php";

// Retrieve form data
$client_name = $_POST['client_name'];
$n_of_posts = $_POST['n_of_posts'];
$n_of_videos = $_POST['n_of_videos'];
$start_date = $_POST['start_date'];
$duration = $_POST['duration'];

// Process days of posting
$days_of_posting = isset($_POST['days_of_posting']) ? implode(',', $_POST['days_of_posting']) : '';

// Process hashtags
$hashtags = $_POST['hashtags'];

// Prepare SQL statement
$sql = "INSERT INTO client_posts (client_name, n_of_posts, n_of_videos, days_of_posting, hashtags, start_date, duration) 
        VALUES (:client_name, :n_of_posts, :n_of_videos, :days_of_posting, :hashtags, :start_date, :duration)";

$stmt = $pdo->prepare($sql);

// Bind parameters and execute
$stmt->bindParam(':client_name', $client_name);
$stmt->bindParam(':n_of_posts', $n_of_posts, PDO::PARAM_INT);
$stmt->bindParam(':n_of_videos', $n_of_videos, PDO::PARAM_INT);
$stmt->bindParam(':days_of_posting', $days_of_posting);
$stmt->bindParam(':hashtags', $hashtags);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':duration', $duration, PDO::PARAM_STR);

try {
    $stmt->execute();
    include"thankyouadmin.html";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
