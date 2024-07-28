<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role_name'] !== 'content') {
    echo "Access denied.";
    exit;
}

require 'db.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

try {
    $sql = "SELECT client_name FROM clients";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Small Bootstrap Calendar</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <style>
        /* General body styling */
        body {
            background-color: black; /* Black background */
            color: green; /* Green text */
        }
        /* Calendar container */
        #calendar-container {
            width: 80%;
            height: ;: 80%; /* Adjust as needed */
            margin: 0 auto;
        }
        /* Calendar element styling */
        #calendar {
            max-width: 100%;
            background-color: black; /* Black background for calendar */
            color: green; /* Green text for calendar */
        }
        /* Event cell styling */
        .fc-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer; /* Make it look clickable */
            position: relative;
            color: white; /* White text for event */
        }
        /* Title within events */
        .fc-event .fc-title {
            max-width: 120px; /* Adjust as needed */
            height: 20px;     /* Adjust as needed */
            overflow: hidden; /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis for overflowed text */
            white-space: nowrap; /* Prevent line breaks */
            text-align: center;
        }
        /* Purple background for event cells */
        .fc-day .fc-day-posting {
            background-color: purple; /* Purple background for event cells */
        }
        /* Option background cell styling */
        #clientSelect {
            background-color: purple; /* Match event cell color */
            color: white; /* White text for better readability */
        }
        #clientSelect option {
            background-color: purple; /* Match event cell color */
            color: white; /* White text for better readability */
        }
        /* Guide styles */
        .guide {
            margin-top: 20px;
        }
        .guide .color-box {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        #floatingButton {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999; /* Ensure it's on top */
            padding: 15px 30px; /* Increase padding */
            font-size: 18px; /* Increase font size */
            border-radius: 50px; /* Make the button round */
            background-color: purple; /* Purple button background */
            color: white; /* White text on button */
            border: none; /* Remove default border */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visibility */
        }
        /* Modal custom styles */
        .modal-content {
            background-color: black; /* Black background for modal */
            color: green; /* Green text for modal */
        }
        .modal-header {
            border-bottom: 1px solid purple; /* Purple border for modal header */
        }
        .modal-footer {
            border-top: 1px solid purple; /* Purple border for modal footer */
        }
        .modal-body input, .modal-body textarea {
            background-color: black; /* Black background for input/textarea */
            color: green; /* Green text for input/textarea */
            border: 1px solid purple; /* Purple border for input/textarea */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <h2>Select a Client:</h2>
        <select name='client' id='clientSelect'>
            <option value='' disabled selected>Select a client</option>
            <?php
            if ($clients) {
                foreach ($clients as $client) {
                    echo "<option value='" . htmlspecialchars($client['client_name']) . "'>" . htmlspecialchars($client['client_name']) . "</option>";
                }
            } else {
                echo "<option value=''>No clients found</option>";
            }
            ?>
        </select>
        <div id='resultContainer'></div>
        <div id="calendar-container">
            <div id="calendar"></div>
        </div>
        <div class="guide mt-4">
            <h5>Event Color Guide:</h5>
            <div>
                <span class="color-box" style="background-color: purple;"></span> Purple: Event
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Content Modal -->
  <!-- Modal HTML -->
  <div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="contentModalLabel">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="contentForm" action="content_sumbit.php" method="post">
          <div class="mb-3">
            <label for="eventTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
          </div>
          <div class="mb-3">
            <label for="eventDescription" class="form-label">Description</label>
            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="eventHashtags" class="form-label">Hashtags</label>
            <input type="text" class="form-control" id="eventHashtags" name="eventHashtags">
          </div>
          <input type="hidden" id="eventDate" name="eventDate">
          <input type="hidden" id="eventID" name="eventID">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id= "CloseButton">Close</button>
        <button type="button" class="btn btn-primary" id="saveButton">Save</button>
        <button type="submit" class="btn btn-primary" id="submitButton" form="contentForm">Submit</button>
      </div>
    </div>
  </div>
</div>


<script>
   document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('clientSelect');
    const resultContainer = document.getElementById('resultContainer');
    let startDate, endDate; // Declare global variables
    let selectedEvent; // Variable to keep track of selected event
    let selectedClient; // Variable to keep track of selected client
    let globalHashtags = ''; // Global variable to store hashtags


    // Local Storage Key
    const localStorageKey = 'modalSavedData';

    function getSavedData() {
        const data = localStorage.getItem(localStorageKey);
        return data ? JSON.parse(data) : {};
    }

    function saveDataToLocal(clientName, eventId, data) {
        const savedData = getSavedData();
        const key = `${clientName}_${eventId}`; // Composite key
        savedData[key] = data;
        localStorage.setItem(localStorageKey, JSON.stringify(savedData));
    }

    function showModal(event) {
        const savedData = getSavedData();
        const key = `${selectedClient}_${event.id}`; // Composite key
        const eventData = savedData[key] || {};

        document.getElementById('eventTitle').value = eventData.title || '';
        document.getElementById('eventDescription').value = eventData.description || '';
        document.getElementById('eventHashtags').value = eventData.hashtags ||globalHashtags || ''; // Set hashtags
        document.getElementById('eventDate').value = event.start;
        document.getElementById('eventID').value = event.id; // Set event ID

        $('#contentModal').modal('show');
    }

    clientSelect.addEventListener('change', function() {
        selectedClient = this.value;

        if (selectedClient) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'client_days.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.error) {
                            resultContainer.innerHTML = `<p style="color: red;">Error: ${response.error}</p>`;
                        } else {
                            startDate = moment(response.start_date);
                            endDate = adjustEndDate(moment(response.end_date));
                            globalHashtags = response.hashtags || ''; // Store the hashtags globally


                            function getDatesForDayOfWeek(dayOfWeek, start, end) {
                                const days = {
                                    'Monday': 1,
                                    'Tuesday': 2,
                                    'Wednesday': 3,
                                    'Thursday': 4,
                                    'Friday': 5,
                                    'Saturday': 6,
                                    'Sunday': 0
                                };
                                const dates = [];
                                let current = start.clone().day(days[dayOfWeek]);

                                if (current.isBefore(start)) {
                                    current.add(7, 'days');
                                }

                                while (current.isSameOrBefore(end)) {
                                    dates.push(current.clone());
                                    current.add(7, 'days');
                                }

                                return dates;
                            }

                            const events = [];
                            response.posting_days.forEach(dayString => {
                                const daysArray = dayString.split(',').map(day => {
                                    return day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
                                });

                                daysArray.forEach(dayOfWeek => {
                                    const color = 'purple'; // Purple color for events
                                    const dates = getDatesForDayOfWeek(dayOfWeek, startDate, endDate);

                                    dates.forEach(date => {
                                        if (date.isBetween(startDate, endDate, null, '[]')) {
                                            events.push({
                                                id: date.format('YYYY-MM-DD'), // Unique ID for each date
                                                start: date.format('YYYY-MM-DD'),
                                                end: date.format('YYYY-MM-DD'),
                                                rendering: 'background',
                                                backgroundColor: color,
                                                title: 'Event on ' + date.format('YYYY-MM-DD'), // Add a title for display
                                                hashtags: response.hashtags || [] // Add hashtags from response
                                            });
                                        }
                                    });
                                });
                            });

                            $('#calendar').fullCalendar('removeEvents');
                            $('#calendar').fullCalendar('addEventSource', events);

                            checkViewBounds(); // Check and adjust view bounds
                        }
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        resultContainer.innerHTML = '<p style="color: red;">Failed to parse response.</p>';
                    }
                } else {
                    console.error('Request failed. Status:', xhr.status);
                    resultContainer.innerHTML = '<p style="color: red;">Request failed. Status: ' + xhr.status + '</p>';
                }
            };

            xhr.send('client_name=' + encodeURIComponent(selectedClient));
        }
    });

    function adjustEndDate(endDate) {
        return endDate.clone().endOf('month').add(1, 'month').startOf('month').subtract(1, 'day');
    }

    function checkViewBounds() {
        const calendar = $('#calendar').fullCalendar('getCalendar');
        const view = calendar.view;
        const viewStart = view.intervalStart;
        const viewEnd = view.intervalEnd;

        if (viewStart.isBefore(startDate)) {
            calendar.gotoDate(startDate);
        }
        if (viewEnd.isAfter(endDate)) {
            calendar.gotoDate(endDate);
        }
    }

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        editable: true,
        eventRender: function(event, element) {
            console.log('Rendering event:', event); // Debug log for event data

            // Add a clickable button for each event
            const btn = $('<button class="fc-custom-btn btn btn-secondary btn-sm">Write</button>');
            btn.on('click', function() {
                // Trigger popup for this event
                showModal(event);
                selectedEvent = event.id; // Store the selected event's ID
                console.log('Selected event ID:', selectedEvent); // Debug log for selected event ID
            });
            element.append(btn);

            // Ensure cursor is a pointer and event cells are clickable
            element.css('cursor', 'pointer');
            element.find('.fc-title').attr('title', event.title);
        },
        dayClick: function(date, jsEvent, view) {

            // Handle click on a day cell
            const events = $('#calendar').fullCalendar('clientEvents', function(event) {
                return moment(event.start).isSame(date, 'day');
            });

            if (events.length > 0) {
                // If there are events on the clicked day, proceed to show modal
                showModal(events[0]); // Show the first event of the day
            }
        },
        eventClick: function(event, jsEvent, view) {
            console.log('Event clicked:', event); // Debug log for event click

            // Handle click on an event
            selectedEvent = event.id; // Store the selected event's ID
            const eventDate = moment(event.start).format('MMMM Do, YYYY'); // Format date
            $('#contentModalLabel').text('Event on ' + eventDate); // Set the modal title
            $('#modalEventId').text('Event ID: ' + event.id); // Set the event ID in the modal body
            console.log('Event ID displayed:', event.id); // Debug log for displayed event ID
            // Optional: Add visual indication of selected event
            $('#calendar').fullCalendar('renderEvent', {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                backgroundColor: 'blue' // Change color to indicate selection
            }, true);
        },
        viewRender: function(view) {
            if (startDate && endDate) {
                checkViewBounds();
            }
        }
    });

    document.getElementById('saveButton').addEventListener('click', function () {
        const form = document.getElementById('contentForm');
        const formData = new FormData(form);

        const data = {
            title: formData.get('eventTitle'),
            description: formData.get('eventDescription'),
            hashtags: formData.get('eventHashtags') // Include hashtags
        };

        saveDataToLocal(selectedClient, formData.get('eventID'), data);

        $('#contentModal').modal('hide');
    });

    document.getElementById('submitButton').addEventListener('click', function () {
        const form = document.getElementById('contentForm');
        form.submit(); // Submit the form to content.php

        
       
    });
});




</script>

</body>
</html>
