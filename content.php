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
$sessionRoleName =$_SESSION['username'];
$sessionUsername =$_SESSION['role_name'];


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
<!-- Container for displaying the required number of posts and videos -->
<div id="itemsRequiredContainer"
class="d-none">
    <span id="requiredPostsCount">Required Number of Posts: </span><br>
    <span id="requiredVideosCount">Required Number of Videos: </span>
</div>

<!-- Sticky Button -->
<button id="viewLocalStorageButton"
class="" style="
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
<button id="submitButton" class="btn btn-primary d-none">Submit </button>
<button id="previewButton" class="btn btn-primary d-none">preview</button>


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
                        <div>
                        <label for="title" class="form-label">title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption (Text)</label>
                            <textarea class="form-control" id="caption" name="caption" rows="3" required></textarea>
                            
                        </div>
  <!-- Additional Static Fields -->
<div id="additionalFields" class="d-none">
    <div class="mb-3">
        <label for="ConceptAdditional" class="form-label">Additional Concept (theme)</label>
        <input type="text" class="form-control" id="ConceptAdditional" name="ConceptAdditional">
    </div>
    <div>
                        <label for="Additionaltitle" class="form-label">Additionaltitle</label>
                        <input type="text" class="form-control" id="Additionaltitle" name="title" required>
                        </div>
    <div class="mb-3">
        <label for="captionAdditional" class="form-label">Additional Caption (Text)</label>
        <textarea class="form-control" id="captionAdditional" name="captionAdditional" rows="3"></textarea>
    </div>
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
                        <div class="mb-3">
                            <label for="VideoConcept" class="form-label">Concept (theme)</label>
                            <input type="text" class="form-control" id="VideoConcept" name="VideoConcept" required>
                        </div>
                        <div class="mb-3">
                            <label for="VideoCaption" class="form-label">Caption (Text)</label>
                            <textarea class="form-control" id="VideoCaption" name="VideoCaption" rows="3" required></textarea>
                        </div>
                        <!-- Social Media Platforms Section in Modal -->
                        <div class="mb-3">
                            <label class="form-label">Social Media Platforms</label>
                            <div class="social-media-platforms">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoXAccount" name="videoSocialMedia[]" value="x_account_link">
                                    <label class="form-check-label" for="VideoXAccount">X</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoInstagramAccount" name="videoSocialMedia[]" value="instagram_account_link">
                                    <label class="form-check-label" for="VideoInstagramAccount">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoLinkedinAccount" name="videoSocialMedia[]" value="linkedin_account_link">
                                    <label class="form-check-label" for="VideoLinkedinAccount">LinkedIn</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoFacebookAccount" name="videoSocialMedia[]" value="facebook_account_link">
                                    <label class="form-check-label" for="VideoFacebookAccount">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoYoutubeAccount" name="videoSocialMedia[]" value="youtube_account_link">
                                    <label class="form-check-label" for="VideoYoutubeAccount">YouTube</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoSnapchatAccount" name="videoSocialMedia[]" value="snapchat_account_link">
                                    <label class="form-check-label" for="VideoSnapchatAccount">Snapchat</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="VideoTiktokAccount" name="videoSocialMedia[]" value="tiktok_account_link">
                                    <label class="form-check-label" for="VideoTiktokAccount">TikTok</label>
                                </div>
                            </div>
                        </div>
                        <!-- Sponsors Section in Modal -->
                        <div class="mb-3">
                            <label>Sponsored</label>
                            <div class="sponsor-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="videoSponsors" id="VideoSponsorYes" value="yes">
                                    <label class="form-check-label" for="VideoSponsorYes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="videoSponsors" id="VideoSponsorNo" value="no">
                                    <label class="form-check-label" for="VideoSponsorNo">No</label>
                                </div>
                            </div>
                        </div>
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



<!-- Modal for displaying stored form data -->
<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel">Stored Form Data</h5>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Date</th>
                            <th>Language</th>
                            <th>Concept</th>
                            <th>Caption</th>
                            <th>Social Media</th>
                            <th>Sponsorship</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data rows will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>





<script>
        const sessionRoleName = "<?php echo $sessionRoleName; ?>";
        const sessionUsername = "<?php echo $sessionUsername; ?>";
        console.log('Session Role Name:', sessionRoleName);
        console.log('Session Username:', sessionUsername);
        let savedItemsCount=0;

    // localStorage.clear();

    

//     // Add event listener for the view local storage button
    document.getElementById('viewLocalStorageButton').addEventListener('click', displayLocalStorageData);
// });

    // Function to display local storage data in the console
function displayLocalStorageData() {
    const allKeys = Object.keys(localStorage);

    console.group('--- Local Storage Contents ---');

    if (allKeys.length === 0) {
        //console.log('Local storage is empty.');
    } else {
        allKeys.forEach(key => {
            const value = localStorage.getItem(key);
            console.group(`Key: ${key}`);
            //console.log('Value:', value);
            console.groupEnd();
        });
    }

    console.groupEnd();
}



document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const localStorageKey = 'modalSavedData';
    let startDate, endDate;
    let selectedEvent;
    let selectedClient;
    let globalHashtags = '';
    let nOfPosts, nOfVideos, languages; // Added new variables
      


    document.getElementById('submitButton').addEventListener('click', handleSubmission);

function handleSubmission() {
    
    // Log the types and values of the variables
    console.log('savedItemsCount:', savedItemsCount, 'Type:', typeof savedItemsCount);
    console.log('visibleEventsCount:', visibleEventsCount, 'Type:', typeof visibleEventsCount);

    // Check if all items are saved
    if (savedItemsCount >= visibleEventsCount) {
        // If all items are saved, proceed to redirection
        console.log('All items are saved. Redirecting...');
        postLocalStorageData();

// Refresh the current page
window.location.reload();}else{alert("please fill all the events")

}
   

}

// Function to post data to PHP script
function postLocalStorageData() {
    const data = localStorage.getItem(localStorageKey);
    const sessionRoleName = "c1";
    const sessionUsername = "content";

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
                            title: item.title || '', // Ensure title field is included
                            Concept: item.Concept || '',
                            caption: item.caption || '',
                            socialMedia: Array.isArray(item.socialMedia) ? item.socialMedia : [], // Default to empty array if not an array
                            sponsors: item.sponsors || 'no', // Default to 'no' if null
                            state: item.state || 'unknown', // Default to 'unknown' if null
                            color: item.color || 'defaultColor', // Default color if null
                            language: item.language || '' // Ensure language is included
                        };
                        // Add the session role and username
                        acc[key].role_name = sessionRoleName;
                        acc[key].username = sessionUsername;
                    }
                    return acc;
                }, {});

                // Log the cleaned data before sending
                console.log('Cleaned Data to be sent:', cleanedData);

                // Proceed with the fetch request
                fetch('csub.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(cleanedData) // Send cleaned data
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
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
    //console.log('Formatted Data:', formattedData);
}


// Function to log the formatted data
function displayFormattedData(formattedData) {
    // Log the formatted data to the console
    //console.log('Formatted Data:', formattedData);
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

   

    // Function to save data to localStorage
    // function saveDataToLocal(clientName, eventId, data) {
    //     const savedData = getSavedData();
    //     const key = `${clientName}_${eventId}`;
    //     savedData[key] = data;
    //     localStorage.setItem(localStorageKey, JSON.stringify(savedData));
    // }
    // Function to get saved data from local storage
// Function to get saved data from local storage
function saveDataToLocal(clientName, eventId, data) {
    const savedData = getSavedData();
    
    // Debugging: Log inputs and key generation
    //console.log('Saving data...');
    //console.log('Client Name:', clientName);
    //console.log('Event ID:', eventId);
    
    // Ensure eventId does not include clientName or extra underscores
    const cleanEventId = eventId.includes(clientName) ? eventId.split('_').slice(1).join('_') : eventId;
    
    // Generate the key
    const key = `${clientName}_${cleanEventId}`;
    //console.log('Generated Key:', key);
    
    // Save data
    savedData[key] = data;
    localStorage.setItem(localStorageKey, JSON.stringify(savedData));
}

function showModal(event) {
    // Get saved data from local storage
    const savedData = getSavedData();
    
    // Log the selected client and event ID
    //console.log('Selected Client:', selectedClient);
    //console.log('Event ID:', event.id);
    
    // Create the key without extra underscores
    const key = event.id;
    
    //console.log('Key:', key);
    //console.log('Saved Data:', savedData);

    const eventData = savedData[key] || {};
    //console.log('Event Data:', eventData);

    // Populate form fields with data from local storage
    document.getElementById('Concept').value = eventData.Concept || '';
    document.getElementById('caption').value = eventData.caption || globalHashtags || '';
    document.getElementById('eventDate').value = event.start;
    document.getElementById('eventID').value = event.id;
    document.getElementById('ConceptAdditional').value = eventData.ConceptAdditional || '';
    document.getElementById('captionAdditional').value = eventData.captionAdditional || '';
    document.getElementById('Additionaltitle').value = eventData.additionalTitle || '';
    document.getElementById('title').value = eventData.title || '';

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
    sponsorYes.checked = sponsorshipOption === 'yes';
    sponsorNo.checked = sponsorshipOption === 'no';

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
        // Ensure sections are hidden if eventID doesn't match
        document.getElementById('staticSection').classList.add('d-none');
        document.getElementById('videoSection').classList.add('d-none');
    }

    // Show the modal with options
    $('#contentModal').modal({
        backdrop: 'static',  // Prevent closing when clicking outside
        keyboard: false      // Prevent closing when pressing ESC
    });
}

// Call this function when needed
// showModal(event); // Ensure to pass the event when calling this functionent when calling this function



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

                //console.log('Response from server:', response);

                if (response.error) {
                    document.getElementById('resultContainer').innerHTML = `<p style="color: red;">Error: ${response.error}</p>`;
                    console.error('Server Error:', response.error);
                } else {
                    startDate = moment(response.start_date);
                    endDate = adjustEndDate(moment(response.end_date));
                    globalHashtags = response.hashtags || '';
                    languages = response.languages || '';
                    document.getElementById('requiredPostsCount').textContent = `Required Number of Posts: ${response.n_of_posts}`;
                    document.getElementById('requiredVideosCount').textContent = `Required Number of Videos: ${response.n_of_videos}`;
                    document.getElementById('itemsRequiredContainer').classList.remove('d-none');
                    document.getElementById('additionalFields').classList.add('d-none');

                    if (languages==="both"){
                        document.getElementById('additionalFields').classList.remove('d-none');


                    }

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

                    //console.log('Months added to dropdown:', response.months);

                    const events = [];
                    let visibleEvents = [];

                    // Example of fetching and parsing events
// Example of fetching and parsing events
response.posting_days.forEach(dayString => {
    const daysArray = dayString.split(',').map(day => {
        return day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
    });

    // Fetch saved events from local storage
    const savedEvents = JSON.parse(localStorage.getItem('modalSavedData')) || {}; // Associative array of saved event IDs
    //console.log('Saved Events from localStorage:', savedEvents);

    daysArray.forEach(dayOfWeek => {
        const color = 'red'; // Default color
        const dates = getDatesForDayOfWeek(dayOfWeek, startDate, endDate);

        dates.forEach(date => {
            if (date.isBetween(startDate, endDate, null, '[]')) {
                const eventId = selectedClient + '_' + date.format('YYYY-MM-DD'); // Include client name
                const isSaved = savedEvents.hasOwnProperty(eventId); // Check if event ID is in saved events

                // Log both sides of the if statement
                //console.log('Generated Event ID:', eventId);
                //console.log('Saved Events Keys:', Object.keys(savedEvents));
                //console.log('Key Exists:', isSaved);

                const event = {
                    id: eventId,
                    start: date.format('YYYY-MM-DD'),
                    end: date.format('YYYY-MM-DD'),
                    rendering: 'background',
                    backgroundColor: isSaved ? 'blue' : color, // Set color based on saved status
                    Concept: 'Event on ' + date.format('YYYY-MM-DD'),
                    hashtags: response.hashtags || []
                };
                events.push(event);
            }
        });
    });
});

//console.log('Events created:', events);


// Function to save an event ID to local storage
function saveEvent(eventId) {
    let savedEvents = JSON.parse(localStorage.getItem('modalSavedData')) || {};
    savedEvents[eventId] = true; // Mark event as saved
    localStorage.setItem('modalSavedData', JSON.stringify(savedEvents));
}
function countSavedItems(clientName, monthYear) {
  // Retrieve the saved data from local storage
  const savedData = JSON.parse(localStorage.getItem('modalSavedData')) || {};
  
  // Initialize count
  let count = 0;

  // Iterate over the keys in savedData
  for (const key in savedData) {
    if (savedData.hasOwnProperty(key)) {
      // Extract client name and date from the key
      const [storedClientName, date] = key.split('_');
      const [yearMonth] = date.split('-');
      
      // Check if the client name and month match
      if (storedClientName === clientName && yearMonth === monthYear) {
        count++;
      }
    }
  }

  return count;
}





                    monthSelect.addEventListener('change', function() {



                    
// Initial call to set count based on default selections
updateSavedItemsCount();








                        const selectedMonths = Array.from(this.selectedOptions).map(option => option.value);


                        if (selectedMonths.length > 0) {
                            $('#calendar').fullCalendar('removeEvents'); // Remove all existing events
                            // Reset counters
                            visibleEventsCount = 0; // Reset visible events count

                            // Clear UI counters
                            // document.getElementById('savedItemsCount').textContent = `Saved Items: ${savedItemsCount}`;
                            document.getElementById('visibleEventsCount').textContent = `Visible Events: ${visibleEventsCount}`;
                            document.getElementById('requiredPostsCount').textContent = `Required Number of Posts: ${response.n_of_posts}`;
                            document.getElementById('requiredVideosCount').textContent = `Required Number of Videos: ${response.n_of_videos}`;

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
                        function updateCounter(clientName, monthSelect) {
   savedItemsCount = countSavedItems(clientName, monthSelect);
  document.getElementById('savedItemsCount').textContent = savedItemsCount;
}


                    });

                    $('#calendar').fullCalendar({
                        viewRender: function(view, element) {
                            // Function to be called every time the view changes
                            const visibleRange = view.intervalStart.format('YYYY-MM-DD') + '/' + view.intervalEnd.format('YYYY-MM-DD');
                            //console.log('Visible range:', visibleRange);

                            // Example: Update visible events based on the current view
                            visibleEvents = $('#calendar').fullCalendar('getEvents').filter(event => {
                                const eventDate = moment(event.start);
                                return eventDate.isBetween(view.intervalStart, view.intervalEnd, null, '[]');
                            });

                            //console.log('Visible events:', visibleEvents);

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
                }
            }catch (e) {
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
         // Get values from input fields
         const additionalTitle = document.getElementById('Additionaltitle').value;
        const title = document.getElementById('title').value;
    const form = document.getElementById('contentForm');
    const formData = new FormData(form);
    if (!validateForm()) {
        event.stopPropagation(); // Prevent modal from closing

            event.preventDefault(); // Prevent form submission if validation fails
        }else{


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
        language: languages || '', // Ensure this is included
        ConceptAdditional: formData.get('ConceptAdditional'),
        captionAdditional: formData.get('captionAdditional'),
        additionalTitle: additionalTitle,
        title: title,

    };


// Save data to local storage
saveDataToLocal(selectedClient, formData.get('eventID'), data);

// Update event color
updateEventColor(formData.get('eventID'), 'blue');

// Save the item and update the countsa +HERE+
// saveItem(formData.get('eventID'));

// Hide the modal and refetch events
$('#contentModal').modal('hide');
// $('#calendar').fullCalendar('refetchEvents');
// function saveItem(eventID, itemData) {
//     const localStorageKey = 'modalSavedData';
    
//     // Retrieve existing data from local storage
//     let data = localStorage.getItem(localStorageKey);
//     let parsedData = {};

//     if (data) {
//         try {
//             parsedData = JSON.parse(data);
//         } catch (error) {
//             console.error('Error parsing JSON from local storage:', error);
//             return;
//         }
//     }

//     // Check if the item already exists in local storage
//     if (parsedData.hasOwnProperty(eventID)) {
//         console.log('Item already exists, updating it.');

//         // If the item exists, update it
//         parsedData[eventID] = itemData;
//     } else {
//         console.log('Item does not exist, adding it.');

//         // If the item does not exist, add it
//         parsedData[eventID] = itemData;
//     }

//     // Save the updated data back to local storage
//     localStorage.setItem(localStorageKey, JSON.stringify(parsedData));

//     // Increment the global saved items count and update display
//     savedItemsCount = Object.keys(parsedData).length;
//     document.getElementById('savedItemsCount').textContent = `Saved Items: ${savedItemsCount}`;
    
//     // Show the container and buttons if needed
//     const savedItemsContainer = document.getElementById('savedItemsContainer');
//     if (savedItemsCount > 0) {
//         savedItemsContainer.classList.remove('d-none'); // Show container
//         document.getElementById('submitButton').classList.remove('d-none'); // Show submit button
//         document.getElementById('previewButton').classList.remove('d-none'); // Show preview button
//     } else {
//         savedItemsContainer.classList.add('d-none'); // Hide container
//     }
    
//     console.log('Saved Items Count:', savedItemsCount);
// }



    // Show the submit button after saving
    document.getElementById('submitButton').classList.remove('d-none');
    document.getElementById('previewButton').classList.remove('d-none');


    // Show the saved items count container
    const savedItemsContainer = document.getElementById('savedItemsContainer');
    savedItemsContainer.classList.remove('d-none');

    // Update the UI to show the saved items count
    document.getElementById('savedItemsCount').textContent = `Saved Items: ${savedItemsCount}`;
    function populateTable() {
    const storedData = localStorage.getItem('modalSavedData');
    const parsedData = JSON.parse(storedData);
    //console.log('Raw data from local storage:', storedData);
    //console.log('Parsed data:', parsedData);

    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = ''; // Clear existing rows

    if (parsedData) {
    for (const [id, data] of Object.entries(parsedData)) {
        // Extract client name and event date from ID
        const [clientName, eventDate] = id.split('_');
        
        // Check if the client name matches the selected client
        if (clientName === selectedClient) {
            const row = document.createElement('tr');
            const formattedDate = new Date(eventDate).toLocaleDateString();

            row.innerHTML = `
                <td>${clientName}</td>
                <td>${formattedDate}</td>
                <td>${data.language}</td>
                <td>${data.Concept}</td>
                <td>${data.caption}</td>
                <td>${data.socialMedia.join(', ')}</td>
                <td>${data.sponsors}</td>
            `;

            tableBody.appendChild(row);
        }
    }
} else {
    //console.log('No data found in local storage');
}
}




document.getElementById('previewButton').addEventListener('click', function() {
    //console.log('Preview button clicked');

    populateTable();
    const dataModal = new bootstrap.Modal(document.getElementById('dataModal'));
    dataModal.show();
});

// Trigger the modal and populate table when needed
updateSavedItemsCount()



}});
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
function validateForm() {
    let isValid = true;
    const staticSection = document.getElementById('staticSection');
    const videoSection = document.getElementById('videoSection');

    // Check if a content type is selected
    const eventType = document.querySelector('input[name="eventType"]:checked');
    if (!eventType) {
        alert('Please select a content type.');
        isValid = false;
        return isValid; // Stop further validation
    }

    // Check if static section is visible and validate its fields
    if (!staticSection.classList.contains('d-none')) {
        const staticFields = {
            Concept: staticSection.querySelector('#Concept'),
            caption: staticSection.querySelector('#caption'),
            socialMediaCheckboxes: staticSection.querySelectorAll('input[name="socialMedia[]"]:checked'),
            sponsorOption: document.querySelector('input[name="sponsors"]:checked')
        };
        
        // Check if 'Concept' field is empty
        if (!staticFields.Concept.value.trim()) {
            staticFields.Concept.classList.add('is-invalid');
            isValid = false;
            alert('Please fill in the Concept (theme) field.');
            return isValid; // Stop further validation
        } else {
            staticFields.Concept.classList.remove('is-invalid');
        }
        
        // Check if 'caption' field is empty
        if (!staticFields.caption.value.trim()) {
            staticFields.caption.classList.add('is-invalid');
            isValid = false;
            alert('Please fill in the Caption (Text) field.');
            return isValid; // Stop further validation
        } else {
            staticFields.caption.classList.remove('is-invalid');
        }
        
        // Check if at least one social media checkbox is selected
        if (staticFields.socialMediaCheckboxes.length === 0) {
            isValid = false;
            alert('Please select at least one social media platform.');
            return isValid; // Stop further validation
        }

        // Check if a sponsorship option is selected
        if (!staticFields.sponsorOption) {
            isValid = false;
            alert('Please select if the content is sponsored or not.');
            return isValid; // Stop further validation
        }
    }
    
    // Check if video section is visible and validate its fields
    if (!videoSection.classList.contains('d-none')) {
        const videoFields = {
            VideoConcept: videoSection.querySelector('#VideoConcept'),
            VideoCaption: videoSection.querySelector('#VideoCaption'),
            videoSocialMediaCheckboxes: videoSection.querySelectorAll('input[name="videoSocialMedia[]"]:checked'),
            videoSponsorOption: document.querySelector('input[name="videoSponsors"]:checked')
        };

        // Check if 'VideoConcept' field is empty
        if (!videoFields.VideoConcept.value.trim()) {
            videoFields.VideoConcept.classList.add('is-invalid');
            isValid = false;
            alert('Please fill in the Video Concept (theme) field.');
            return isValid; // Stop further validation
        } else {
            videoFields.VideoConcept.classList.remove('is-invalid');
        }

        // Check if 'VideoCaption' field is empty
        if (!videoFields.VideoCaption.value.trim()) {
            videoFields.VideoCaption.classList.add('is-invalid');
            isValid = false;
            alert('Please fill in the Video Caption (Text) field.');
            return isValid; // Stop further validation
        } else {
            videoFields.VideoCaption.classList.remove('is-invalid');
        }

        // Check if at least one social media checkbox is selected
        if (videoFields.videoSocialMediaCheckboxes.length === 0) {
            isValid = false;
            alert('Please select at least one social media platform for the video.');
            return isValid; // Stop further validation
        }

        // Check if a sponsorship option is selected
        if (!videoFields.videoSponsorOption) {
            isValid = false;
            alert('Please select if the video content is sponsored or not.');
            return isValid; // Stop further validation
        }
    }

    return isValid;
}
function updateSavedItemsCount() {
    const monthSelect = document.getElementById('monthSelect');
    const clientSelect = document.getElementById('clientSelect');
    const selectedMonth = monthSelect.value; // e.g., "2024 09"
    const selectedClientName = clientSelect.value;
    const localStorageKey = 'modalSavedData';
    const savedItemsCountElement = document.getElementById('savedItemsCount');
    const savedItemsContainer = document.getElementById('savedItemsContainer');

    console.log('Selected Month:', selectedMonth);
    console.log('Selected Client Name:', selectedClientName);

    // Retrieve and parse data from local storage
    const data = localStorage.getItem(localStorageKey);
    console.log('Local Storage Data:', data);

    if (data) {
        try {
            const parsedData = JSON.parse(data);
            console.log('Parsed Data:', parsedData);

            // Ensure parsedData is an object and not empty
            if (typeof parsedData === 'object' && parsedData !== null) {
                console.log('Parsed Data is an object and not null');

                // Initialize count
                let savedItemsCounter = 0;

                // Iterate through the data
                for (const key in parsedData) {
                    if (parsedData.hasOwnProperty(key)) {
                        const item = parsedData[key];

                        // Extract clientName and date from the key
                        const [storedClientName, storedDate] = key.split('_');
                        console.log('Stored Client Name:', storedClientName);
                        console.log('Stored Date:', storedDate);

                        // Format the stored date
                        const formattedStoredDate = `${storedDate.slice(5, 7)},${storedDate.slice(0, 4)}`;
                        console.log('Formatted Stored Date:', formattedStoredDate);
                        console.log('Selected Month:', selectedMonth);

                        // Check if item matches clientName and if the stored date matches the selected month
                        if (
                            storedClientName === selectedClientName && 
                            formattedStoredDate === selectedMonth
                        ) {
                            console.log('Item matches:', {
                                storedClientName: storedClientName,
                                selectedClientName: selectedClientName,
                                formattedStoredDate: formattedStoredDate,
                                selectedMonth: selectedMonth,
                                item: item
                            });
                            savedItemsCounter++;
                            savedItemsCount++;
                        } else {
                            console.log('Item does not match:', {
                                storedClientName: storedClientName,
                                selectedClientName: selectedClientName,
                                formattedStoredDate: formattedStoredDate,
                                selectedMonth: selectedMonth,
                                item: item
                            });
                        }
                    }
                }

                // Update the saved items count
                if (savedItemsCounter > 0) {
                    savedItemsContainer.classList.remove('d-none'); // Show container
                    document.getElementById('submitButton').classList.remove('d-none'); // Show submit button
                    document.getElementById('previewButton').classList.remove('d-none'); // Show preview button
                } else {
                    savedItemsContainer.classList.add('d-none'); // Hide container if no items
                }

                // Update the saved items count display
                savedItemsCountElement.textContent = `Saved Items: ${savedItemsCounter}`;

                // Display the saved items count
                console.log("TESTTT", savedItemsCountElement.textContent, savedItemsCounter);
                console.log('Saved Items Count:', savedItemsCounter);
            } else {
                console.error('Parsed data is not an object or is empty.');
                savedItemsContainer.classList.add('d-none'); // Hide container
            }
        } catch (error) {
            console.error('Error parsing JSON:', error);
            savedItemsContainer.classList.add('d-none'); // Hide container
        }
    } else {
        console.error('No data found in local storage');
        savedItemsContainer.classList.add('d-none'); // Hide container
    }
}






    


</script>


</body>
</html>
<style>
    /* Fixed submit button */
    #submitButton,
#previewButton {
    position: fixed;
    bottom: 20px;
    z-index: 1000; /* Ensure it is above other elements */
}

#submitButton {
    right: 20px; /* Position the Submit button */
}

#previewButton {
    right: 200px; /* Position the Preview button to the left of the Submit button */
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
