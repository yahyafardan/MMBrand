<?php
require 'db.php';

try {
    // Fetch records with status 'waiting for approval'
    $sql = "SELECT * FROM content WHERE status = 'app1'";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        echo "<p>No records waiting for approval.</p>";
        exit;
    }

    // Organize records by client
    $clients = [];
    foreach ($records as $record) {
        $clientName = $record['client_name'];
        $month = $record['month'];

        if (!isset($clients[$clientName])) {
            $clients[$clientName] = [];
        }

        if (!isset($clients[$clientName][$month])) {
            $clients[$clientName][$month] = [];
        }

        $clients[$clientName][$month][] = $record;
    }

    // Sort months for each client
    foreach ($clients as $clientName => $months) {
        ksort($months); // Sort months in ascending order
        $clients[$clientName] = $months;
    }

    echo '<div class="container">';

    // Iterate over the clients and generate HTML
    foreach ($clients as $clientName => $months) {
        echo '<div class="client-container">';
        echo '<div class="client-name">' . htmlspecialchars($clientName) . '</div>';

        foreach ($months as $month => $records) {
            echo '<div class="month-container">';
            echo '<div class="month-name">' . htmlspecialchars($month) . '</div>';

            // Add form for each month to review records
            echo '<form action="review1.php" method="post" target="_blank">';
            echo '<input type="hidden" name="client_name" value="' . htmlspecialchars($clientName) . '">';
            echo '<input type="hidden" name="month" value="' . htmlspecialchars($month) . '">';
            echo '<button type="submit" class="review-btn">Review and Approve</button>';
            echo '</form>';

            echo '</div>'; // End of month-container
        }

        echo '</div>'; // End of client-container
    }

    echo '</div>'; // End of container

} catch (PDOException $e) {
    echo '<p>Error fetching records: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
<script>
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        location.reload(); // Refresh the page
        // alert("Page is visible");
    }
});
</script>




<style>
 /* Ensure the container takes full width and allows wrapping */
.container {
    display: flex;
    flex-wrap: wrap; /* Allows wrapping to the next line */
    gap: 20px; /* Space between client containers */
    padding: 20px;
    justify-content: flex-start; /* Align items to the start */
}

/* Style for each client container */
.client-container {
    flex: 1 1 300px; /* Flex-grow and flex-shrink, with a base width of 300px */
    border: 1px solid #ddd;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
}

/* Style for client name */
.client-name {
    font-size: 1.5em;
    margin-bottom: 15px;
}

/* Style for each month container */
.month-container {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 15px;
    background-color: #e9ecef;
}

/* Style for month name */
.month-name {
    font-size: 1.2em;
    margin-bottom: 10px;
}

/* Style for review button */
.review-btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    background-color: #007bff;
    border-radius: 5px;
    text-align: center;
    margin-top: 10px;
}

.review-btn:hover {
    background-color: #0056b3;
}


/* Style for record container */
.app1-container {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #fff;
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

