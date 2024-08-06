<?php
// Include the database connection file
include 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $user = $_POST['user'];
    $password = $_POST['pass'];

    // Step 1: Validate input
    if (empty($user) || empty($password)) {
        echo "Both fields are required.";
        exit;
    }
    
    // Step 2: Prepare a SQL statement to fetch the user data
    $sql = "SELECT * FROM users WHERE username = :user";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user', $user, PDO::PARAM_STR);

    // Step 3: Execute the statement
    $stmt->execute();

    // Step 4: Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Step 5: Check if user exists
    if (!$user) {
        echo "Invalid username or password.";
        exit;
    }

    // Step 6: Verify the password
    $passwordVerified = password_verify($password, $user['password_hash']);
    if (!$passwordVerified) {
        echo "Invalid username or password.";
        exit;
    }

    // Step 7: Start a session and store user information
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_name'] = $user['role_name'];

 // Step 8: Redirect to the protected page based on the role
switch ($_SESSION['role_name']) {
    case "admin":
        header("Location: admin.php");
        break;
    case "content":
        header("Location: content.php");
        break;
    case "design":
        header("Location: design.php");
        break;
    case "social_media":
        header("Location: social_media.php");
        break;
    case "client":
        header("Location: client.php");
        break;
    case "app1":
        header("Location: app1landing.php");
        break;
    case "app2":
        header("Location: app2landing.php");
        break;
    default:
        echo "Invalid role.";
        exit;
}


    exit;
} else {
    echo "Invalid request method.";
}
?>
