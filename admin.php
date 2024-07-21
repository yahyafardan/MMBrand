<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is an admin
if ($_SESSION['role_name'] !== 'admin') {
    echo "Access denied.";
    exit;
}

// Page content for admins
// echo "Welcome, " . $_SESSION['username'] . "! This is the admin page.";
require "admin.html";
?>
