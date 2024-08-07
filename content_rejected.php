<?php
require 'db.php'; // Ensure this file contains the PDO connection setup

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is content
if ($_SESSION['role_name'] !== 'content') {
    echo "Access denied.";
    exit;
}

// Define column names mapping
$columnNames = [
    'idea' => 'idea',  // Map 'idea' to 'Concept'
    'title' => 'Title',
    'caption' => 'Caption',
    // Add more mappings as needed
];

// Fetch content with status 'rejectedC'
$sql = "SELECT * FROM content WHERE status = 'rejectedC'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$content = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for updating records
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $id = $_POST['id'];
    
    // Prepare the SQL statement
    $sql = "UPDATE content SET concept = :concept, title = :title, caption = :caption, status = 'app1' WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    // Execute the update query
    $stmt->execute([
        ':concept' => $_POST['idea'],
        ':title' => $_POST['title'],
        ':caption' => $_POST['caption'],
        ':id' => $id
    ]);

    // Set a success message
    $_SESSION['successMessage'] = "Record updated successfully!";
    // Redirect to the same page to refresh
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Content</title>
</head>
<body>
<div class="container" id="contentContainer">
    <h1>Rejected Content</h1>
    <div id="notesDisplay">
        <?php if (empty($content)): ?>
            <p>No rejected content found.</p>
        <?php else: ?>
            <?php foreach ($content as $record): ?>
                <?php
                // Parse JSON data
                $parsedNotes = json_decode($record['notes'], true);
                ?>
                <div class="record">
                    <h3>Notes for ID: <?php echo htmlspecialchars($record['id']); ?></h3>

                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($record['id']); ?>">

                        <h4>Notes</h4>
                        <?php if (isset($parsedNotes['idea'])): ?>
                            <p><strong><?php echo htmlspecialchars($columnNames['idea']); ?>:</strong></p>
                            <input type="text" value="<?php echo htmlspecialchars($parsedNotes['idea']); ?>" disabled>

                            <?php if (isset($parsedNotes['concept'])): ?>
                                <p><strong>Idea:</strong></p> <!-- Changed label to "Idea" -->
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['concept']); ?>" disabled>
                            <?php endif; ?>

                            <?php if (isset($parsedNotes['title'])): ?>
                                <p><strong><?php echo htmlspecialchars($columnNames['title']); ?>:</strong></p>
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['title']); ?>" disabled>
                            <?php endif; ?>

                            <?php if (isset($parsedNotes['caption'])): ?>
                                <p><strong><?php echo htmlspecialchars($columnNames['caption']); ?>:</strong></p>
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['caption']); ?>" disabled>
                            <?php endif; ?>

                            <!-- Editable database column values -->
                            <h4>Database Values</h4>
                            <p><strong><?php echo htmlspecialchars('Idea (From DB):'); ?></strong></p>
                            <input type="text" name="idea" value="<?php echo htmlspecialchars($record['concept']); ?>">

                            <p><strong><?php echo htmlspecialchars($columnNames['title']) . ' (From DB):'; ?></strong></p>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($record['title']); ?>">

                            <p><strong><?php echo htmlspecialchars($columnNames['caption']) . ' (From DB):'; ?></strong></p>
                            <input type="text" name="caption" value="<?php echo htmlspecialchars($record['caption']); ?>">
                        <?php endif; ?>

                        <button type="submit">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for alert -->
<?php if (isset($_SESSION['successMessage'])): ?>
    <script>
        alert("<?php echo htmlspecialchars($_SESSION['successMessage']); ?>");
    </script>
    <?php unset($_SESSION['successMessage']); // Clear the message after displaying ?>
<?php endif; ?>

</body>
</html>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
        margin: 20px;
    }
    .container {
        max-width: 800px;
        margin: auto;
        padding: 20px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
        color: #d9534f;
    }
    .record {
        border: 1px solid #ddd;
        padding: 15px;
        margin: 10px 0;
        border-radius: 5px;
        background: #f9f9f9;
    }
    .record p {
        margin: 5px 0;
    }
    .record strong {
        color: #555;
    }
    .record input,
    .record textarea {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        box-sizing: border-box;
    }
    .record textarea {
        height: 100px;
        resize: vertical;
    }
</style>