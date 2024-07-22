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
        echo "h";
        exit;
    }

    // Step 7: Start a session and store user information
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_name'] = $user['role_name'];

    // Step 8: Redirect to the protected page based on the role
    if ($_SESSION['role_name'] == "admin") {

        header("Location: admin.php");

    } elseif ($_SESSION['role_name']=="content") {

        header("Location: content.php"); // Redirect users to a content page

    } elseif($_SESSION['role_name']=="design"){

        header("Location: design.php"); // Redirect users to a designer page

    } elseif($_SESSION['role_name']=="social_media"){

        header("Location: social_media.php"); // Redirect to users to a social media page

    } elseif($_SESSION['role_name']=="client"){

        header("Location:client.php"); // Redirect to users to a client page
    exit;

} else {

    echo "Invalid request method.";
}
}
?>