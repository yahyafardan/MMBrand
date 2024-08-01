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
  
</head>
<body>
  <!-- HTML element for displaying the saved items count -->
<div id="savedItemsContainer" class="d-none">
    <span id="savedItemsCount">Saved Items: 0</span>
</div>

<!-- HTML element for displaying the visible events count -->
<div id="visibleEventsContainer" class="d-none">
    <p id="visibleEventsCount">Visible Events: 0</p>
</div>
<!-- Sticky Button -->
<button id="viewLocalStorageButton"
class="d-none" style="
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    z-index: 1000;
">
    View Local Storage
</button>



    <!-- Hidden Submit Button -->
<button id="submitButton" class="btn btn-primary d-none">Submit Button</button>

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
        <span class="color-box task"></span>  Task
        </br>
        <span class="color-box saved"></span>  Saved
        </br>
        <span class="color-box done"></span>  Completed
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





<!-- Content Modal -->
 

<div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentModalLabel">Event Details</h5>
                <div class="modal-header-info">
                    <div class="info-item client-name">
                        <span id="modalClientName"></span> <!-- Client Name Display -->
                    </div>
                    <div class="info-item task-date">
                        <span id="modalDateID"></span> <!-- Date ID Display -->
                    </div>
                    <div class="info-item language-data">
                        <span class="language-label">Languages:</span> <!-- Label for languages -->
                        <div id="languageData"></div> <!-- Field to display AJAX response -->
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
                        <label class="form-label">Content Type</label>
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
                    <div id="staticSection" class="event-section d-none">
                        <div class="mb-3">
                            <label for="Concept" class="form-label">Concept (theme)</label>
                            <input type="text" class="form-control" id="Concept" name="Concept" required>
                        </div>
                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption (Text)</label>
                            <textarea class="form-control" id="caption" name="caption" rows="3" required></textarea>
                        </div>
                        <!-- Social Media Platforms Section in Modal -->
                        <div class="mb-3">
                            <label class="form-label">Social Media Platforms</label>
                            <div class="social-media-platforms">
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
    // Function to clear local storage
function clearLocalStorage() {
    localStorage.clear();
    console.log('Local storage has been cleared.');
}

// Function to display local storage data in the console
function displayLocalStorageData() {
    const allKeys = Object.keys(localStorage);

    console.group('--- Local Storage Contents ---');

    if (allKeys.length === 0) {
        console.log('Local storage is empty.');
    } else {
        allKeys.forEach(key => {
            const value = localStorage.getItem(key);
            console.group(`Key: ${key}`);
            console.log('Value:', value);
            console.groupEnd();
        });
    }

    console.groupEnd();
}

// Clear local storage on page load
window.addEventListener('load', function() {
    clearLocalStorage();

    // Add event listener for the view local storage button
    document.getElementById('viewLocalStorageButton').addEventListener('click', displayLocalStorageData);
});

    // Function to display local storage data in the console
function displayLocalStorageData() {
    const allKeys = Object.keys(localStorage);

    console.group('--- Local Storage Contents ---');

    if (allKeys.length === 0) {
        console.log('Local storage is empty.');
    } else {
        allKeys.forEach(key => {
            const value = localStorage.getItem(key);
            console.group(`Key: ${key}`);
            console.log('Value:', value);
            console.groupEnd();
        });
    }

    console.groupEnd();
}

// Event listener for the view local storage button
document.getElementById('viewLocalStorageButton').addEventListener('click', displayLocalStorageData);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const localStorageKey = 'modalSavedData';
    let startDate, endDate;
    let selectedEvent;
    let selectedClient;
    let globalHashtags = '';
    let nOfPosts, nOfVideos, languages; // Added new variables
    let GsavedItemsCount = savedItemsCount; // This should be updated with the actual saved items count
    let GvisibleEventsCount = visibleEventsCount; // This should be updated dynamically
    let savedItemsSet = new Set(); // Initialize the Setlet savedItemsCount = 0;   


    document.getElementById('submitButton').addEventListener('click', handleSubmission);

function handleSubmission() {
    // Log the types and values of the variables
    console.log('savedItemsCount:', savedItemsCount, 'Type:', typeof savedItemsCount);
    console.log('visibleEventsCount:', visibleEventsCount, 'Type:', typeof visibleEventsCount);

    // Check if all items are saved
    // if (savedItemsCount >= visibleEventsCount) {
        // If all items are saved, proceed to redirection
        console.log('All items are saved. Redirecting...');
        postLocalStorageData();

        // window.location.href = '1.php'; // Replace with your desired URL
    // } else {
    //     // Show an alert if not all items are saved
    //     console.log('Not all items are saved. Showing alert.');
    //     alert('You have to save all items before proceeding.');
    // }
}

// Function to post data to PHP script
function postLocalStorageData() {
    const localStorageKey = 'modalSavedData';
    const data = localStorage.getItem(localStorageKey);

    console.log('Local Storage Key:', localStorageKey);
    console.log('Local Storage Data:', data);

    if (data) {
        // Parse data as JSON
        try {
            const parsedData = JSON.parse(data);

            console.log('Parsed Data:', parsedData);

            // Ensure parsedData is an object and not empty
            if (typeof parsedData === 'object' && parsedData !== null && Object.keys(parsedData).length > 0) {
                // Clean and prepare data
                const cleanedData = Object.keys(parsedData).reduce((acc, key) => {
                    const item = parsedData[key];
                    // Clean up null or empty values
                    if (item && item.state === 'saved') {
                        acc[key] = {
                            type: item.type || 'unknown', // Default to 'unknown' if null
                            Concept: item.Concept || '',
                            caption: item.caption || '',
                            socialMedia: item.socialMedia || [], // Default to empty array if null
                            sponsors: item.sponsors || 'no', // Default to 'no' if null
                            state: item.state || 'unknown', // Default to 'unknown' if null
                            color: item.color || 'defaultColor' // Default color if null
                        };
                    }
                    return acc;
                }, {});

                // Proceed with the fetch request
                fetch('csub.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(cleanedData) // Send cleaned data
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Success:', result);
                    if (result.status === 'success') {
                        displayFormattedData(result.formattedData);
                        localStorage.removeItem(localStorageKey); // Clear local storage after successful processing
                    } else {
                        console.error('Error:', result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                console.error('Parsed data is not an object or is empty.');
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
        }
    } else {
        console.error('No data found in local storage');
    }
}




// Function to log the formatted data
function displayFormattedData(formattedData) {
    // Log the formatted data to the console
    console.log('Formatted Data:', formattedData);
}


// Function to log the formatted data
function displayFormattedData(formattedData) {
    // Log the formatted data to the console
    console.log('Formatted Data:', formattedData);
}

;






// Function to get saved data from local storage
function getSavedData() {
    const data = localStorage.getItem(localStorageKey);
    return data ? JSON.parse(data) : {};
}


    

    


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
    }
   
// Function to show the modal and populate its fields
function showModal(event) {
    
    const savedData = getSavedData();
    const key = `${selectedClient}_${event.id}`;
    const eventData = savedData[key] || {};
    
// Assuming eventData contains information from your event
document.getElementById('Concept').value = eventData.Concept || '';
document.getElementById('caption').value = eventData.hashtags || globalHashtags || '';
document.getElementById('eventDate').value = event.start;
document.getElementById('eventID').value = event.id;

// Format the date for display
const formattedDate = moment(event.start).format('MMMM Do, YYYY');
document.getElementById('modalDateID').textContent = `Task Date: ${formattedDate}`;

// Set the client name
document.getElementById('modalClientName').textContent = `Client: ${selectedClient}`;

// Set the language data
document.getElementById('languageData').textContent = languages;

// Set the selected social media platforms
const socialMediaPlatforms = eventData.socialMedia || [];
const socialMediaCheckboxes = document.querySelectorAll('.social-media-platforms .form-check-input');
socialMediaCheckboxes.forEach(checkbox => {
    checkbox.checked = socialMediaPlatforms.includes(checkbox.value);
});



// Set the sponsorship option
const sponsorshipOption = eventData.sponsors || '';
const sponsorYes = document.getElementById('sponsorYes');
const sponsorNo = document.getElementById('sponsorNo');
 // Clear previous selections
 sponsorYes.checked = false;
    sponsorNo.checked = false;


if (sponsorshipOption === 'yes') {
    sponsorYes.checked = true;
} else if (sponsorshipOption === 'no') {
    sponsorNo.checked = true;
}

// Reset the radio buttons for content type
const typestatic = document.getElementById('staticType');
    const typevideo = document.getElementById('videoType');
    typestatic.checked = false;
    typevideo.checked = false;

    // Set the type based on eventData.type only if eventID matches
    if (savedData.hasOwnProperty(key)) {
        const eventType = eventData.type || '';
        if (eventType === 'static') {
            typestatic.checked = true;
            document.getElementById('staticSection').classList.remove('d-none');
            document.getElementById('videoSection').classList.add('d-none');
        } else if (eventType === 'video') {
            typevideo.checked = true;
            document.getElementById('videoSection').classList.remove('d-none');
            document.getElementById('staticSection').classList.add('d-none');
        }
    } else {
        // If the eventID doesn't match, ensure sections are hidden
        document.getElementById('staticSection').classList.add('d-none');
        document.getElementById('videoSection').classList.add('d-none');
    }





    

    $('#contentModal').modal('show');
}



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

    // Assuming `month` is in 'MM,YYYY' format
    const [monthIndex, year] = month.split(','); // Split into month and year
    const date = new Date(year, parseInt(monthIndex, 10) - 1); // Month is zero-based in JavaScript

    // Format the month as MMMM/YYYY
    const options = { year: 'numeric', month: 'long' };
    const formattedMonth = date.toLocaleDateString('en-US', options); // e.g., 'April 2024'

    option.value = month;
    option.textContent = formattedMonth;

    monthSelect.appendChild(option);
});

const events = [];
let visibleEvents = [];

// Example of fetching and parsing events
response.posting_days.forEach(dayString => {
    const daysArray = dayString.split(',').map(day => {
        return day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
    });

    daysArray.forEach(dayOfWeek => {
        const color = 'purple';
        const dates = getDatesForDayOfWeek(dayOfWeek, startDate, endDate);

        dates.forEach(date => {
            if (date.isBetween(startDate, endDate, null, '[]')) {
                const event = {
                    id: date.format('YYYY-MM-DD'),
                    start: date.format('YYYY-MM-DD'),
                    end: date.format('YYYY-MM-DD'),
                    rendering: 'background',
                    backgroundColor: color,
                    Concept: 'Event on ' + date.format('YYYY-MM-DD'),
                    hashtags: response.hashtags || []
                };
                events.push(event);
            }
        });
    });
});

monthSelect.addEventListener('change', function() {
    const selectedMonths = Array.from(this.selectedOptions).map(option => option.value);

    if (selectedMonths.length > 0) {
        $('#calendar').fullCalendar('removeEvents'); // Remove all existing events
        // Reset counters
        savedItemsCount = 0;
        visibleEventsCount = 0; // Reset visible events count

        // Clear UI counters
        document.getElementById('savedItemsCount').textContent = `Saved Items: ${savedItemsCount}`;
        document.getElementById('visibleEventsCount').textContent = `Visible Events: ${visibleEventsCount}`;


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
        
        // Update the visible events
        visibleEvents = filteredEvents;
        updateVisibleEventsCounter();
    }
});

$('#calendar').fullCalendar({
    viewRender: function(view, element) {
        // Function to be called every time the view changes
        const visibleRange = view.intervalStart.format('YYYY-MM-DD') + '/' + view.intervalEnd.format('YYYY-MM-DD');

        // Example: Update visible events based on the current view
        visibleEvents = $('#calendar').fullCalendar('getEvents').filter(event => {
            const eventDate = moment(event.start);
            return eventDate.isBetween(view.intervalStart, view.intervalEnd, null, '[]');
        });

        updateVisibleEventsCounter();
    }
});

// Function to update the visible events counter
function updateVisibleEventsCounter() {
    visibleEventsCount = visibleEvents.length;

    // Update the counter in the HTML
    document.getElementById('visibleEventsCount').textContent = `Visible Events: ${visibleEventsCount}`;

    // Show or hide the visible events container based on the count
    const visibleEventsContainer = document.getElementById('visibleEventsContainer');
    if (visibleEventsCount > 0) {
        visibleEventsContainer.classList.remove('d-none');
    } else {
        visibleEventsContainer.classList.add('d-none');
    }
}



applySavedEventColors(); // Apply saved event colors after loading events
                        }}
 catch (e) {
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
            const btn = $('<button class="fc-custom-btn btn btn-secondary btn-sm"></button>');
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

            selectedEvent = event.id;
            const eventDate = moment(event.start).format('MMMM Do, YYYY');
            $('#contentModalLabel').text('Event on ' + eventDate);
            $('#modalEventId').text('Event ID: ' + event.id);
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

    // Extract social media links
    const socialMediaPlatform = formData.getAll('socialMedia[]');

    // Extract sponsorship option
    const sponsorshipOption = formData.get('sponsors'); // Will be 'yes', 'no', or undefined

    const data = {
        type: formData.get('eventType'), // 'static' or 'video'
        Concept: formData.get('Concept'),
        caption: formData.get('caption'),
        socialMedia: socialMediaPlatform,
        sponsors: sponsorshipOption, // This can be 'yes', 'no', or undefined
        state: 'saved',
        color: 'blue',
        language: languages || '', // Ensure this is included

    };


// Save data to local storage
saveDataToLocal(selectedClient, formData.get('eventID'), data);

// Update event color
updateEventColor(formData.get('eventID'), 'blue');

// Save the item and update the countsa
saveItem(formData.get('eventID'));

// Hide the modal and refetch events
$('#contentModal').modal('hide');
$('#calendar').fullCalendar('refetchEvents');
    function saveItem(eventID) {
    // Check if the item has already been saved
    if (!savedItemsSet.has(eventID)) {
        // Add the item ID to the Set
        savedItemsSet.add(eventID);

        // Increment the global saved items count
        savedItemsCount++;

    }
}


    // Show the submit button after saving
    document.getElementById('submitButton').classList.remove('d-none');

    // Show the saved items count container
    const savedItemsContainer = document.getElementById('savedItemsContainer');
    savedItemsContainer.classList.remove('d-none');

    // Update the UI to show the saved items count
    document.getElementById('savedItemsCount').textContent = `Saved Items: ${savedItemsCount}`;
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




document.addEventListener('DOMContentLoaded', function() {
    // Get the radio buttons and sections
    const staticTypeRadio = document.getElementById('staticType');
    const videoTypeRadio = document.getElementById('videoType');
    const staticSection = document.getElementById('staticSection');
    const videoSection = document.getElementById('videoSection');

    // Function to toggle sections based on selected radio button
    function toggleSections() {
        if (staticTypeRadio.checked) {
            staticSection.classList.remove('d-none');
            videoSection.classList.add('d-none');
        } else if (videoTypeRadio.checked) {
            staticSection.classList.add('d-none');
            videoSection.classList.remove('d-none');
        }
    }

    // Add event listeners to radio buttons
    staticTypeRadio.addEventListener('change', toggleSections);
    videoTypeRadio.addEventListener('change', toggleSections);

    // Initialize sections based on the current state of the radio buttons
    toggleSections();
});


</script>


</body>
</html>
<style>
    /* Fixed submit button */
#submitButton {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000; /* Ensure it is above other elements */
}

/* Color boxes */
.color-box {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 8px;
    vertical-align: middle;
}

/* Color box specific styles */
.color-box.task {
    background-color: #B2DFDB; /* Light Teal */
}

.color-box.saved {
    background-color: #C8E6C9; /* Light Green */
}

.color-box.done {
    background-color: #BBDEFB; /* Light Blue */
}

/* General styling for the form */
#contentForm {
    background-color: #F9FBE7; /* Light beige background for the form */
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #E0E0E0; /* Light gray border */
}

/* Modal header layout */
.modal-header {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center the title and info */
    text-align: center; /* Center text */
    padding: 15px;
}

/* Styling for the event details */
.modal-header-content {
    width: 100%;
    text-align: center; /* Center the title and info */
}

.modal-title {
    margin-bottom: 10px; /* Space below the title */
}

.modal-header-info {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px;
    flex-wrap: wrap; /* Wrap items if needed */
}

.modal-header-info .info-item {
    margin: 0 10px; /* Space between elements */
    display: flex;
    align-items: center;
}

.modal-header-info .info-item span {
    margin-left: 5px; /* Space between label and content */
}

/* Sections for static and video types */
.event-section {
    margin-bottom: 1.5rem;
}

/* Styling for section headers */
.modal-body .mb-3 {
    margin-bottom: 1.5rem;
}

.form-label {
    color: #333; /* Darker text color for labels */
    font-weight: bold;
}

/* Horizontal alignment and spacing for checkboxes and radio buttons */
.social-media-platforms, .sponsor-options {
    display: flex;
    flex-wrap: wrap; /* Allow wrapping of items */
    gap: 15px; /* Space between items */
    margin-bottom: 1rem;
}

/* Styling for individual checkboxes and radio buttons */
.form-check {
    display: flex;
    align-items: center;
    gap: 8px; /* Space between checkbox and label */
}

/* Styling for the checkbox and radio button labels */
.form-check-label {
    font-size: 1rem; /* Adjust font size */
    color: #555; /* Slightly lighter text color */
}

/* Color for selected radio buttons and checkboxes */
.form-check-input:checked {
    background-color: #009688; /* Teal background for checked state */
    border-color: #00796B; /* Darker teal border */
}

/* Styling for the save and close buttons */
.modal-footer .btn {
    border-radius: 5px;
}

.btn-secondary {
    background-color: #607D8B; /* Slate gray background */
    color: white; /* White text */
}

.btn-secondary:hover {
    background-color: #455A64; /* Darker slate gray on hover */
}

.btn-primary {
    background-color: #0288D1; /* Bright blue background */
    color: white; /* White text */
}

.btn-primary:hover {
    background-color: #0277BD; /* Darker blue on hover */
}

/* Adjusting the container alignment */
.container {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Aligns items to the start (left) */
}

/* Adding relaxed colors to the selection elements */
#clientSelect, #monthSelect {
    background-color: #E0F7FA; /* Light Cyan */
    color: #000000; /* Black text */
    border: 2px solid #00796B; /* Teal border */
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px; /* Add space between the elements */
}

/* Aligning the guide to the left and giving relaxed colors to the color boxes */
.guide {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Aligns guide items to the start (left) */
    margin-top: 20px;
}

/* Styling for the languages label */
.language-label {
    font-weight: normal;
    margin-right: 5px; /* Space between label and content */
}

/* Styling for the language data container */
#languageData {
    display: inline-block;
    font-weight: normal; /* Regular font weight for the content */
    color: #333; /* Color for the content */
}

</style>
