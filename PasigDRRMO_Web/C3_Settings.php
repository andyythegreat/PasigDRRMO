<?php
session_start();
include 'connection.php';

date_default_timezone_set('Asia/Manila');

$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

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

<?php

$sql = "SELECT Logo FROM c3_logo"; 
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$drromoLogoPath = ''; 

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $drromoLogoPath = $row['Logo']; 
}

$stmt->close();
$conn->close();

$displayDrromoLogo = !empty($drromoLogoPath);

?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <script type="text/javascript">
            window.history.forward()
        </script>
        
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> PCDRRMO | Settings </title>
        <link rel="stylesheet" href="C3_Settings55.css">
        <link rel="shortcut icon" type="image/png" href="<?php echo $drromoLogoPath; ?>">
    </head>
    <body>
        
    <!-- navbar -->
    <nav class="navbar">
        <div class="logo_item">
            <img src="<?php echo htmlspecialchars($drromoLogoPath); ?>" alt=""></i>Pasig City DRRMO
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


<div class="logo-container">
            <?php if ($showLogo): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="User Logo" class="user-logo" onclick="toggleDropdown()">
                <div id="dropdownMenu" class="dropdown-content">
                    <a href="C3_Settings.php"> 
                    <div class="brgy-profile-icon-container">
                    <img src="images/BRGYSettings.png" alt="Settings Icon"  style="width: 23px; height: 23px; margin-right: 15px;" class="brgy-settings-icon"> Settings
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

    <!-- sidebar -->
    <nav class="sidebar">
    <div class="menu_content">

    <!-- DASHBOARD -->
    <div class="menu_dashboard">
    <ul>
        <li>
            <a href="C3_Dashboard.php">Dashboard</a>
        </li>
    </ul>
    </div>


    <!-- REPORT A FIRE -->
    <ul class="menu_items">
        <div class="menu_title menu_fire"></div>
        <!-- LOCATE -->
        <li class="item">
            <a href="C3_Locate.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Locate.png" class="navlink_image">
                </span>
                <span class="navlink">Locate</span>
            </a>
        </li>
        <!-- BARANGAY REPORTS -->
        <li class="item">
            <a href="C3_BReports.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Barangay_Reports.png" class="navlink_image">
                </span>
                <span class="navlink">Barangay Reports</span>
            </a>
        </li>
        <!-- MOBILE REPORTS -->
        <li class="item">
            <a href="C3_MReports.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Cellphone.png" style="width:25px; height:25px;"class="navlink_image">
                </span>
                <span class="navlink">Mobile Reports</span>
            </a>
        </li>
    </ul>

    <!-- DISPATCH -->
    <ul class="menu_items">
        <div class="menu_title menu_dispatch"></div>
        <!-- ONGOING -->
        <li class="item">
            <a href="C3_Ongoing.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Ongoing.png" class="navlink_image">
                </span>
                <span class="navlink">Ongoing</span>
            </a>
        </li>
        <!-- REQUEST -->
        <li class="item">
            <a href="C3_RequestSBar.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/request2.png" class="navlink_image">
                </span>
                <span class="navlink">Request</span>
            </a>
        </li>
        <!-- COMPLETED -->
        <li class="item">
            <a href="C3_Completed.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Completed.png" class="navlink_image">
                </span>
                <span class="navlink">Completed</span>
            </a>
        </li>
        <!-- HELPOUT -->
        <li class="item">
            <a href="C3_HelpOut.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/HelpOut.png" class="navlink_image">
                </span>
                <span class="navlink">Help Out</span>
            </a>
        </li>
    </ul>

    <!-- INCIDENT REPORT -->
    <ul class="menu_items">
        <div class="menu_title menu_incident"></div>
        <!-- FILE -->
        <li class="item">
            <a href="C3_File.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/File.png" class="navlink_image">
                </span>
                <span class="navlink">File</span>
            </a>
        </li>
        <!-- RECORDS -->
        <li class="item">
            <a href="C3_Records.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Records.png" class="navlink_image">
                </span>
                <span class="navlink">Records</span>
            </a>
        </li>
    </ul>

    <!-- INFORMATION -->
    <ul class="menu_items">
        <div class="menu_title menu_maintenance"></div>
        <!-- ANNOUNCEMENT -->
        <li class="item">
            <a href="C3_Announcements.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Announcement.png" class="navlink_image">
                </span>
                <span class="navlink">Announcement</span>
            </a>
        </li>

        <!-- Account -->
        <li class="item">
            <a href="C3_Account.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Account.png" class="navlink_image">
                </span>
                <span class="navlink"> Accounts </span>
            </a>
        </li>

        <!-- Contact Information -->
        <li class="item">
            <a href="C3_ContactInfo.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Contact.png" style="width:25px; height:25px;" class="navlink_image">
                </span>
                <span class="navlink"> Hotlines </span>
            </a>
        </li>

    <!-- ACTIVTY & ANALYTICS -->
    <ul class="menu_items">
        <div class="menu_title menu_data"></div>
        <!-- DATA ANALYTICS -->
        <li class="item">
            <a href="C3_DataAnalytics.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Data_Analytics.png" class="navlink_image">
                </span>
                <span class="navlink"> Data Analytics </span>
            </a>
        </li>

        <!-- AUDIT TRAIL -->
        <li class="item">
        <a href="C3_Reports.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Audit Trail</span>
        </a>
        </li>
        </ul>

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



<!-- Barangay Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="modalTitle">New Barangay Report Alert</h2>
            <span class="close1" onclick="closeModal()">✖</span>
        </div>
        <hr>
        <div id="reportDetails">
            <p><strong>Caller:</strong> <span id="modalCaller">Unknown</span></p>
            <p><strong>Location:</strong> <span id="modalLocation">Unknown</span></p>
            <p><strong>Involve:</strong> <span id="modalInvolve">Unknown</span></p>
            <p><strong>Status:</strong> <span id="modalStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToReport()" class="view-report">View Report</button>
        </div>
    </div>
</div>

<!-- Mobile Report Modal -->
<div id="mobileReportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="mobileModalTitle">New Mobile Report Alert</h2>
            <span class="close1" onclick="closeMobileModal()">✖</span>
        </div>
        <hr>
        <div id="mobileReportDetails">
            <p><strong>Caller:</strong> <span id="mobileModalCaller">Unknown</span></p>
            <p><strong>Location:</strong> <span id="mobileModalLocation">Unknown</span></p>
            <p><strong>Involve:</strong> <span id="mobileModalInvolve">Unknown</span></p>
            <p><strong>Status:</strong> <span id="mobileModalStatus">Unknown</span></p>
        </div>
        <div class="button-container">
            <button onclick="redirectToMobileReport()" class="view-report">View Report</button>
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

#reportDetails {
    margin-top: 10px;
    margin-bottom: 20px; 
    padding: 8px;
}

#reportDetails p {
    line-height: 2; 
    margin: 0; 
}

#reportDetails strong {
    color: #333; 
}

.button-container {
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

button:hover {
    background-color: #0056b3;
}
</style>

        
<!-- Audio for alert sound -->
<audio id="alertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>
<audio id="alertMSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<script>
    var previousCount = null, previousMCount = null;
    var firstLoad = true, firstMLoad = true;
    var lastBarangayReportURL = null, lastMobileReportURL = null;

    function initializeAudio() {
        document.getElementById('alertSound').volume = 1.0;
        document.getElementById('alertMSound').volume = 1.0;
    }

    function fetchReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_report_count.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentCount = response.count;
                lastBarangayReportURL = response.lastReportURL;

                if (firstLoad) {
                    previousCount = currentCount;
                    firstLoad = false;
                    return;
                }

                if (currentCount > previousCount) {
                    var sound = document.getElementById('alertSound');
                    sound.currentTime = 0;
                    sound.play().then(() => {
                        openModal(response.latestReportDetails);
                    }).catch(error => console.error("Error playing sound: ", error));
                }
                previousCount = currentCount;
            }
        };
        xhr.onerror = function() { console.error("Request failed."); };
        xhr.send();
    }

    function fetchMobileReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_Mobilereport_count.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentMCount = response.count;
                lastMobileReportURL = response.lastReportURL;

                if (firstMLoad) {
                    previousMCount = currentMCount;
                    firstMLoad = false;
                    return;
                }

                if (currentMCount > previousMCount) {
                    var sound = document.getElementById('alertMSound');
                    sound.currentTime = 0;
                    sound.play().then(() => {
                        openMobileModal(response.latestReportDetails);
                    }).catch(error => console.error("Error playing sound: ", error));
                }
                previousMCount = currentMCount;
            }
        };
        xhr.onerror = function() { console.error("Request failed."); };
        xhr.send();
    }

    function openModal(details) {
        document.getElementById('modalCaller').innerText = details.caller;
        document.getElementById('modalLocation').innerText = details.location;
        document.getElementById('modalInvolve').innerText = details.involve;
        document.getElementById('modalStatus').innerText = details.status;
        document.getElementById('reportModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('reportModal').style.display = "none";
    }

    function redirectToReport() {
        if (lastBarangayReportURL) window.location.href = lastBarangayReportURL;
        closeModal();
    }

    function openMobileModal(details) {
        document.getElementById('mobileModalCaller').innerText = details.caller;
        document.getElementById('mobileModalLocation').innerText = details.location;
        document.getElementById('mobileModalInvolve').innerText = details.involve;
        document.getElementById('mobileModalStatus').innerText = details.status;
        document.getElementById('mobileReportModal').style.display = "block";
    }

    function closeMobileModal() {
        document.getElementById('mobileReportModal').style.display = "none";
    }

    function redirectToMobileReport() {
        if (lastMobileReportURL) window.location.href = lastMobileReportURL;
        closeMobileModal();
    }

    initializeAudio();
    setInterval(fetchReportCount, 10000);
    setInterval(fetchMobileReportCount, 10000);
    fetchReportCount();
    fetchMobileReportCount();
</script>


<!-- Request Section [COPY PASTE TO!!!!!!!!!!!!!!!!!!!!!!!!!!!111] -->
<a href="C3_Requests.php" class="request-link" style="display:none;">
    <div class="request-container" style="display:none;">
        <div class="request-header">
            <h3>Requests</h3> 
        </div>
        <p class="request-number" id="requestCount">0</p>
        <img src="images/RequestIcon.png" alt="Request Icon" class="request-image">
    </div>
</a>

<!-- HANGGANG DITO LANG!@!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! -->

<!-- REQUEST ALERT POPUP [HANGGANG DITO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1] -->
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
    xhr.open('GET', 'get_request_count.php', true);
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
    document.getElementById('requestModal').style.display = "block";
}

function closeRequestModal() {
    document.getElementById('requestModal').style.display = "none";
}

function redirectToRequest() {
    window.location.href = "C3_RequestSBar.php";
    closeRequestModal(); 
}

initializeRequestAudio();
setInterval(fetchRequestCount, 10000);
fetchRequestCount();

</script>

<!-- END DITOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1 -->

<!-- Home Section -->
<section class="home-section">

<div class="container">
<div class="main-container">

<!-- Maintenance Content Button -->
<div class="maintenance-content-button">
    <a href="C3_Logo.php" class="maintenance-button"> 
        <img src="images/MLogo.png" alt="Logo" class="maintenance-icon2"> 
        <span class="maintenance-button-text2"> Logo </span>
    </a>
    <a href="C3_Background.php" class="maintenance-button" data-target="background-content"> 
        <img src="images/MBackground.png" alt="Background" class="maintenance-icon"> 
        <span class="maintenance-button-text"> Background </span>
    </a>
    <a href="C3_Settings.php" class="maintenance-button">
        <img src="images/status.png" alt="Status" class="maintenance-icon"> 
        <span class="maintenance-button-text">Status</span>
    </a>
    <a href="C3_Involve.php" class="maintenance-button">
        <img src="images/MInvolve.png" alt="Involve" class="maintenance-icon1"> 
        <span class="maintenance-button-text"> Involve </span>
    </a>
</div>
</div>


<?php
include('connection.php');

$message = "";
$error = "";

$archived_sql = "SELECT ID, Name, Color FROM archive_statuscolor ORDER BY ID";
$archived_stmt = $conn->prepare($archived_sql);
$archived_stmt->execute();
$archived_result = $archived_stmt->get_result();

if (isset($_POST['restore_status'])) {
    $restore_id = $_POST['restore_id'];

    $restoreStmt = $conn->prepare("SELECT Name, Color FROM archive_statuscolor WHERE ID = ?");
    $restoreStmt->bind_param("i", $restore_id);
    $restoreStmt->execute();
    $restore_result = $restoreStmt->get_result();

    if ($restore_result->num_rows > 0) {
        $status = $restore_result->fetch_assoc();

        $insertStmt = $conn->prepare("INSERT INTO c3_status (Name, Color) VALUES (?, ?)");
        $insertStmt->bind_param("ss", $status['Name'], $status['Color']);

        if ($insertStmt->execute()) {
            $deleteArchiveStmt = $conn->prepare("DELETE FROM archive_statuscolor WHERE ID = ?");
            $deleteArchiveStmt->bind_param("i", $restore_id);
            $deleteArchiveStmt->execute();

            $message = "Status restored successfully.";
        } else {
            $error = "Error restoring status: " . $insertStmt->error;
        }

        $insertStmt->close();
    } else {
        $error = "Archived status not found.";
    }

    $restoreStmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $color = $_POST['color'];

    if (empty($name)) {
        $error = "Status name is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO c3_status (Name, Color) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $color);

        if ($stmt->execute()) {
            $message = "Status added successfully.";
        } else {
            $error = "Error adding status: " . $stmt->error;
        }

        $stmt->close();
    }
}

if (isset($_POST['archive_status'])) {
    $status_id = $_POST['status_id'];

    $stmt = $conn->prepare("SELECT Name, Color FROM c3_status WHERE id = ?");
    $stmt->bind_param("i", $status_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $status = $result->fetch_assoc();

        $archiveStmt = $conn->prepare("INSERT INTO archive_statuscolor (name, color) VALUES (?, ?)");
        $archiveStmt->bind_param("ss", $status['Name'], $status['Color']);

        if ($archiveStmt->execute()) {
            $deleteStmt = $conn->prepare("DELETE FROM c3_status WHERE id = ?");
            $deleteStmt->bind_param("i", $status_id);
            $deleteStmt->execute();

            $message = "Status archived successfully.";
        } else {
            $error = "Error archiving status: " . $archiveStmt->error;
        }

        $archiveStmt->close();
    } else {
        $error = "Status not found.";
    }

    $stmt->close();
}

if (isset($_POST['update_status_id'])) {
    $update_id = $_POST['update_status_id'];
    $name = trim($_POST['statusName']);
    $color = $_POST['hexColor'];

    if (empty($name)) {
        $error = "Status name is required.";
    } else {
        $updateStmt = $conn->prepare("UPDATE c3_status SET Name = ?, Color = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $name, $color, $update_id);

        if ($updateStmt->execute()) {
            $message = "Status updated successfully.";
        } else {
            $error = "Error updating status: " . $updateStmt->error;  // Capture any error
        }

        $updateStmt->close();
    }
}


$statusQuery = "SELECT id, Name, Color FROM c3_status ORDER BY ID";
$statusResult = $conn->query($statusQuery);

$conn->close();
?>


<!-- Section for Adding Status -->
<div class="add-status-container">
    <h1>Add Status</h1>
    
<div class="form-container">
    <form method="post" action="C3_Settings.php">
 
        <label for="status-name">Name:</label>
        <input type="text" id="status-name" name="name" class="status-name-input" placeholder="Enter status name" required>
        
        <label for="status-color">Color:</label>
        <input type="color" id="status-color" name="color" class="status-color-input">

        <button type="submit" class="add-status-button">Add</button> <br>
    </form>
</div>
    
    <!-- Container for the Archived Button -->
    <div class="left-align-wrapper">
    <div class="fire-trucks-button-container">
        <button class="archived-fire-truck-btn" onclick="archivedOpenPopup()"> Archived </button>
    </div>
    </div>


<!-- Popup for Archived Fire Truck Information -->
<div id="archivedTruckPopup" class="fire-truck-popup" style="display: none;">
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Archived Status</h2>
            <span class="close" onclick="clArchivedPopup()">✖</span>
        </div>
        <hr>

        <div class="archived-popup-container">
            <table class="archived-trucks-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Hex Color</th>
                        <th>Action</th>
                    </tr>
                </thead>
               <tbody>
                    <?php
                    if ($archived_result->num_rows > 0) {
                        while ($row = $archived_result->fetch_assoc()) {
                            $status = htmlspecialchars($row['Name']);
                            $hexColor = htmlspecialchars($row['Color']);
                            
                            echo "<tr>
                                    <td>$status</td>
                                    <td style='background-color: $hexColor;'>$hexColor</td>
                                    <td>
                                        <form method='POST' action='C3_Settings.php' class='center-form'> 
                                            <input type='hidden' name='restore_id' value='" . $row['ID'] . "'> 
                                            <button type='submit' name='restore_status' class='restore-button'>
                                                <img src='images/restore.png' alt='Restore Icon' class='restore-icon'>
                                            </button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No archived statuses available</td></tr>";
                    }

                    $archived_stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function archivedOpenPopup() {
        document.getElementById('archivedTruckPopup').style.display = 'flex';
    }

    function clArchivedPopup() {
        document.getElementById('archivedTruckPopup').style.display = 'none';
    }
</script>

    <!-- Table for Status -->
    <div class="status-container">
    <table class="status-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Hex Color</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($statusResult->num_rows > 0): ?>
                <?php while ($row = $statusResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td style="background-color: <?php echo htmlspecialchars($row['Color']); ?>;"><?php echo htmlspecialchars($row['Color']); ?></td>
                        <td>
                            <form method="post" action="C3_Settings.php" style="display:inline;">
                                <input type="hidden" name="status_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="archive-button" name="archive_status">
                                    <img src="images/archive.png" alt="archive" class="button-icon"> 
                                </button>
                            </form>
                                <button class="update-button" 
                                    onclick="openUpdatePopup('<?php echo htmlspecialchars($row['Name']); ?>', '<?php echo htmlspecialchars($row['Color']); ?>', <?php echo $row['id']; ?>)">
                                    <img src="images/update.png" alt="update" class="button-icon">
                                </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No statuses available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    
<div id="updateTruckPopup" class="fire-truck-popup"> 
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Update Status </h2> 
            <span class="close" onclick="closeUpdatePopup()">✖</span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
            <form class="fire-truck-popup-form" id="updateTruckForm" method="POST" action="C3_Settings.php">
                <input type="hidden" name="update_status_id" id="update_status_id"> 
                <div class="update-form-group">
                    <label for="statusNameUpdate"> Status Name:</label>
                    <input type="text" id="unitNameUpdate" name="statusName" required>
                </div>

                <div class="update-form-group">
                    <label for="hexColorUpdate"> Hex Color:</label>
                    <input type="color" id="hexColorUpdate" name="hexColor" required>
                </div>
                
                <div class="fire-truck-popup-buttons">
                    <button type="submit" class="fire-truck-add">Update</button> 
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function openUpdatePopup(name, color, id) {
    document.getElementById('unitNameUpdate').value = name;
    document.getElementById('hexColorUpdate').value = color; 
    document.getElementById('update_status_id').value = id; 
    document.getElementById('updateTruckPopup').style.display = 'flex';
}

function closeUpdatePopup() {
    document.getElementById('updateTruckPopup').style.display = 'none';
}



function closeUpdatePopup() {
    document.getElementById('updateTruckPopup').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('updateTruckForm').addEventListener('submit', function(event) {

    });
});

</script>
    
    
</div>
</div>

</section>

</body>
</html>