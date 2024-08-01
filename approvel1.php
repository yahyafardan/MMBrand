<?php

// Include the database connection file
require 'db.php';

try {
    // Fetch records with status 'waiting for approval'
    $sql = "SELECT * FROM content WHERE status = 'approvalIn'";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        echo "<p>No records waiting for approval.</p>";
        exit;
    }

    echo '<div class="container">';

    // Iterate over the records and generate HTML
    foreach ($records as $record) {
        echo '<div class="approvalIn-container" id="record-' . htmlspecialchars($record['id']) . '">';
        
        // Display the entire record
        echo '<div class="record-details">';
        echo '<p><strong>Type:</strong> ' . htmlspecialchars($record['type']) . '</p>';
        echo '<p><strong>Concept:</strong> ' . htmlspecialchars($record['concept']) . '</p>';
        echo '<p><strong>Caption:</strong> ' . htmlspecialchars($record['caption']) . '</p>';
        echo '<p><strong>Language:</strong> ' . htmlspecialchars($record['language']) . '</p>';
        echo '<p><strong>Post Date:</strong> ' . htmlspecialchars($record['post_date']) . '</p>';
        echo '<p><strong>Month:</strong> ' . htmlspecialchars($record['month']) . '</p>';
        echo '<p><strong>Social Media Platforms:</strong> ' . htmlspecialchars($record['social_media_platforms']) . '</p>';
        echo '<p><strong>Sponsored:</strong> ' . htmlspecialchars($record['sponsored']) . '</p>';
        echo '<p><strong>Status:</strong> ' . htmlspecialchars($record['status']) . '</p>';
        echo '<p><strong>Client Name:</strong> ' . htmlspecialchars($record['client_name']) . '</p>';
        echo '</div>';

        echo '<div class="button-container">';
        echo '<a class="elementor-button elementor-button-link elementor-size-sm approve-btn" href="#">Approved</a>';
        echo '<a class="elementor-button elementor-button-link elementor-size-sm reject-btn" href="#">Reject</a>';
        echo '</div>';

        // Hidden note input field
        echo '<div class="note-container" style="display:none;">';
        echo '<label for="note-' . htmlspecialchars($record['id']) . '">Note:</label>';
        echo '<textarea id="note-' . htmlspecialchars($record['id']) . '" rows="3" placeholder="Enter your note here..."></textarea>';
        echo '<button class="save-note-btn" onclick="saveNote(' . htmlspecialchars($record['id']) . ')">Save Note</button>';
        echo '</div>';

        echo '</div>';
    }

    echo '</div>';

} catch (PDOException $e) {
    // Log and display database query errors
    echo '<p>Error fetching records: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<script>
// Function to toggle the visibility of the note input field
document.querySelectorAll('.reject-btn').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default action
        const container = this.closest('.approvalIn-container');
        const noteContainer = container.querySelector('.note-container');
        noteContainer.style.display = noteContainer.style.display === 'none' ? 'block' : 'none';
    });
});

// Function to handle saving the note (you can implement AJAX or form submission here)
function saveNote(recordId) {
    const noteTextArea = document.getElementById('note-' + recordId);
    const note = noteTextArea.value;

    // Example: Logging note to console (implement your saving logic here)
    console.log('Saving note for record ID ' + recordId + ': ' + note);

    // Optionally, hide the note input field after saving
    noteTextArea.closest('.note-container').style.display = 'none';
}
</script>

<style>
/* Ensure the container takes full width and allows wrapping */
.container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Optional: Add space between items */
    padding: 20px;
}

/* Style for each record container */
.approvalIn-container {
    flex: 1 1 auto; /* Allow items to grow and shrink */
    min-width: 300px; /* Optional: Set a minimum width for each item */
    border: 1px solid #ddd;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    display: flex;
    flex-direction: column;
}

/* Style for record details */
.record-details p {
    margin: 5px 0;
}

/* Style for button container */
.button-container {
    margin-top: 15px;
    display: flex;
    gap: 10px; /* Space between buttons */
}

/* Style for buttons */
.elementor-button {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    background-color: #007bff;
    border-radius: 5px;
    text-align: center;
}

.elementor-button:hover {
    background-color: #0056b3;
}

.elementor-button-link {
    background-color: #28a745;
}

.elementor-button-link:hover {
    background-color: #218838;
}
/* Style for note input field and button */
.note-container {
    margin-top: 10px;
}

.note-container label {
    display: block;
    margin-bottom: 5px;
}

.note-container textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.note-container .save-note-btn {
    display: block;
    margin-top: 10px;
    padding: 8px 16px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.note-container .save-note-btn:hover {
    background-color: #0056b3;
}

</style>