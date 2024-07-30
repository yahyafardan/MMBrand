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
        
        .event-section {
            display: none;
        }

        
        /* Style the select element */
#monthSelect {
    background-color: #ADD8E6; /* Light Blue background */
    color: #2F4F4F; /* Dark Slate Gray text */
    border: 1px solid #2F4F4F; /* Dark Slate Gray border */
    padding: 5px 10px; /* Padding inside the dropdown */
    border-radius: 4px; /* Rounded corners */
    font-size: 16px; /* Font size */
}

/* Style the options */
#monthSelect option {
    background-color: #ADD8E6; /* Light Blue background */
    color: #2F4F4F; /* Dark Slate Gray text */
    padding: 10px; /* Padding inside options */
}

        /* General body styling */
        body {
            background-color: black; /* Black background */
            color: green; /* Green text */
        }
        /* Calendar container background */
#calendar {
    background-color: #ADD8E6; /* Light Blue background */
    color: #2F4F4F; /* Dark Slate Gray text */
}

/* Event cells color */
.fc-event {
    background-color: #90EE90; /* Light Green for events */
    border-color: #90EE90; /* Match the border with the event color */
}

/* Event title color */
.fc-event .fc-title {
    color: #2F4F4F; /* Dark Slate Gray text for better readability */
}

/* Button styling */
.fc-custom-btn {
    background-color: #87CEFA; /* Light Sky Blue */
    color: #2F4F4F; /* Dark Slate Gray text */
}

/* Modal styling */
.modal-content {
    background-color: #FFFFFF; /* White background for the modal */
    border: 1px solid #E0E0E0; /* Light gray border for subtle contrast */
}

/* Modal header and footer buttons */
.modal-header .close {
    color: #2F4F4F; /* Dark Slate Gray close button */
}
.modal-footer .btn-primary {
    background-color: #87CEFA; /* Light Sky Blue button */
    border-color: #87CEFA; /* Match the border with the button color */
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
    border: 1px solid #ddd; /* Add border to make color boxes more visible */
}

/* Event colors for different statuses */
.fc-event.task {
    background-color: purple !important; /* Purple for tasks */
    border-color: purple !important; /* Border color for tasks */
}

.fc-event.saved {
    background-color: blue !important; /* Blue for saved events */
    border-color: blue !important; /* Border color for saved events */
}

.fc-event.done {
    background-color: green !important; /* Green for completed events */
    border-color: green !important; /* Border color for completed events */
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
        /* Updated Guide styles */
.guide .color-box.task {
    background-color: purple; /* Purple for tasks */
}

.guide .color-box.saved {
    background-color: blue; /* Blue for saved events */
}

.guide .color-box.done {
    background-color: green; /* Green for completed events */
}/* Style for the modal checkboxes container */
.modal-body .social-media-links {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem; /* Adjust space between checkboxes */
}

/* Individual checkbox styling */
.modal-body .form-check {
    display: flex;
    align-items: center;
    margin-right: 1rem; /* Adjust space between items */
}
/* Style for the language options container */
.language-options,
.sponsor-options,
.social-media-links {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem; /* Adjust this value to control the space between items */
}

/* Style for individual checkboxes/radio buttons */
.form-check {
    display: flex;
    align-items: center;
    margin-right: 1rem; /* Adjust spacing between items */
}
.form-check-input {
    margin-right: 0.5rem; /* Adjust spacing between checkbox/radio and label */
}

/* Optional: If needed to limit width or ensure proper wrapping */
.language-options,
.sponsor-options,
.social-media-links {
    max-width: 100%;
    overflow: auto;
}
.modal-header-content {
    width: 100%;
    text-align: center;
}

.modal-header-info {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px; /* Adjust as needed */
}

.modal-header-info > * {
    margin: 0 10px; /* Space out the items */
}

#languageData {
    font-weight: bold; /* Make language data stand out */
}
.modal-header-content {
    width: 100%;
    text-align: center;
}

.modal-header-info {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px; /* Adjust as needed */
}

.modal-header-info > * {
    margin: 0 10px; /* Space out the items */
}

.language-label {
    font-weight: bold;
}

#languageData {
    font-weight: bold; /* Make language data stand out */
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
        </select> <h2>Select a Month:</h2>
        <select id="monthSelect">
        </select>
        <div id='resultContainer'></div>
        <div id="calendar-container">
            <div id="calendar"></div>
        </div>
        <div class="guide mt-4">
    <h5>Event Color Guide:</h5>
    <div>
        <span class="color-box task"></span> Purple: Task
        </br>
        <span class="color-box saved"></span> Blue: Saved
        </br>
        <span class="color-box done"></span> Green: Completed
    </div>
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





<!-- Content Modal --><!-- Content Modal -->
<div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <h5 class="modal-title" id="contentModalLabel">Event Details</h5>
                    <div class="modal-header-info">
                        <span id="modalClientName" class="mx-3"></span> <!-- Client Name Display -->
                        <span id="modalDateID" class="ml-3"></span> <!-- Date ID Display -->
                        <span class="language-label">Languages:</span> <!-- Label for languages -->
                        <div id="languageData" class="ml-2"></div> <!-- Field to display AJAX response -->
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="contentForm" action="content_submit.php" method="post">
                    <!-- Radio buttons for selecting Type -->
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="eventType" id="staticType" value="static" required>
                            <label class="form-check-label" for="staticType">Static</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="eventType" id="videoType" value="video">
                            <label class="form-check-label" for="videoType">Video</label>
                        </div>
                    </div>
                    <!-- Sections for static -->
                    <div id="staticSection" class="event-section">
                        <div class="mb-3">
                            <label for="Concept" class="form-label">Concept (theme)</label>
                            <input type="text" class="form-control" id="Concept" name="Concept" required>
                        </div>
                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption (Text)</label>
                            <textarea class="form-control" id="caption" name="caption" rows="3" required></textarea>
                        </div>
                        <!-- Social Media Links Section in Modal -->
                        <div class="mb-3">
                            <label class="form-label">Social Media Links</label>
                            <div class="social-media-links">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="xAccount" name="socialMedia[]" value="x_account_link">
                                    <label class="form-check-label" for="xAccount">X</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="instagramAccount" name="socialMedia[]" value="instagram_account_link">
                                    <label class="form-check-label" for="instagramAccount">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="linkedinAccount" name="socialMedia[]" value="linkedin_account_link">
                                    <label class="form-check-label" for="linkedinAccount">LinkedIn</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="facebookAccount" name="socialMedia[]" value="facebook_account_link">
                                    <label class="form-check-label" for="facebookAccount">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="youtubeAccount" name="socialMedia[]" value="youtube_account_link">
                                    <label class="form-check-label" for="youtubeAccount">YouTube</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="snapchatAccount" name="socialMedia[]" value="snapchat_account_link">
                                    <label class="form-check-label" for="snapchatAccount">Snapchat</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tiktokAccount" name="socialMedia[]" value="tiktok_account_link">
                                    <label class="form-check-label" for="tiktokAccount">TikTok</label>
                                </div>
                            </div>
                        </div>
                        <!-- Sponsors Section in Modal -->
                        <div class="mb-3">
                            <label>Sponsored</label>
                            <div class="sponsor-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sponsors" id="sponsorYes" value="yes">
                                    <label class="form-check-label" for="sponsorYes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sponsors" id="sponsorNo" value="no">
                                    <label class="form-check-label" for="sponsorNo">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sections for video -->
                    <div id="videoSection" class="event-section d-none">
                        <label for="Concept" class="form-label">Title</label>
                    </div>
                    <input type="hidden" id="eventDate" name="eventDate">
                    <input type="hidden" id="eventID" name="eventID">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="saveButton">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const localStorageKey = 'modalSavedData';
    let startDate, endDate;
    let selectedEvent;
    let selectedClient;
    let globalHashtags = '';
    let nOfPosts, nOfVideos, languages; // Added new variables
    

    


    // Function to update event color
    function updateEventColor(eventId, color) {
        const events = $('#calendar').fullCalendar('clientEvents', function(event) {
            return event.id === eventId;
        });

        if (events.length > 0) {
            const event = events[0];
            event.backgroundColor = color;
            event.borderColor = color; // Update border color if needed
            $('#calendar').fullCalendar('updateEvent', event);
        }
    }

    // Function to apply saved event colors from localStorage
    function applySavedEventColors() {
        const savedData = getSavedData();
        for (const key in savedData) {
            if (savedData.hasOwnProperty(key)) {
                const data = savedData[key];
                updateEventColor(data.eventId, data.color);
            }
        }
    }

    // Function to get saved data from localStorage
    function getSavedData() {
        const data = localStorage.getItem(localStorageKey);
        return data ? JSON.parse(data) : {};
    }

    // Function to save data to localStorage
    function saveDataToLocal(clientName, eventId, data) {
        const savedData = getSavedData();
        const key = `${clientName}_${eventId}`;
        data.color = 'blue'; // Set the event color to blue
        savedData[key] = data;
        localStorage.setItem(localStorageKey, JSON.stringify(savedData));
    }// Function to initialize the modal and set up event listeners
function initializeModal() {
    const typeRadios = document.getElementsByName('eventType');
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const staticSection = document.getElementById('staticSection');
            const videoSection = document.getElementById('videoSection');

            if (this.value === 'static') {
                staticSection.style.display = 'block';
                videoSection.style.display = 'none';
            } else if (this.value === 'video') {
                staticSection.style.display = 'none';
                videoSection.style.display = 'block';
            }
        });
    });
}

// Function to show the modal and populate its fields
function showModal(event) {
    const savedData = getSavedData();
    const key = `${selectedClient}_${event.id}`;
    const eventData = savedData[key] || {};

    document.getElementById('Concept').value = eventData.title || '';
    document.getElementById('caption').value = eventData.hashtags || globalHashtags || '';
    document.getElementById('eventDate').value = event.start;
    document.getElementById('eventID').value = event.id;
    const formattedDate = moment(event.start).format('MMMM Do, YYYY');
    document.getElementById('modalDateID').textContent = `Task Date: ${formattedDate}`;
    document.getElementById('modalClientName').textContent = `Client: ${selectedClient}`;
    document.getElementById('languageData').textContent = languages;


    // Set initial visibility of sections based on the currently selected radio button
    const selectedType = Array.from(document.getElementsByName('eventType')).find(radio => radio.checked)?.value || 'static';
    const staticSection = document.getElementById('staticSection');
    const videoSection = document.getElementById('videoSection');
    
    if (selectedType === 'static') {
        staticSection.style.display = 'block';
        videoSection.style.display = 'none';
    } else {
        staticSection.style.display = 'none';
        videoSection.style.display = 'block';
    }

    $('#contentModal').modal('show');
}

// Initialize modal settings when the document is ready
$(document).ready(function() {
    initializeModal();
});


    // Event listener for client selection
    document.getElementById('clientSelect').addEventListener('change', function() {
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
                            document.getElementById('resultContainer').innerHTML = `<p style="color: red;">Error: ${response.error}</p>`;
                        } else {
                            startDate = moment(response.start_date);
                            endDate = adjustEndDate(moment(response.end_date));
                            globalHashtags = response.hashtags || '';
                            languages=response.languages || '';


                            // Populate months dropdown
                            const monthSelect = document.getElementById('monthSelect');
                            monthSelect.innerHTML = ''; // Clear existing options

                            // Add default option
                            const defaultOption = document.createElement('option');
                            defaultOption.value = '';
                            defaultOption.textContent = 'Select a month';
                            defaultOption.disabled = true;
                            defaultOption.selected = true;
                            monthSelect.appendChild(defaultOption);

                            // Add dynamic month options
                            response.months.forEach(month => {
                                const option = document.createElement('option');
                                option.value = month;
                                option.textContent = month;
                                monthSelect.appendChild(option);
                            });

                            const events = [];
                            response.posting_days.forEach(dayString => {
                                const daysArray = dayString.split(',').map(day => {
                                    return day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
                                });

                                daysArray.forEach(dayOfWeek => {
                                    const color = 'purple';
                                    const dates = getDatesForDayOfWeek(dayOfWeek, startDate, endDate);

                                    dates.forEach(date => {
                                        if (date.isBetween(startDate, endDate, null, '[]')) {
                                            events.push({
                                                id: date.format('YYYY-MM-DD'),
                                                start: date.format('YYYY-MM-DD'),
                                                end: date.format('YYYY-MM-DD'),
                                                rendering: 'background',
                                                backgroundColor: color,
                                                title: 'Event on ' + date.format('YYYY-MM-DD'),
                                                hashtags: response.hashtags || []
                                            });
                                        }
                                    });
                                });
                            });

                            monthSelect.addEventListener('change', function() {
                                const selectedMonths = Array.from(this.selectedOptions).map(option => option.value);

                                if (selectedMonths.length > 0) {
                                    console.log("Months selected:", selectedMonths);
                                    $('#calendar').fullCalendar('removeEvents'); // Remove all existing events

                                    // Filter and add only the events for the selected months
                                    const filteredEvents = events.filter(event => {
                                        const eventDate = moment(event.start);
                                        return selectedMonths.some(month => {
                                            const [monthStr, yearStr] = month.split(',');
                                            const monthStart = moment().year(yearStr).month(monthStr - 1).startOf('month');
                                            const monthEnd = moment().year(yearStr).month(monthStr - 1).endOf('month');
                                            return eventDate.isBetween(monthStart, monthEnd, null, '[]');
                                        });
                                    });

                                    $('#calendar').fullCalendar('addEventSource', filteredEvents);
                                    $('#calendar').fullCalendar('gotoDate', moment(selectedMonths[0], 'MM,YYYY').startOf('month')); // Navigate to the first selected month
                                }
                            });

                            applySavedEventColors(); // Apply saved event colors after loading events
                        }
                    } catch (e) {
                        console.error('Error processing response:', e);
                        document.getElementById('resultContainer').innerHTML = '<p style="color: red;">Error processing response.</p>';
                    }
                } else {
                    console.error('Request failed. Status:', xhr.status);
                    document.getElementById('resultContainer').innerHTML = '<p style="color: red;">Request failed. Status: ' + xhr.status + '</p>';
                }
            };

            xhr.send('client_name=' + encodeURIComponent(selectedClient));
        }
    });

    // Function to adjust endDate to the end of the month
    function adjustEndDate(endDate) {
        return endDate.clone().endOf('month').add(1, 'month').startOf('month').subtract(1, 'day');
    }

    // Function to get dates for a specific day of the week within a date range
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

    // Initialize FullCalendar
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        editable: true,
        eventRender: function(event, element, view) {
            // Extract the selected month and year
            const [monthStr, yearStr] = monthSelect.value.split(',');
            const selectedMonth = parseInt(monthStr, 10) - 1; // Zero-indexed month
            const selectedYear = parseInt(yearStr, 10);

            // Determine the start and end of the selected month
            const selectedMonthStart = moment().year(selectedYear).month(selectedMonth).startOf('month');
            const selectedMonthEnd = moment().year(selectedYear).month(selectedMonth).endOf('month');

            // Check if the event date is within the selected month
            const eventDate = moment(event.start);
            if (eventDate.isBetween(selectedMonthStart, selectedMonthEnd, null, '[]')) {
                element.css('background-color', event.backgroundColor || 'purple');
            } else {
                element.css('background-color', ''); // No color for dates outside the month
            }

            // Add custom button for the event
            const btn = $('<button class="fc-custom-btn btn btn-secondary btn-sm">Write</button>');
            btn.on('click', function() {
                showModal(event);
                selectedEvent = event.id;
            });
            element.append(btn);

            // Set cursor and tooltip
            element.css('cursor', 'pointer');
            element.find('.fc-title').attr('title', event.title);
        },
        dayClick: function(date, jsEvent, view) {
            const viewStart = moment(view.intervalStart).startOf('month');
            const viewEnd = moment(view.intervalEnd).subtract(1, 'day').endOf('month');

            // Check if the clicked date is within the visible month
            if (date.isBetween(viewStart, viewEnd, null, '[]')) {
                const events = $('#calendar').fullCalendar('clientEvents', function(event) {
                    return moment(event.start).isSame(date, 'day');
                });

                if (events.length > 0) {
                    showModal(events[0]);
                }
            }
        },
        eventClick: function(event, jsEvent, view) {
            console.log('Event clicked:', event);

            selectedEvent = event.id;
            const eventDate = moment(event.start).format('MMMM Do, YYYY');
            $('#contentModalLabel').text('Event on ' + eventDate);
            $('#modalEventId').text('Event ID: ' + event.id);
            $('#eventHashtags').val(event.hashtags || '');
        },
        viewRender: function(view) {
            try {
                if (monthSelect && monthSelect.value) {
                    const [monthStr, yearStr] = monthSelect.value.split(',');
                    const month = parseInt(monthStr, 10) - 1; // Convert month to zero-indexed
                    const year = parseInt(yearStr, 10);

                    if (!isNaN(month) && !isNaN(year)) {
                        const viewStart = moment(view.intervalStart);
                        const viewEnd = moment(view.intervalEnd).subtract(1, 'day');

                        if (viewStart.month() !== month || viewStart.year() !== year) {
                            $('#calendar').fullCalendar('gotoDate', new Date(year, month, 1));
                        }
                    }
                }
            } catch (e) {
                console.error('Error in viewRender:', e);
            }
        }
    });

    // Event listener for save button
    document.getElementById('saveButton').addEventListener('click', function () {
        const form = document.getElementById('contentForm');
        const formData = new FormData(form);

        const data = {
            title: formData.get('Concept'),
            description: formData.get('caption'),
            hashtags: formData.get('eventHashtags'),
            state: 'saved',
            color: 'blue'
        };

        saveDataToLocal(selectedClient, formData.get('eventID'), data);
        updateEventColor(formData.get('eventID'), 'blue');

        $('#contentModal').modal('hide');
        $('#calendar').fullCalendar('refetchEvents');
    });
});
window.addEventListener('beforeunload', function (e) {
    // Customize the message to be shown in the alert
    var confirmationMessage = 'Are you sure you want to leave? Changes you made may not be saved.';

    // Standard for most browsers
    e.preventDefault(); 
    e.returnValue = confirmationMessage;

    // For some older browsers
    return confirmationMessage;
});




</script>


</body>
</html>
