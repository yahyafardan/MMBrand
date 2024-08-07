<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    include "invaild.html";
    exit;
}

// Check if the user is an admin
if ($_SESSION['role_name'] !== 'social_media') {
    include "acessdenied.html";
    exit;
}

// Page content for admins
echo "Welcome, " . $_SESSION['username'] . "! This is the social_media page.";
?>
