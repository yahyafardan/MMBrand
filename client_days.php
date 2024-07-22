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
        <div id="calendar-container">
            <div id="calendar"></div>
        </div>
        <div class="guide mt-4">
            <h5>Event Color Guide:</h5>
            <div>
                <span class="color-box" style="background-color: red;"></span> Red: Event 1
            </div>
            <div>
                <span class="color-box" style="background-color: blue;"></span> Blue: Event 2
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
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                editable: true,
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        url: 'fetch_events.php', // Adjust the endpoint as needed
                        dataType: 'json',
                        success: function(data) {
                            var events = [];
                            $(data).each(function() {
                                events.push({
                                    title: $(this).attr('title'),
                                    start: $(this).attr('start'),
                                    backgroundColor: $(this).attr('backgroundColor')
                                });
                            });
                            callback(events);
                        }
                    });
                }
            });

            const clientSelect = document.getElementById('clientSelect');
            const resultContainer = document.getElementById('resultContainer');

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
                                    const { start_date, end_date, posting_days } = response;
                                    const startDate = moment(start_date);
                                    const endDate = moment(end_date);

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
                                    posting_days.forEach(day => {
                                        const dayOfWeek = day.day_of_week;
                                        const color = day.color || 'rgba(0, 255, 0, 0.3)';
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
        });
    </script>
</body>
</html>
