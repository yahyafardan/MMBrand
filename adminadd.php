<?php
// Include the database connection file
require 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if the user is an admin
if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error {
            color: red;
            background-color: #f8d7da;
        }
        .success {
            color: green;
            background-color: #d4edda;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add User</h2>

        <div id="message" class="message" style="display: none;"></div>

        <form id="addUserForm">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="role_name">Role:</label>
            <select id="role_name" name="role_name" required>
                <option value="" disabled selected>Select a role</option>
                <option value="admin">Admin</option>
                <option value="editor">content</option>
                <option value="viewer">desing</option>
                <option value="viewer">app1</option>
                <option value="viewer">app2</option>

            </select>

            <input type="submit" value="Add User">
        </form>
    </div>

    <script>
        document.getElementById('addUserForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Retrieve form values
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Check if passwords match
            if (password !== confirmPassword) {
                const messageDiv = document.getElementById('message');
                messageDiv.style.display = 'block';
                messageDiv.textContent = 'Passwords do not match';
                messageDiv.className = 'message error';
                return; // Stop further processing
            }

            // Collect form data
            const formData = new FormData(this);

            fetch('adminaddsub.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                messageDiv.style.display = 'block';
                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message success';

                    // Clear the form fields
                    document.getElementById('addUserForm').reset();
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
