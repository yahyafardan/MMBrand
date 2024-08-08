<?php
require 'db.php'; // Ensure this file contains the PDO connection setup

session_start();

if (!isset($_SESSION['username'])) {
    include "invalid.html";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve posted data
    $clientName = $_POST['client_name'];
    $month = $_POST['month'];
}

if ($_SESSION['role_name'] !== 'content') {
    include "accessdenied.html";
    exit;
}

// Define column names mapping
$columnNames = [
    'idea' => 'Idea',
    'title' => 'Title',
    'caption' => 'Caption',
];

// Fetch content with status 'rejectedC' and filter by month and client name
$sql = "SELECT * FROM content WHERE status = 'rejectedC' AND client_name = :client_name AND month = :month";
$stmt = $pdo->prepare($sql);

// Bind the parameters from the posted data
$stmt->bindParam(':client_name', $clientName);
$stmt->bindParam(':month', $month);

// Execute the query
$stmt->execute();
$content = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Content</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .flex-container {
            display: flex;
            flex-wrap: wrap;
        }
        .record {
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(33% - 20px);
        }
        .form-group {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container" id="contentContainer">
    <div id="notesDisplay" class="flex-container">
        <?php if (empty($content)): ?>
            <iframe src="nothing.html" style="border: none; width: 100%; height: 100vh;"></iframe>
                    <?php else: ?>
            <h1>Rejected Content</h1>

            <?php foreach ($content as $record): ?>
                <?php
                $parsedNotes = json_decode($record['notes'], true);
                ?>
                <div class="record">
                    <h3>Notes for Client: <?php echo htmlspecialchars($record['client_name']); ?></h3>
                    <form class="updateForm" data-record-id="<?php echo htmlspecialchars($record['id']); ?>" data-client-name="<?php echo htmlspecialchars($record['client_name']); ?>" data-month="<?php echo htmlspecialchars($record['month']); ?>">
                        <h4>Notes</h4>
                        <?php if (isset($parsedNotes['idea'])): ?>
                            <div class="form-group">
                                <p><strong><?php echo htmlspecialchars($columnNames['idea']); ?>:</strong></p>
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['idea']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <p><strong><?php echo htmlspecialchars($columnNames['title']); ?>:</strong></p>
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['title']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <p><strong><?php echo htmlspecialchars($columnNames['caption']); ?>:</strong></p>
                                <input type="text" value="<?php echo htmlspecialchars($parsedNotes['caption']); ?>" disabled>
                            </div>
                            <h4>Update Values</h4>
                            <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record['id']); ?>">
                            <div class="form-group">
                                <textarea name="idea" placeholder="New Idea" required></textarea>
                            </div>
                            <div class="form-group">
                                <textarea name="title" placeholder="New Title" required></textarea>
                            </div>
                            <div class="form-group">
                                <textarea name="caption" placeholder="New Caption" required></textarea>
                            </div>
                        <?php endif; ?>
                        <button type="submit">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- Add this button to your existing HTML -->
<!-- <button id="debugLocalStorage" type="button">Debug Local Storage</button> -->

<script>
$(document).ready(function() {
    // Function to save form data to local storage
    function saveToLocalStorage(recordId) {
        const form = $(`.updateForm[data-record-id="${recordId}"]`);
        const idea = form.find('textarea[name="idea"]').val();
        const title = form.find('textarea[name="title"]').val();
        const caption = form.find('textarea[name="caption"]').val();

        localStorage.setItem(`form_${recordId}`, JSON.stringify({
            idea: idea,
            title: title,
            caption: caption
        }));
    }

    // Function to load form data from local storage
    function loadFromLocalStorage(recordId) {
        const savedData = localStorage.getItem(`form_${recordId}`);
        if (savedData) {
            const data = JSON.parse(savedData);
            const form = $(`.updateForm[data-record-id="${recordId}"]`);

            form.find('textarea[name="idea"]').val(data.idea || '');
            form.find('textarea[name="title"]').val(data.title || '');
            form.find('textarea[name="caption"]').val(data.caption || '');
        }
    }

    // Save form data on keystroke
    $(document).on('input', '.updateForm textarea', function() {
        const form = $(this).closest('.updateForm');
        const recordId = form.data('record-id');
        saveToLocalStorage(recordId);
    });

    // Load form data when the page loads
    $('.updateForm').each(function() {
        const recordId = $(this).data('record-id');
        loadFromLocalStorage(recordId);
    });

    // Handle form submission
    $('.updateForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        const form = $(this);
        const clientName = form.data('client-name');
        const month = form.data('month');
        const recordId = form.find('input[name="record_id"]').val();

        $.ajax({
            type: 'POST',
            url: 'content_rejectedsub.php',
            data: {
                record_id: recordId,
                client_name: clientName,
                month: month,
                idea: form.find('textarea[name="idea"]').val(),
                title: form.find('textarea[name="title"]').val(),
                caption: form.find('textarea[name="caption"]').val()
            },
            success: function(response) {
                alert('Record updated successfully!');
                localStorage.removeItem(`form_${recordId}`); // Clear the saved data from local storage
                location.reload(); // Refresh the page
            },
            error: function() {
                alert('An error occurred while processing your request.');
            }
        });
    });

    // Debugging local storage button
    $('#debugLocalStorage').on('click', function() {
        console.log('Local Storage Content:');
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            const value = localStorage.getItem(key);
            console.log(`${key}: ${value}`);
        }
    });
});
</script>
</body>
</html>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
        margin: 0;
        padding: 0;
        height: 100vh; /* Full height of the viewport */
    }

    .container {
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        display: flex;
        flex-direction: column; /* Stack children vertically */
        align-items: center; /* Center items horizontally */
        padding: 20px;
        background: white;
        border-radius: 0; /* Remove border radius */
        box-shadow: none; /* Remove shadow */
    }

    h1 {
        color: #d9534f;
        margin-bottom: 20px;
        text-align: center;
    }

    .flex-container {
        display: flex;
        flex-wrap: wrap; /* Allow wrapping to the next line */
        justify-content: space-between; /* Distribute space between items */
        width: 100%; /* Full width */
    }

    .record {
        flex: 1 1 calc(30% - 20px); /* Adjust to fit three records per row */
        margin: 10px; /* Space around each record */
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background: #f9f9f9;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
    }

    .record h3 {
        margin-top: 0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        border-color: #d9534f;
        outline: none;
    }

    .form-group textarea {
        width: 100%;
        height: 100px; /* Adjust height as needed */
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        transition: border-color 0.3s;
    }

    .form-group textarea:focus {
        border-color: #d9534f;
        outline: none;
    }

    button {
        background-color: #d9534f;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s, transform 0.3s;
    }

    button:hover {
        background-color: #c9302c;
        transform: translateY(-2px);
    }

    @media (max-width: 600px) {
        .record {
            flex: 1 1 100%; /* Stack records on small screens */
        }
    }
           /* Basic styles to ensure the button is visible */
           #debugLocalStorage {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #debugLocalStorage:hover {
            background-color: #0056b3;
        }
    </style>