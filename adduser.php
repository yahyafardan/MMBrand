<?php
// Include the database connection filesession_start();

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
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role_name = $_POST['role_name'];
    
    // Validate passwords
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
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
            $success = "User added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
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
</head>
<body>
    <h2>Add User</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="adduser.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <label for="role_name">Role:</label>
        <select id="role_name" name="role_name" required>
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
            <option value="viewer">Viewer</option>
        </select><br><br>

        <input type="submit" value="Add User">
    </form>
</body>
</html>
