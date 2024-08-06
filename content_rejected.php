<?php
require 'db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is content
if ($_SESSION['role_name'] !== 'content') {
    echo "Access denied.";
    exit;
}
echo"hi";
?>