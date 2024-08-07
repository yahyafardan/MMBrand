<?php
// Include the database connection file
require 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    include "invaild.html";
    exit;
}

// Check if the user is an admin
if ($_SESSION['role_name'] !== 'admin') {
    include "acessdenied.html";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role_name = $_POST['role_name'];

    // Validate passwords
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    } else {
        // Hash the password using bcrypt
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert record into the users table
        $sql = "INSERT INTO users (username, password_hash, role_name, created_at) VALUES (:username, :password_hash, :role_name, NOW())";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => $password_hash,
                ':role_name' => $role_name
            ]);
            echo json_encode(['success' => true, 'message' => 'User added successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error adding user: ' . $e->getMessage()]);
        }
    }
}
?>
