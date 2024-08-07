<?php
require 'db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    include "invaild.html";
    exit;
}

// Check if the user is content
if ($_SESSION['role_name'] !== 'content') {
    include "acessdenied.html";
    exit;
}

// Query to check for rejected content
$query = "SELECT client_name, COUNT(*) as rejected_count 
          FROM content 
          WHERE status = 'rejectedC' 
          GROUP BY client_name";
$stmt = $pdo->prepare($query);
$stmt->execute();
$rejectedContent = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine if there are rejected content rows
$hasRejectedContent = count($rejectedContent) > 0;
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
            cursor: <?php echo $hasRejectedContent ? 'pointer' : 'default'; ?>; /* Change cursor based on content presence */
            <?php if (!$hasRejectedContent) echo 'opacity: 0.6;'; ?> /* Gray out if no rejected content */
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
    </style>
</head>
<body>
    <div class="options-container">
        <div class="option" id="reviewRejectedContent">
            <h2>Review Rejected Content</h2>
            <p id="rejectedContentInfo">
                <?php 
                if ($hasRejectedContent) {
                    foreach ($rejectedContent as $content) {
                        echo 'Client: ' . htmlspecialchars($content['client_name']) . ' - Rejected Content: ' . htmlspecialchars($content['rejected_count']) . '<br>';
                    }
                } else {
                    echo 'No rejected content available.';
                }
                ?>
            </p>
            <?php if ($hasRejectedContent): ?>
                <a href="content_rejected.php">Click here</a>
            <?php endif; ?>
        </div>
        
        <div class="option">
            <h2>write content</h2>
            <!-- <p>This is a brief description of Option 2. It explains what this option entails and why it might be useful.</p> -->
            <a href="content.php" target="_blank">Click here for more details</a>
        </div>
        
        <div class="option">
            <h2>Option 3 Title</h2>
            <p>This is a brief description of Option 3. Learn more about this option and its benefits by clicking the link below.</p>
            <a href="https://example.com/option3" target="_blank">Click here for more details</a>
        </div>
    </div>

    <script>
        document.getElementById('reviewRejectedContent').addEventListener('click', function() {
            if (<?php echo json_encode($hasRejectedContent); ?>) {
                window.location.href = 'content_rejected.php';
            }
        });
    </script>
</body>
</html>
