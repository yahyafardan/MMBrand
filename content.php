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
        #calendar-container {
            width: 600px; /* Adjust as needed */
            margin: 0 auto;
        }
        #calendar {
            max-width: 100%;
        }
        .fc-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc-event .fc-title {
            max-width: 120px; /* Adjust as needed */
            height: 20px;     /* Adjust as needed */
            overflow: hidden; /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis for overflowed text */
            white-space: nowrap; /* Prevent line breaks */
            text-align: center;
        }
        .fc-day.fc-day-posting {
            background-color: rgba(0, 255, 0, 0.3); /* Default green with transparency */
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
                <span class="color-box" style="background-color: blue;"></span> Blue: Event 2
            </div>
            <div>
                <span class="color-box" style="background-color: red;"></span> Red: Event 1
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSelect = document.getElementById('clientSelect');
            const resultContainer = document.getElementById('resultContainer');

            let startDate, endDate; // Declare global variables

            clientSelect.addEventListener('change', function() {
                const clientName = this.value;

                if (clientName) {
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
                                    endDate = moment(response.end_date);

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
                                        let current = start.clone().day(dayOfWeek);

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
                                    response.posting_days.forEach(day => {
                                        const dayOfWeek = day; // day is already in correct format
                                        const color = 'red'; // Default color if not specified
                                        const dates = getDatesForDayOfWeek(dayOfWeek, startDate, endDate);

                                        dates.forEach(date => {
                                            events.push({
                                                start: date.format('YYYY-MM-DD'),
                                                end: date.format('YYYY-MM-DD'),
                                                rendering: 'background',
                                                backgroundColor: color
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

                    xhr.send('client_name=' + encodeURIComponent(clientName));
                }
            });

            function checkViewBounds() {
                const calendar = $('#calendar').fullCalendar('getCalendar');
                const view = calendar.view;
                const viewStart = view.intervalStart;
                const viewEnd = view.intervalEnd;

                if (viewStart.isBefore(startDate) || viewEnd.isAfter(endDate)) {
                    calendar.gotoDate(startDate);
                    calendar.changeView('month', startDate);
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
                    if (event.url) {
                        element.find('.fc-title').html('<a href="' + event.url + '" target="_blank">' + event.title + '</a>');
                    }
                    element.find('.fc-title').attr('title', event.title);
                },
                viewRender: function(view) {
                    if (startDate && endDate) {
                        checkViewBounds();
                    }
                }
            });
        });
    </script>
</body>
</html>
