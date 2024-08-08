<?php 
require 'db.php'; 
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

$clientName = isset($_POST['client_name']) ? $_POST['client_name'] : ''; 
$month = isset($_POST['month']) ? $_POST['month'] : ''; 

if (!$clientName || !$month) { 
    echo '<p>Invalid client or month specified.</p>'; 
    exit; 
}

try { 
    $sql = "SELECT * FROM content WHERE client_name = :client_name AND month = :month AND status = 'app1'"; 
    $stmt = $pdo->prepare($sql); 
    $stmt->execute(['client_name' => $clientName, 'month' => $month]); 
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    
    if (!$records) { 
        echo '<iframe src="nothing.html" style="border: none; width: 100%; height: 100vh;"></iframe>';
        exit; 
    }
    
    echo '<div class="clients-container">';
    echo "<h2>Content for $clientName - $month</h2>";
    echo '<div class="client-container">';
    
    foreach ($records as $record) {
        $contributors = json_decode($record['contributors'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) { 
            echo '<p>Error decoding contributors JSON: ' . htmlspecialchars(json_last_error_msg()) . '</p>'; 
            continue; 
        }
        
        echo '<div class="record-container" data-id="' . htmlspecialchars($record['id']) . '">';
        echo '<p><strong>Type:</strong> ' . htmlspecialchars($record['type']) . '</p>';
        echo '<p><strong>Concept:</strong> ' . htmlspecialchars($record['concept']) . '</p>';
        echo '<p><strong>Caption:</strong> ' . htmlspecialchars($record['caption']) . '</p>';
        echo '<p><strong>Language:</strong> ' . htmlspecialchars($record['language']) . '</p>';
        echo '<p><strong>Last Updated:</strong> ' . htmlspecialchars($record['updated_at']) . '</p>';
        
        if (!empty($contributors)) { 
            echo '<p><strong>Contributors:</strong> '; 
            $contributorItems = []; 
            foreach ($contributors as $contributor => $content) { 
                $contributorItems[] = 'Person: ' . htmlspecialchars($contributor) . ' - Role: ' . htmlspecialchars($content); 
            } 
            echo implode(' | ', $contributorItems); 
            echo '</p>'; 
        }
        
        echo '<div class="button-container">';
        echo '<a class="approve-btn" href="#" data-id="' . htmlspecialchars($record['id']) . '" onclick="submitAction(event, \'approve\', ' . htmlspecialchars($record['id']) . ', \'' . htmlspecialchars($clientName) . '\', \'' . htmlspecialchars($month) . '\')">Approve</a>';
        echo '<a class="reject-btn" href="#" data-id="' . htmlspecialchars($record['id']) . '" onclick="showRejectionForm(event, this)">Reject</a>';
        echo '<div class="rejection-field" style="display: none;">';
        echo '<p>Select the fields to include in the rejection notes:</p>';
        echo '<label><input type="checkbox" name="reject_option" value="idea"> Idea</label>';
        echo '<label><input type="checkbox" name="reject_option" value="title"> Title</label>';
        echo '<label><input type="checkbox" name="reject_option" value="caption"> Caption</label>';
        echo '<div class="notes-container"></div>'; // Container for dynamic note areas
        echo '<button type="button" class="submit-reject-btn" onclick="submitAction(event, \'reject\', ' . htmlspecialchars($record['id']) . ', \'' . htmlspecialchars($clientName) . '\', \'' . htmlspecialchars($month) . '\')">Reject</button>';
        echo '<button type="button" class="return-btn" onclick="returnToOptions(event, this)">Return</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>'; // Close client-container
    echo '</div>'; // Close clients-container
    
} catch (PDOException $e) { 
    echo '<p>Error fetching records: ' . htmlspecialchars($e->getMessage()) . '</p>'; 
}
?>
<script>
  function showRejectionForm(event, element) {
    event.preventDefault();
    const buttonContainer = element.closest('.button-container');
    const rejectionField = buttonContainer.querySelector('.rejection-field');
    const rejectButton = buttonContainer.querySelector('.reject-btn');
    const approveButton = buttonContainer.querySelector('.approve-btn'); // Select the Approve button

    // Hide the original Reject button and the Approve button, and show the rejection form
    rejectButton.style.display = 'none';
    approveButton.style.display = 'none'; // Hide the Approve button
    rejectionField.style.display = 'block';
}

function returnToOptions(event, element) {
    event.preventDefault();
    const buttonContainer = element.closest('.button-container');
    const rejectionField = buttonContainer.querySelector('.rejection-field');
    const rejectButton = buttonContainer.querySelector('.reject-btn');
    const approveButton = buttonContainer.querySelector('.approve-btn'); // Select the Approve button

    // Hide the rejection form and show the original Reject button, and also show the Approve button
    rejectionField.style.display = 'none';
    rejectButton.style.display = 'inline-block';
    approveButton.style.display = 'inline-block'; // Show the Approve button again

    // Clear the notes container
    const notesContainer = buttonContainer.querySelector('.notes-container');
    notesContainer.innerHTML = '';

    // Uncheck all checkboxes
    const checkboxes = buttonContainer.querySelectorAll('.rejection-field input[name="reject_option"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);

    // Clear the note areas
    const noteAreas = buttonContainer.querySelectorAll('.rejection-field .note-area');
    noteAreas.forEach(noteArea => noteArea.remove());
}


document.addEventListener('change', function(event) {
    if (event.target.name === 'reject_option') {
        const checkbox = event.target;
        const notesContainer = checkbox.closest('.rejection-field').querySelector('.notes-container');
        const checkboxValue = checkbox.value;
        
        if (checkbox.checked) {
            // Create a new note area
            const noteArea = document.createElement('div');
            noteArea.classList.add('note-area');
            noteArea.innerHTML = `<label>${checkboxValue} Note:</label><textarea name="${checkboxValue}_note" rows="2" style="width: 100%; resize: both;"></textarea>`;
            notesContainer.appendChild(noteArea);
            
            // Automatically select other checkboxes if 'idea' is selected
            if (checkboxValue === 'idea') {
                const otherCheckboxes = ['title', 'caption']; // Replace with actual checkbox values
                otherCheckboxes.forEach(value => {
                    const otherCheckbox = checkbox.closest('.rejection-field').querySelector(`input[name="reject_option"][value="${value}"]`);
                    if (otherCheckbox && !otherCheckbox.checked) {
                        otherCheckbox.checked = true;
                        // Create note area if it does not exist
                        const existingNoteArea = notesContainer.querySelector(`.note-area textarea[name="${value}_note"]`);
                        if (!existingNoteArea) {
                            const noteArea = document.createElement('div');
                            noteArea.classList.add('note-area');
                            noteArea.innerHTML = `<label>${value} Note:</label><textarea name="${value}_note" rows="2" style="width: 100%; resize: both;"></textarea>`;
                            notesContainer.appendChild(noteArea);
                        }
                    }
                });
            }
        } else {
            // Remove the corresponding note area
            const noteArea = notesContainer.querySelector(`.note-area textarea[name="${checkboxValue}_note"]`).parentElement;
            if (noteArea) {
                notesContainer.removeChild(noteArea);
            }
        }
    }
});

function submitAction(event, action, recordId, clientName, month) {
    event.preventDefault();
    let rejectionOptions = [];
    let rejectionNotes = {};

    if (action === 'reject') {
        // Collect selected checkboxes
        const selectedOptions = document.querySelectorAll(`.record-container[data-id="${recordId}"] .rejection-field input[name="reject_option"]:checked`);
        rejectionOptions = Array.from(selectedOptions).map(option => option.value);

        // Get notes
        rejectionOptions.forEach(option => {
            const note = document.querySelector(`.record-container[data-id="${recordId}"] .rejection-field textarea[name="${option}_note"]`).value;
            if (note) {
                rejectionNotes[option] = note;
            }
        });

        // Validation checks
        if (rejectionOptions.length === 0) {
            alert('Please select at least one option for rejection.');
            return;
        }

        // Check for missing notes
        let missingNotes = [];
        rejectionOptions.forEach(option => {
            if (option !== 'idea') { // Skip validation for 'idea' checkbox
                const note = rejectionNotes[option];
                if (!note || !note.trim()) {
                    missingNotes.push(option);
                }
            }
        });

        if (missingNotes.length > 0) {
            alert('Please provide a non-empty note for the following selected rejection options: ' + missingNotes.join(', '));
            return;
        }
    }

    const data = {
        id: recordId,
        action: action,
        notes: JSON.stringify(rejectionNotes), // Encoding the notes as an associative array
        client_name: clientName,
        month: month
    };

    console.log('Sending data:', data);

    fetch('approve1sub.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.text())
    .then(result => {
        console.log('Server response:', result);
        alert(result);
        location.reload(true); // Reload the page to update the records
    })
    .catch(error => {
        console.error('Error:', error);
    });
}


</script>






<style>
    .reject-btn,
.return-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px; /* Space between buttons */
}

.reject-btn {
    background-color: #d9534f; /* Red for reject */
}

.reject-btn:hover {
    background-color: #c9302c; /* Darker red for hover */
}

.return-btn {
    background-color: #5bc0de; /* Light blue for return */
}

.return-btn:hover {
    background-color: #31b0d5; /* Darker blue for hover */
}

.clients-container {
    display: flex;
    flex-direction: column; /* Display clients in a column */
    padding: 20px;
}

.client-container {
    display: flex;
    flex-wrap: wrap; /* Allow records to wrap to the next line */
    gap: 20px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 20px; /* Space between client containers */
}

.client-container h3 {
    width: 100%; /* Make the client name take the full width of the container */
    margin-top: 0;
    margin-bottom: 10px; /* Space between client name and records */
}

.record-container {
    flex: 0 0 300px; /* Each record takes a fixed width and does not shrink */
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 15px; /* Space between record containers */
}

.record-container p {
    margin: 5px 0;
}

.button-container {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.approve-btn, .reject-btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    border-radius: 5px;
    text-align: center;
}

.approve-btn {
    background-color: #28a745;
}

.approve-btn:hover {
    background-color: #218838;
}

.reject-btn {
    background-color: #dc3545;
}

.reject-btn:hover {
    background-color: #c82333;
}
.record-container ul {
    list-style-type: none;
    padding-left: 0;
    margin: 0;
}

.record-container li {
    padding: 5px 0;
    border-bottom: 1px solid #ddd;
}

.record-container li:last-child {
    border-bottom: none;
}
.contributors-container {
    display: flex;
    flex-wrap: wrap; /* Allow items to wrap if there are too many for one line */
    gap: 10px; /* Space between contributor items */
    margin-top: 10px; /* Space above contributors section */
}

.contributor-item {
    display: inline-block; /* Display items inline */
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f1f1f1;
}
/* Optional: Style the list of contributors if needed */
.record-container p {
    margin: 5px 0;
}

.record-container p strong {
    margin-right: 10px;
}
.clients-container {
    display: flex;
    flex-direction: column;
    padding: 20px;
}

.client-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 20px;
}

.record-container {
    flex: 0 0 300px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 15px;
}

.record-container p {
    margin: 5px 0;
}

.button-container {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.approve-btn, .reject-btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    border-radius: 5px;
    text-align: center;
}

.approve-btn {
    background-color: #28a745;
}

.approve-btn:hover {
    background-color: #218838;
}

.reject-btn {
    background-color: #dc3545;
}

.reject-btn:hover {
    background-color: #c82333;
}

.rejection-field {
    display: none; /* Initially hidden */
}
.clients-container {
    display: flex;
    flex-direction: column;
    padding: 20px;
}

.client-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 20px;
}

.record-container {
    flex: 0 0 300px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    margin-bottom: 15px;
}

.record-container p {
    margin: 5px 0;
}

.button-container {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.approve-btn, .reject-btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    border-radius: 5px;
    text-align: center;
}

.approve-btn {
    background-color: #28a745;
}

.approve-btn:hover {
    background-color: #218838;
}

.reject-btn {
    background-color: #dc3545;
}

.reject-btn:hover {
    background-color: #c82333;
}

.rejection-field {
    display: none; /* Initially hidden */
}
/* Styling for the Reject button inside the rejection form */
.submit-reject-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    background-color: #dc3545; /* Red for reject */
    margin-right: 10px; /* Space between buttons */
}

.submit-reject-btn:hover {
    background-color: #c82333; /* Darker red for hover */
}


</style>




