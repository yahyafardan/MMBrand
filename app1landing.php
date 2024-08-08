<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    include "invaild.html";
    exit;
}

// Check if the user is an app1
if ($_SESSION['role_name'] !== 'app1') {
    include "acessdenied.html";
    exit;
}

// Include the database connection file
require 'db.php';

try {
    // Query to fetch content with status 'app1' and their client names
    $sql = "SELECT client_name, COUNT(*) as content_count 
            FROM content 
            WHERE status = 'app1' 
            GROUP BY client_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $client_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database query errors
    echo "Database query failed: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 40px; /* Increased space between options */
            width: 100%;
            max-width: 1200px; /* Further increased maximum width */
            padding: 40px; /* Increased padding */
            box-sizing: border-box;
        }
        .option {
            background: #fff;
            border-radius: 12px; /* Larger border-radius */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15); /* Larger shadow */
            padding: 40px; /* Increased padding */
            text-align: center;
            font-size: 20px; /* Larger font size */
        }
        .option h2 {
            margin-top: 0;
            color: #333;
            font-size: 28px; /* Larger heading size */
        }
        .option p {
            color: #666;
            font-size: 18px; /* Larger text size */
        }
        .option a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 20px; /* Larger link size */
        }
        .option a:hover {
            text-decoration: underline;
        }
        ul {
            list-style-type: none; /* Remove bullets from the list */
            padding: 0;
            margin: 0;
        }
        li {
            font-size: 18px; /* Larger text size for list items */
            margin-bottom: 10px; /* Space between list items */
        }
    </style>
</head>
<body>
    <div class="options-container">
        <div class="option">
            <div>
                <?php if ($client_data): ?>
                    <h3>Review Content:</h3>
                    <ul>
                        <?php foreach ($client_data as $data): ?>
                            <li>
                                Client: <?php echo htmlspecialchars($data['client_name']); ?> - 
                                Number of content: <?php echo htmlspecialchars($data['content_count']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="approvel1.php">Click here to Review content</a>
                <?php else: ?>
                    <p>No content currently under review.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="option">
            <h2>Option 2 Title</h2>
            <p>This is a brief description of Option 2. It explains what this option entails and why it might be useful.</p>
            <a href="https://example.com/option2" target="_blank">Click here for more details</a>
        </div>
        
        <div class="option">
            <h2>Option 3 Title</h2>
            <p>This is a brief description of Option 3. Learn more about this option and its benefits by clicking the link below.</p>
            <a href="https://example.com/option3" target="_blank">Click here for more details</a>
        </div>
    </div>
</body>
</html>
