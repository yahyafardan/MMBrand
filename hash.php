<?php
// The password you want to hash
$password = 'p5';

// Generate the password hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Output the hashed password
echo $hashedPassword;
INSERT INTO users (username, password_hash, role_name)
VALUES ('approvel1', '$2y$10$a4V7xkzFjqYoUA4CPq56/uodUjKcmfa14w19thYOGQSZlMAipO7va', 'app1');
?>
