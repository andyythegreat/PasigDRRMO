<?php
session_start();
$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$sql = "SELECT Logo FROM c3_logo LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$generalLogoPath = $result->fetch_assoc()['Logo'];

$sql = "SELECT Logo FROM c3_addaccount WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $logoPath = $row['Logo'];
    $showLogo = !empty($logoPath);
} else {
    $showLogo = false;
}

$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | BRGY Announcements </title>
    <link rel="stylesheet" href="BRGY_Announcements55.css">
    <link rel="shortcut icon" type="image/png" href="<?php echo htmlspecialchars($generalLogoPath); ?>">
</head>
<body>

<!-- navbar -->
<nav class="navbar">
    <div class="logo_item">
        <img src="<?php echo htmlspecialchars($generalLogoPath); ?>" alt=""></i>Pasig City DRRMO
    </div>

    <div class="navbar_content">

<!-- LIVE TIME AND WEATHER FORECAST -->
<div id="weather">Loading Weather Data...</div>

<script>
function fetchWeather(latitude, longitude) {
const url = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current_weather=true&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m`;

fetch(url)
    .then(response => response.json())
    .then(data => {
        const temperature = data.current_weather.temperature;
        const windSpeed = data.current_weather.windspeed;
        const weatherCode = data.current_weather.weathercode;

        let weatherIcon = "";
        if (weatherCode === 0) {
            weatherIcon = "☀️"; // CLEAR SKY 
        } else if (weatherCode >= 1 && weatherCode <= 3) { 
            weatherIcon = "⛅"; // PARTLY CLOUDY  
        } else if (weatherCode >= 51 && weatherCode <= 67) { 
            weatherIcon = "🌧️"; // LIGHT TO MODERATE RAIN 
        } else if (weatherCode >= 80 && weatherCode <= 86) { 
            weatherIcon = "🌧️"; // RAIN SHOWERS
        } else if (weatherCode >= 95 && weatherCode <= 99) { 
            weatherIcon = "⛈️"; // THUNDERSTORM
        } else {
            weatherIcon = "❄️"; // SNOW OR OTHER WEATHER CONDITIONS
        }

        const weatherString = `
            <div>
                <span class="weather-icon">${weatherIcon}</span>
                <span class="weather-info">
                    <strong>${temperature}°C</strong>
                    <br>Wind: ${windSpeed} m/s
                </span>
            </div>
        `;
        
        document.getElementById('weather').innerHTML = weatherString;
    })
    .catch(error => {
        console.error('Error fetching the weather data:', error);
        document.getElementById('weather').innerHTML = 'Failed to load weather data.';
    });
}

function getLocation() {
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        position => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            fetchWeather(latitude, longitude);
        },
        error => {
            console.error('Error getting location:', error);
            document.getElementById('weather').innerHTML = 'Location access denied. Unable to load weather data.';
        }
    );
} else {
    document.getElementById('weather').innerHTML = 'Geolocation is not supported by this browser.';
}
}

getLocation();
</script>

<div id="clock"></div>
<script>
    function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    // Add leading zeros to minutes and seconds
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    // Display the time
    const timeString = hours + ':' + minutes + ':' + seconds;
    document.getElementById('clock').textContent = timeString;
    }

    // Update the clock every second
    setInterval(updateClock, 1000);

    // Initial call to display the time immediately
    updateClock();
</script>

    <h2><?php 
                    $user = $_SESSION['Username'];
                    $user_display = str_replace('BRGY_', 'BARANGAY ', $user);
                    $user_words = explode(' ', $user_display);
                    $second_word = isset($user_words[1]) ? $user_words[1] : '';
                    echo "{$user_words[0]} <span style='color: #062B82;'>{$second_word}</span>";
                ?>
            </h2>
            
<button class="btn" id="notifBell">
    <img src="images/Notif_Bell.png">
    <span id="notifIndicator" class="notif-indicator"></span>
</button>


    <div id="notificationDiv">
        <h2>Notifications</h2>
        <p id="notifications">No notifications yet</p>
    </div>
            
            <div class="logo-container">
            <?php if ($showLogo): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="User Logo" class="user-logo" onclick="toggleDropdown()">
                <div id="dropdownMenu" class="dropdown-content">
                    <a href="BRGY_Profile.php">
                    <div class="brgy-profile-icon-container">
                    <img src="images/BRGYProfile.png" alt="Profile Icon"  style="width: 23px; height: 23px; margin-right: 15px;" class="brgy-profile-icon"> Profile
                    </div>
                    </a>
            </div>
            </div>
            <?php endif; ?>

            <script>
                function toggleDropdown() {
                var dropdown = document.getElementById("dropdownMenu");
                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            }

                window.onclick = function(event) {
            if (!event.target.matches('.user-logo')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
                    }
                }
            }
            }
            </script>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetchNotifications(); // Initial fetch on load
    setInterval(fetchNotifications, 5000); // Fetch notifications every 5 seconds
});

// Toggle notifications display and hide red dot on click
document.getElementById('notifBell').addEventListener('click', function (event) {
    var notificationDiv = document.getElementById('notificationDiv');
    notificationDiv.style.display = (notificationDiv.style.display === 'none') ? 'block' : 'none';

    // Mark notifications as seen and reset the count
    markNotificationsAsSeen();

    // Prevent propagation
    event.stopPropagation();
});

// Hide the notification popup if clicked outside
document.addEventListener('click', function (event) {
    var notificationDiv = document.getElementById('notificationDiv');
    if (event.target !== notificationDiv && event.target !== document.getElementById('notifBell')) {
        notificationDiv.style.display = 'none';
    }
});

// Fetch notifications and handle red dot visibility
function fetchNotifications() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_notifications.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var notifications = JSON.parse(xhr.responseText);
            var notificationsElement = document.getElementById('notifications');
            notificationsElement.innerHTML = '';

            var notifIndicator = document.getElementById('notifIndicator');
            var seenCount = parseInt(localStorage.getItem('notificationsSeenCount')) || 0;

            if (notifications.length > seenCount) {
                // Calculate new notifications since last seen
                var newNotifications = notifications.length - seenCount;

                // Show the red dot with a count of new notifications
                notifIndicator.style.display = 'block';
                notifIndicator.textContent = newNotifications;
            } else {
                // Hide the red dot if no new notifications
                notifIndicator.style.display = 'none';
            }

            // Display all notifications (read and new)
            notifications.forEach(function (notification) {
                var notificationDiv = document.createElement('div');
                notificationDiv.classList.add('notification-item');

                notificationDiv.onclick = function () {
                    if (notification.source === 'c3_locate') {
                        window.location.href = 'BRGY_Ongoing.php';
                    } else if (notification.source === 'c3_announcement') {
                        window.location.href = 'BRGY_Announcements.php';
                    } else if (notification.source === 'brgy_accept') {
                        window.location.href = 'BRGY_Ongoing.php';
                    } else if (notification.source === 'brgy_decline') {
                        window.location.href = 'BRGY_RAlarm.php';
                    }
                };

                var p = document.createElement('p');
                p.textContent = notification.text;
                notificationDiv.appendChild(p);
                notificationsElement.appendChild(notificationDiv);
            });

            // Always store the total notifications count
            localStorage.setItem('notificationsTotalCount', notifications.length);
        } else {
            console.error('Failed to fetch notifications');
        }
    };
    xhr.send();
}

// Mark notifications as "seen" by updating the seen count
function markNotificationsAsSeen() {
    var totalNotifications = parseInt(localStorage.getItem('notificationsTotalCount')) || 0;
    localStorage.setItem('notificationsSeenCount', totalNotifications); // Mark all notifications as seen
    var notifIndicator = document.getElementById('notifIndicator');
    notifIndicator.style.display = 'none';
    notifIndicator.textContent = ''; // Reset the count
}

// Ensure the red dot is displayed correctly on page load
document.addEventListener('DOMContentLoaded', function () {
    var seenCount = parseInt(localStorage.getItem('notificationsSeenCount')) || 0;
    var totalCount = parseInt(localStorage.getItem('notificationsTotalCount')) || 0;
    var newNotifications = totalCount - seenCount;
    var notifIndicator = document.getElementById('notifIndicator');

    if (newNotifications > 0) {
        notifIndicator.style.display = 'block';
        notifIndicator.textContent = newNotifications;
    }
});
</script>

<script>
    function changeProfile() {
    document.getElementById("fileInput").click(); // Trigger click on the hidden file input
    }

    // Handle file selection change
    document.getElementById("fileInput").addEventListener("change", function(event) { 
    var file = event.target.files[0]; // Get the selected file
    var reader = new FileReader(); // Create a file reader object

    reader.onload = function() {
        document.getElementById("customLogo").src = reader.result; // Update the src attribute of the logo image with the selected image
    };

    if (file) {
        reader.readAsDataURL(file); // Read the selected file as a Data URL
    }
    });
    </script>

<!-- sidebar -->
<nav class="sidebar">
<div class="menu_content">

<!-- REPORT A FIRE -->
<ul class="menu_items">
    <div class="menu_title menu_fire"></div>
    <!-- LOCATE -->
    <li class="item">
        <a href="BRGY_Locate.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Locate.png" class="navlink_image">
            </span>
            <span class="navlink">Locate</span>
        </a>
    </li>
    <!-- REPORTED ALARMS -->
    <li class="item">
        <a href="BRGY_RAlarm.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Barangay_Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Reported Alarm</span>
        </a>
    </li>

<!-- DISPATCH -->
<ul class="menu_items">
    <div class="menu_title menu_dispatch"></div>
    <!-- ONGOING -->
    <li class="item">
        <a href="BRGY_Ongoing.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Ongoing.png" class="navlink_image">
            </span>
            <span class="navlink">Ongoing</span>
        </a>
    </li>
    <!-- COMPLETED -->
    <li class="item">
        <a href="BRGY_Completed.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Completed.png" class="navlink_image">
            </span>
            <span class="navlink">Completed</span>
        </a>
    </li>
    <!-- HELP OUT -->
    <li class="item">
        <a href="BRGY_HelpOut.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/HelpOut.png" style="width:25px; height:25px;" class="navlink_image">
            </span>
            <span class="navlink"> Help Out </span>
        </a>
    </li>
</ul>

<!-- INCIDENT REPORT -->
<ul class="menu_items">
    <div class="menu_title menu_incident"></div>
    <!-- FILE -->
    <li class="item">
        <a href="BRGY_File.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/File.png" class="navlink_image">
            </span>
            <span class="navlink">File</span>
        </a>
    </li>
    <!-- RECORDS -->
    <li class="item">
        <a href="BRGY_Records.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Records.png" class="navlink_image">
            </span>
            <span class="navlink">Records</span>
        </a>
    </li>
</ul>

<!-- MAINTENANCE -->
<ul class="menu_items">
    <div class="menu_title menu_maintenance"></div>
    <!-- FIRE RESPONDERS -->
    <li class="item">
        <a href="BRGY_FResponders.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/firefighter.png" class="navlink_image">
            </span>
            <span class="navlink">Fire Responders</span>
        </a>
    </li>
    <!-- Announcements -->
    <li class="item">
        <a href="BRGY_Announcements.php" class="nav_link active">
            <span class="navlink_icon">
                <img src="images/Announcement.png" class="navlink_image">
            </span>
            <span class="navlink">Announcements</span>
        </a>
    </li>

<!-- OVERVIEW -->
<ul class="menu_items">
    <div class="menu_title menu_data"></div>
    <!-- DATA ANALYTICS -->
    <li class="item">
        <a href="BRGY_DataAnalytics.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Data_Analytics.png" class="navlink_image">
            </span>
            <span class="navlink"> Data Analytics</span>
        </a>
    </li>
    <!-- REPORTS -->
    <li class="item">
        <a href="BRGY_Reports.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Audit Trail</span>
        </a>
    </li>
    
<!-- LOGOUT -->
<div class="bottom_content">
            <div class="bottom collapse_sidebar">
            <span onclick="logout()"> Logout &nbsp; <img src="images/Logout.png" class="logout_icon"></span>
            </div>
        </div>
    </nav>

    <script>
    function logout() {
        if (confirm("Are you sure you want to logout?")) {
            var username = "<?php echo $_SESSION["Username"]; ?>";
            var actionMessage = encodeURIComponent(username) + " just logged out";
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "log_action.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log("Logout action logged successfully.");
                    } else {
                        console.error("Error logging logout action: " + xhr.statusText);
                    }
                    window.location.href = "index.php";
                }
            };
            xhr.send("action=" + actionMessage);
        }
    }
</script>


<!-- Home Section -->
<section class="home-section">
    <h2 class="announcement-heading">
        <img src="images/Announcement.png" alt="Announcement" class="announcement-image"> Announcement
    </h2>

    <?php
    // Establish database connection
    include 'connection.php';


    // Fetch announcements from the database
// Fetch announcements from the database ordered by ID in descending order
$sql = "SELECT Date, Subject, Message, Photo FROM c3_announcement ORDER BY ID DESC";
$result = $conn->query($sql);
    
// Check if there are announcements
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo '<div class="announcement-container">';
        echo "<p>Date: " . $row["Date"] . "</p><br>";
        echo "<h1>" . $row["Subject"] . "</h1>";
        echo '</div>';
        echo '<div class="announcement-container1">';
        echo "<p class='announcement-message'>" . $row["Message"] . "</p>";
        echo '<img class="announcement-photo" src="' . $row["Photo"] . '" alt="Announcement Photo">';
        echo '</div>';
    }
} else {
    echo "<table>";
    echo "<td>";
    echo "No Announcements Available";
    echo "</td>";
    echo "</table>";
}


    $conn->close();
    ?>
</section>



<!-- C3 REPORT ALERT POPUP -->
<div id="c3ReportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="c3ModalTitle">New C3 Report Alert</h2>
            <span class="close1" onclick="closeC3Modal()">✖</span>
        </div> <hr>
        <div id="c3ReportDetails">
            <p><strong>Caller:</strong> <span id="c3ModalCaller">Unknown</span></p>
            <p><strong>Location:</strong> <span id="c3ModalLocation">Unknown</span></p>
            <p><strong>Involve:</strong> <span id="c3ModalInvolve">Unknown</span></p>
            <p><strong>Status:</strong> <span id="c3ModalStatus">Unknown</span></p>
        </div>
        <div class="view-report-container">
            <button onclick="redirectToC3Report()" class="view-report">View Report</button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%;
    overflow: auto; 
    background-color: rgba(0, 0, 0, 0.4); 
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 80%; 
    max-width: 600px; 
    border-radius: 10px; 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
    margin-top: 310px;
}

.fire-truck-popup-header {
  display: flex;
  align-items: center; 
  justify-content: flex-start; 
  margin-bottom: 3px;
}

.alert-icon {
    width: 35px; 
    height: 35px; 
    margin-right: 10px;
    margin-top: 8px;
}

.fire-truck-popup-header h2 {
  margin-top: 20px; 
  font-size: 23px; 
  color: #333; 
  margin-bottom: 5px;
  font-weight: bold;
}

.close1 {
  margin-left: auto; 
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  margin-top: -23px;
}

.close1:hover,
.close1:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

#c3ReportDetails {
    margin-top: 10px;
    margin-bottom: 20px; 
    padding: 8px;
}

#c3ReportDetails p {
    line-height: 2; 
    margin: 0; 
}

#c3ReportDetails strong {
    color: #333; 
}

.view-report-container {
    display: flex;
    justify-content: flex-end; 
    margin-top: 20px; 
}

.view-report {
    background-color: #062B82;
    padding: 10px 20px; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    font-size: 1em; 
    color: white;
    transition: background-color 0.3s;
}

.view-report:hover {
    background-color: #0056b3;
}
</style>




<audio id="alertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>


<script>
var c3PreviousCount = null;
var c3FirstLoad = true;
var lastC3ReportURL = null;

function initializeAudio() {
    var sound = document.getElementById('alertSound');
    sound.volume = 1.0; 
}

function fetchC3ReportCount() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_c3_report_count.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var currentCount = response.count;
            lastC3ReportURL = response.lastReportURL;

            // Check if this is the first load to set up baseline count
            if (c3FirstLoad) {
                c3PreviousCount = currentCount;
                c3FirstLoad = false;
                return;
            }

            // Only open modal if there's a new report
            if (currentCount > c3PreviousCount) {
                var sound = document.getElementById('alertSound');
                sound.currentTime = 0;

                sound.play().then(() => {
                    var latestDetails = response.latestReportDetails;
                    openC3Modal(latestDetails);
                }).catch(error => {
                    console.error("Error playing sound: ", error);
                    alert("Error playing sound: " + error.message);

                    // Update modal even if sound fails to play
                    var latestDetails = response.latestReportDetails;
                    openC3Modal(latestDetails);
                });
                
                // Update previous count after successfully displaying modal
                c3PreviousCount = currentCount;
            }
        }
    };

    xhr.onerror = function() {
        console.error("Request failed.");
    };

    xhr.send();
}

function openC3Modal(details) {
    document.getElementById('c3ModalCaller').innerText = details.caller || 'Unknown';
    document.getElementById('c3ModalLocation').innerText = details.location || 'Unknown';
    document.getElementById('c3ModalInvolve').innerText = details.involve || 'Unknown';
    document.getElementById('c3ModalStatus').innerText = details.status || 'Unknown';
    document.getElementById('c3ReportModal').style.display = "block";
}

function closeC3Modal() {
    document.getElementById('c3ReportModal').style.display = "none";
}

function redirectToC3Report() {
    if (lastC3ReportURL) {
        window.location.href = lastC3ReportURL;
    }
    closeC3Modal();
}

// Run the fetch function every 10 seconds
setInterval(fetchC3ReportCount, 10000);
fetchC3ReportCount(); // Initial call

</script>

<!-- Request Section -->
<a href="C3_Requests.php" class="request-link" style="display:none;">
    <div class="request-container" style="display:none;">
        <div class="request-header">
            <h3>Requests</h3> 
        </div>
        <p class="request-number" id="requestCount">0</p>
        <img src="images/RequestIcon.png" alt="Request Icon" class="request-image">
    </div>
</a>



<!-- REQUEST ALERT POPUP -->
<div id="requestModal" class="modal">
    <div class="modal-content">
        <div class="request-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="requestModalTitle">New Request Alert</h2>
            <span class="close2" onclick="closeRequestModal()"> ✖ </span>
        </div> 
        <hr>
        <div id="requestDetails">
            <p><strong>Responder:</strong> <span id="modalResponder">Unknown</span></p>
            <p><strong>Barangay:</strong> <span id="modalBarangay">Unknown</span></p>
            <p><strong>Request:</strong> <span id="modalRequest">Unknown</span></p>
            <p><strong>Status:</strong> <span id="modalRequestStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToRequest()" class="view-request">View Request</button>
        </div>
    </div>
</div>

<audio id="requestAlertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<style>
    .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-top: 310px;
}

.request-popup-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 3px;
}

.alert-icon {
    width: 35px;
    height: 35px;
    margin-right: 10px;
    margin-top: 8px;
}

.request-popup-header h2 {
    margin-top: 20px;
    font-size: 23px;
    color: #333;
    margin-bottom: 5px;
    font-weight: bold;
}

.close2 {
    margin-left: auto;
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    margin-top: -23px;
}

.close2:hover,
.close2:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

#requestDetails {
    margin-top: 10px;
    margin-bottom: 20px;
    padding: 8px;
}

#requestDetails p {
    line-height: 2;
    margin: 0;
}

#requestDetails strong {
    color: #333;
}

.button-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.view-request {
    background-color: #062B82;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    color: white;
    transition: background-color 0.3s;
}

.view-request:hover {
    background-color: #0056b3;
}
</style>

<script>
    var previousRequestCount = null;
var firstRequestLoad = true;
var lastRequestURL = null; 

function initializeRequestAudio() {
    var sound = document.getElementById('requestAlertSound');
    sound.volume = 1.0; 
}

function fetchRequestCount() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_requestcount.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var currentRequestCount = response.count;
            lastRequestURL = response.lastRequestURL;

            document.getElementById('requestCount').innerText = currentRequestCount;

            if (firstRequestLoad) {
                previousRequestCount = currentRequestCount;
                firstRequestLoad = false;
                return;
            }

            if (currentRequestCount > previousRequestCount) {
                var sound = document.getElementById('requestAlertSound');
                sound.currentTime = 0;
            
                sound.play().then(() => {
                    var latestRequestDetails = response.latestRequestDetails;
            
                    openRequestModal(latestRequestDetails); 
                }).catch(error => {
                    console.error("Error playing sound: ", error);
                    alert("Error playing sound: " + error.message);
                });
            }

            previousRequestCount = currentRequestCount;
        }
    };

    xhr.onerror = function() {
        console.error("Request failed.");
    };

    xhr.send();
}

function openRequestModal(details) {
    document.getElementById('modalResponder').innerText = details.responder;
    document.getElementById('modalBarangay').innerText = details.barangay;
    document.getElementById('modalRequest').innerText = details.request;
    document.getElementById('modalRequestStatus').innerText = details.status;

    // Store the OngoingID globally
    window.currentOngoingID = details.OngoingID;

    document.getElementById('requestModal').style.display = "block";
}
function closeRequestModal() {
    document.getElementById('requestModal').style.display = "none";
}

function redirectToRequest() {
    if (window.currentOngoingID) {
        window.location.href = "BRGY_Request.php?id=" + window.currentOngoingID;
    } else {
        console.error("OngoingID is not defined.");
    }
    closeRequestModal(); 
}

initializeRequestAudio();
setInterval(fetchRequestCount, 10000);
fetchRequestCount();

</script>


</body>
</html>