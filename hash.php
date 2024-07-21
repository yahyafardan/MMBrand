<?php
// The password you want to hash
$password = 'p4';

// Generate the password hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Output the hashed password
echo $hashedPassword;
// INSERT INTO users (username, password_hash, role_name)
// VALUES ('admin', '$2y$10$Q2QHTzK6X6EtRzXU8O8gi.1OAK.LfZAYAPyOsDpW3q7whEFeLLMbu', 'admin');
?>
