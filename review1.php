<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is an app1
if ($_SESSION['role_name'] !== 'app1') {
    echo "Access denied.";
    exit;
}


$clientName = isset($_POST['client_name']) ? $_POST['client_name'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';

if (!$clientName || !$month) {
    echo '<p>Invalid client or month specified.</p>';
    exit;
}

try {
    $sql = "SELECT * FROM content WHERE client_name = :client_name AND month = :month AND status = 'approvalIn'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['client_name' => $clientName, 'month' => $month]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        echo "<p>No records found for $clientName in $month.</p>";
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
        echo '<p><strong>Status:</strong> ' . htmlspecialchars($record['status']) . '</p>';

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
        echo '<a class="reject-btn" href="#" data-id="' . htmlspecialchars($record['id']) . '" onclick="toggleRejectionField(this)">Reject</a>';
        echo '<div class="rejection-field" style="display:none;">';
        echo '<textarea placeholder="Reason for rejection" name="rejection_reason" rows="4" style="width: 100%; resize: both;"></textarea>';
        echo '<button type="button" onclick="submitAction(event, \'reject\', ' . htmlspecialchars($record['id']) . ', \'' . htmlspecialchars($clientName) . '\', \'' . htmlspecialchars($month) . '\')">Submit</button>';
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


<script>function toggleRejectionField(element) {
    const buttonContainer = element.closest('.button-container');
    const approvalButton = buttonContainer.querySelector('.approve-btn');
    const rejectionField = buttonContainer.querySelector('.rejection-field');

    if (rejectionField.style.display === 'block') {
        rejectionField.style.display = 'none'; // Hide the rejection field
        approvalButton.style.display = 'inline-block'; // Show the approval button
    } else {
        approvalButton.style.display = 'none'; // Hide the approval button
        rejectionField.style.display = 'block'; // Show the rejection field
    }
}

function submitAction(event, action, recordId, clientName, month) {
    event.preventDefault();
    let rejectionReason = '';

    if (action === 'reject') {
        rejectionReason = document.querySelector(`.record-container[data-id="${recordId}"] .rejection-field textarea`).value;
        if (!rejectionReason) {
            alert('Please provide a reason for rejection.');
            return;
        }
    }

    const data = {
        id: recordId,
        action: action,
        rejection_reason: rejectionReason,
        client_name: clientName,
        month: month
    };

    console.log('Sending data:', data);

    fetch('approve1sub.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.text())
    .then(result => {
        console.log('Server response:', result);
        alert(result);
        // Hide the specific record container
        const recordContainer = document.querySelector(`.record-container[data-id="${recordId}"]`);
        if (recordContainer) {
            recordContainer.style.display = 'none'; // Hide the container
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

</script>



<style>.clients-container {
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

</style>




