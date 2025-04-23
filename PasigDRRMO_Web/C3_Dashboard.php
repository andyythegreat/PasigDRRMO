<?php
session_start();
include 'connection.php';

date_default_timezone_set('Asia/Manila');

$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

$sql_logo = "SELECT Logo FROM c3_logo WHERE Username = ?";
$stmt_logo = $conn->prepare($sql_logo);
$stmt_logo->bind_param("s", $user);
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();
$logo_row = $result_logo->fetch_assoc();
$logoPath = !empty($logo_row['Logo']) ? $logo_row['Logo'] : "images/PCDRRMO_LOGO1.png";
$faviconPath = !empty($logo_row['Logo']) ? $logo_row['Logo'] : "images/Title.png";
$stmt_logo->close();

$sql_addaccount = "SELECT Logo FROM c3_addaccount WHERE Username = ?";
$stmt_addaccount = $conn->prepare($sql_addaccount);
$stmt_addaccount->bind_param("s", $user);
$stmt_addaccount->execute();
$result_addaccount = $stmt_addaccount->get_result();

if ($row_addaccount = $result_addaccount->fetch_assoc()) {
    $addaccountLogoPath = $row_addaccount['Logo'];
    $showLogo = !empty($addaccountLogoPath);
} else {
    $showLogo = false;
}

$stmt_addaccount->close();


?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <script type="text/javascript">
            window.history.forward()
        </script>
        
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> PCDRRMO | C3 Dashboard </title>
        <link rel="stylesheet" href="C3_Dashboard25.css">
        <link rel="shortcut icon" type="image/png" href="<?php echo $faviconPath; ?>">
    </head>
    <body>

    <!-- navbar -->
    <nav class="navbar">
        <div class="logo_item">
            <img src="<?php echo htmlspecialchars($faviconPath); ?>" alt=""></i>Pasig City DRRMO
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
                <img src="<?php echo htmlspecialchars($addaccountLogoPath); ?>" alt="User Logo" class="user-logo" onclick="toggleDropdown()">
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

<!-- Home Section -->
<section class="home-section">

<!-- Dashboard Section -->
<div class="dashboard-container">
    <img src="images/Dashboard.png" alt="Dashboard Icon" class="dashboard-icon">
    <h1>DASHBOARD</h1>
</div>
<div class="dashboard-container">
    <h2>Real-time monitoring of fire alerts, incidents, and response actions.</h2>
</div>










<!-- Reports Section -->
<div class="reports-section">
    <!-- Ongoing Fire Alerts Section -->
    <div class="fire-alerts-container">
        <div class="fire-alerts-header">
            <h3>Ongoing Fire Alerts</h3>
            <a href="C3_Ongoing.php" class="see-all-btn">See All</a>
        </div>
        <hr>

        <!-- Fire Alerts Information -->
        <div class="fire-alerts-content">
            <?php
            date_default_timezone_set('Asia/Manila');

            $c3_status = [];
            $query = "SELECT Name, Color FROM c3_status";
            $result = $conn->query($query);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $c3_status[$row['Name']] = $row['Color'];
                }
            }

            $sql = "SELECT ID, Date, Caller, Location, Involve, Status FROM c3_locate WHERE Status != 'Resolved' ORDER BY ID DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $foundToday = false;
                
                while($row = $result->fetch_assoc()) {
                    $date = new DateTime($row['Date']);
                    $today = new DateTime();

                    if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
                        $foundToday = true;
                        
                        $status = htmlspecialchars($row['Status']);
                        $backgroundColor = isset($c3_status[$status]) ? $c3_status[$status] : '#ffffff';
                        
                        echo '    <div class="fire-alert-info-container">';
                        echo '        <button class="fire-alert-button" onclick="openPopup(' . htmlspecialchars($row['ID']) . ')">';
                        echo '            <div class="fire-alert-info-grid">';
                        echo '                <div class="fire-alert-info-left">';
                        echo '                    <p><strong>Caller:</strong> ' . htmlspecialchars($row['Caller']) . '</p>';
                        echo '                </div>';
                        echo '                <div class="fire-alert-info-right">';
                        echo '                    <p><strong>Involved:</strong> ' . htmlspecialchars($row['Involve']) . '</p>';
                        echo '                </div>';
                        echo '                <div class="fire-alert-info-left">';
                        echo '                    <p><strong>Location:</strong> ' . htmlspecialchars($row['Location']) . '</p>';
                        echo '                </div>';
                        echo '                <div class="fire-alert-info-right">';
                        echo '                    <p><strong>Status:</strong> <span class="status-label" style="background-color: ' . $backgroundColor . '; color: black; padding: 3px 5px; border-radius: 5px;">' . $status . '</span></p>';
                        echo '                </div>';
                        echo '            </div>';
                        echo '        </button>';
                        echo '    </div>';
                    }
                }

                if (!$foundToday) {
                    echo "<p>No alerts found for today.</p>";            
                }
            } else {
                echo "<p>No alerts found.</p>";
            }
            ?>



<!-- Ongoing Fire Alert Popup -->
<div id="popupModal" class="ongoing-fire-alert-popup">
    <div class="ongoing-fire-alert-content-popup">
        <span class="close" onclick="closePopup()"> ✖ </span>
        <div class="popup-button-container">
            <button id="respondingUnitsBtn" class="ongoing-fire-alert-popup-button active" onclick="showRespondingUnits()">Responding Units</button>
            <button id="requestBtn" class="ongoing-fire-alert-popup-button" onclick="showRequestTable()">Request</button>
        </div>
        <hr>

        <!-- Responding Units Table (No "See All" Button) -->
        <div id="respondingUnitsTable" class="table-container-popup" style="display: block;">
            <table>
                <thead>
                    <tr>
                        <th>Responder</th>
                        <th>Barangay</th>
                        <th>Time Respond</th>
                        <th>Time Arrived</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6">No responding units found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Request Table (With "See All" Button) -->
        <div id="requestTable" class="table-container-popup" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>Responder</th>
                        <th>Barangay</th>
                        <th>Request</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">No requests found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- "See All" Button For Request Table -->
        <div class="see-all-container">
            <a id="seeAllLink" href="#" class="see-all-link">See All</a>
        </div>
    </div>
</div>

<script>
let currentOngoingID;
let refreshInterval;

function openPopup(id) {
    currentOngoingID = id;
    const popupModal = document.getElementById("popupModal");
    if (popupModal) {
        popupModal.style.display = "block";
    }
    fetchRespondingUnits(id);
    fetchRequests(id);

    const seeAllLink = document.getElementById("seeAllLink");
    if (seeAllLink) {
        seeAllLink.href = "C3_Request.php?id=" + id;
    }

    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    refreshInterval = setInterval(() => {
        fetchRespondingUnits(currentOngoingID);
        fetchRequests(currentOngoingID);
    }, 5000);
}

function fetchRespondingUnits(id) {
    const tableBody = document.querySelector("#respondingUnitsTable tbody");
    fetch("fetch_responding_units.php?id=" + id)
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = "";
            if (data.length > 0) {
                data.forEach(row => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                        <td>${row.Responder}</td>
                        <td>${row.Barangay}</td>
                        <td>${row.TimeRespond}</td>
                        <td>${row.TimeArrived}</td>
                        <td>${row.Status}</td>
                    `;
                    tableBody.appendChild(newRow);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="6">No responding units found.</td></tr>';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            tableBody.innerHTML = '<tr><td colspan="6">Error loading data.</td></tr>';
        });
}

function fetchRequests(id) {
    const tableBody = document.querySelector("#requestTable tbody");
    fetch("fetch_requests.php?id=" + id)
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = "";
            if (data.length > 0) {
                data.forEach(row => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                        <td>${row.Responder}</td>
                        <td>${row.Barangay}</td>
                        <td>${row.Request}</td>
                    `;
                    tableBody.appendChild(newRow);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="3">No requests found.</td></tr>';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            tableBody.innerHTML = '<tr><td colspan="3">Error loading data.</td></tr>';
        });
}

function closePopup() {
    const popupModal = document.getElementById("popupModal");
    if (popupModal) {
        popupModal.style.display = "none";
    }
    clearInterval(refreshInterval);
}

function showRespondingUnits() {
    document.getElementById("respondingUnitsTable").style.display = "block";
    document.getElementById("requestTable").style.display = "none";
    fetchRespondingUnits(currentOngoingID);
}

function showRequestTable() {
    document.getElementById("respondingUnitsTable").style.display = "none";
    document.getElementById("requestTable").style.display = "block";
    fetchRequests(currentOngoingID);
}

window.onclick = function(event) {
    const modal = document.getElementById("popupModal");
    if (event.target === modal) {
        modal.style.display = "none";
        clearInterval(refreshInterval);
    }
};
</script>










<!-- IMPORTANT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ongoing-fire-alert-popup-button');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            document.getElementById("respondingUnitsTable").style.display = "none";
            document.getElementById("requestTable").style.display = "none";

            if (this.id === "respondingUnitsBtn") {
                document.getElementById("respondingUnitsTable").style.display = "block";
            } else {
                document.getElementById("requestTable").style.display = "block";
            }
        });
    });
});
</script>

<!-- IMPORTANT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ongoing-fire-alert-popup-button');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            buttons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            document.getElementById("respondingUnitsTable").style.display = "none";
            document.getElementById("requestTable").style.display = "none";
            document.querySelector(".see-all-container").style.display = "none";

            if (this.id === "respondingUnitsBtn") {

                document.getElementById("respondingUnitsTable").style.display = "block";
            } else {
                document.getElementById("requestTable").style.display = "block";
                document.querySelector(".see-all-container").style.display = "block";
            }
        });
    });
});
</script>

</div>
</div>


<!-- Right Side Reports Section -->
<div class="right-reports-container">
<!-- Barangay Number of Reports Section -->
<a href="C3_BReports.php" class="reports-link">
    <div class="barangay-reports-container">
        <div class="barangay-reports-header">
            <h3>Barangay Reports</h3>
        </div>
        <p class="report-number" id="reportCount">0</p>
        <img src="images/Laptop.png" alt="Barangay Image" class="report-image">
    </div>
</a>

<!-- Mobile Number of Reports Section -->
<a href="C3_MReports.php" class="reports-link">
    <div class="mobile-reports-container">
        <div class="mobile-reports-header">
            <h3>Mobile Reports</h3> 
        </div>
        <p class="report-number" id="reportMCount">0</p>
        <img src="images/SmartPhone.png" alt="Mobile Image" class="report-image">
    </div>
</a>

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


<!-- BARANGAY REPORT ALERT POPUP -->
<div id="reportModal" class="modal">
    <div class="modal-content">
    <div class="fire-truck-popup-header">
        <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
        <h2 id="modalTitle">New Barangay Report Alert</h2>
        <span class="close1" onclick="closeModal()"> ✖ </span>
    </div> <hr> 
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



<audio id="alertSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<script>
    var previousCount = null;
    var firstLoad = true;
    var lastBarangayReportURL = null; 

    function initializeAudio() {
        var sound = document.getElementById('alertSound');
        sound.volume = 1.0; 
    }

    function fetchReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_report_count.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentCount = response.count;
                lastBarangayReportURL = response.lastReportURL;

                document.getElementById('reportCount').innerText = currentCount;

                if (firstLoad) {
                    previousCount = currentCount;
                    firstLoad = false;
                    return;
                }

                    if (currentCount > previousCount) {
                        var sound = document.getElementById('alertSound');
                        sound.currentTime = 0;
                    
                        sound.play().then(() => {
                            var latestDetails = response.latestReportDetails;
                    
                            openModal(latestDetails); 
                        }).catch(error => {
                            console.error("Error playing sound: ", error);
                            alert("Error playing sound: " + error.message);
                        });
                    }

                previousCount = currentCount;
            }
        };

        xhr.onerror = function() {
            console.error("Request failed.");
        };

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
    if (lastBarangayReportURL) {
        window.location.href = lastBarangayReportURL;
    }
    closeModal(); 
}

initializeAudio();
setInterval(fetchReportCount, 10000);
fetchReportCount();
</script>

<!-- MOBILE REPORT ALERT POPUP -->
<div id="mobileReportModal" class="modal">
    <div class="modal-content">
        <div class="fire-truck-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-icon">
            <h2 id="mobileModalTitle">New Mobile Report Alert</h2>
            <span class="close1" onclick="closeMobileModal()"> ✖ </span>
        </div> <hr>
        <div id="reportDetails">
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

<audio id="alertMSound" src="https://pasigdrrmo.site/sounds/notif.mp3" preload="auto"></audio>

<script>
    var previousMCount = null;
    var firstMLoad = true;
    var lastMobileReportURL = null; 

    function initializeMobileAudio() {
        var sound = document.getElementById('alertMSound');
        sound.volume = 1.0; 
    }

    function fetchMobileReportCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_Mobilereport_count.php', true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var currentMCount = response.count;
                lastMobileReportURL = response.lastReportURL; 

                document.getElementById('reportMCount').innerText = currentMCount;

                if (firstMLoad) {
                    previousMCount = currentMCount;
                    firstMLoad = false;
                    return;
                }

                if (currentMCount > previousMCount) {
                    var sound = document.getElementById('alertMSound');
                    sound.currentTime = 0; 

                    sound.play().then(() => {
                        var latestDetails = response.latestReportDetails;

                        openMobileModal(latestDetails);
                    }).catch(error => {
                        console.error("Error playing sound: ", error);
                        alert("Error playing sound: " + error.message);
                    });
                }

                previousMCount = currentMCount;
            }
        };

        xhr.onerror = function() {
            console.error("Request failed.");
        };

        xhr.send();
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
        if (lastMobileReportURL) {
            window.location.href = lastMobileReportURL;
        }
        closeMobileModal();
    }

initializeMobileAudio();
setInterval(fetchMobileReportCount, 10000);
fetchMobileReportCount();

</script>


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


  </div>
</div>


<?php
date_default_timezone_set('Asia/Manila');
$today = date("Y-m-d");

// c3_locate
$sqlC3 = "
    SELECT 
        ID, 
        Date, 
        EventLog, 
        Caller, 
        Location, 
        Barangay, 
        Involve, 
        Status, 
        'c3' AS Source
    FROM 
        c3_locate 
    WHERE 
        DATE(Date) = '$today' AND Status != 'Resolved'
";

// brgy_locate
$sqlBrgy = "
    SELECT 
        ID, 
        Date, 
        EventLogB AS EventLog, 
        Caller, 
        Location, 
        Barangay, 
        Involve, 
        Status, 
        'brgy' AS Source
    FROM 
        brgy_locate 
    WHERE 
        DATE(Date) = '$today'
";

// brgy_cancel
$sqlCancel = "
    SELECT 
        ID, 
        Date, 
        EventLog AS EventLog, 
        Caller, 
        Location, 
        Barangay, 
        Involved AS Involve, 
        Status, 
        Reason, 
        'cancel' AS Source
    FROM 
        brgy_cancel 
    WHERE 
        DATE(Date) = '$today'
";

// brgy_helpout
$sqlHelpOut = "
    SELECT 
        ID, 
        Date, 
        Location, 
        Barangay, 
        EventLog, 
        Involve, 
        TypeOfTruck, 
        Status,
        'helpout' AS Source
    FROM 
        brgy_helpout 
    WHERE 
        DATE(Date) = '$today'
";

// brgy_report_update
$sqlReportUpdate = "
    SELECT 
        ID, 
        Date, 
        EventLog, 
        Caller, 
        Location, 
        Barangay, 
        Involve, 
        Status,
        'report_update' AS Source
    FROM 
        brgy_report_update 
    WHERE 
        DATE(Date) = '$today'
";

// mobilelocate
$sqlMobileLocate = "
    SELECT 
        ID, 
        Date, 
        EventLog, 
        Caller, 
        Location, 
        Barangay, 
        Involve, 
        Status,
        'mobile' AS Source
    FROM 
        mobilelocate 
    WHERE 
        DATE(Date) = '$today'
";
$sqlMobileRespond = "
    SELECT 
        mr.ID,
        mr.Username,
        mr.RespondersBarangay,
        mr.Location,
        mr.OngoingID,
        mr.DateForRequest,
        c3.Status,
        c3.Involve,
        'mobile_respond' AS Source
    FROM 
        mobile_respond mr
    LEFT JOIN 
        c3_locate c3
    ON 
        mr.OngoingID = c3.ID
    WHERE 
        mr.RespondStatus = 'Request for Fire Out' 
        AND DATE(mr.DateForRequest) = '$today'
        AND c3.Status != 'Resolved'
";


// Fetch data from mobile_respond if Status is 'Arrived
$sqlMobileRespondArrived = "
    SELECT 
        mr.ID,
        mr.Username,
        mr.RespondersBarangay,
        mr.Location,
        mr.OngoingID,
        mr.TimeArrived AS Date,
        c3.Status,
        c3.Involve,
        'arrived' AS Source
    FROM 
        mobile_respond mr
    LEFT JOIN 
        c3_locate c3
    ON 
        mr.OngoingID = c3.ID
    WHERE 
        mr.RespondStatus = 'Arrived' 
        AND DATE(mr.TimeArrived) = '$today'
        AND c3.Status != 'Resolved'
";


$sqlMobileRespondArriving = "
    SELECT 
        mr.ID,
        mr.Username,
        mr.RespondersBarangay,
        mr.Location,
        mr.OngoingID,
        mr.TimeRespond AS Date,
        c3.Status,
        c3.Involve,
        'arriving' AS Source
    FROM 
        mobile_respond mr
    LEFT JOIN 
        c3_locate c3
    ON 
        mr.OngoingID = c3.ID
    WHERE 
        mr.RespondStatus = 'Arriving' 
        AND DATE(mr.TimeRespond) = '$today'
        AND c3.Status != 'Resolved'
";




$sqlRequest = "
    SELECT 
        c3_request.ID, 
        c3_request.Barangay, 
        c3_request.Responder, 
        c3_request.Request, 
        c3_request.OngoingID,
        c3_request.Date,
        c3_locate.Location,
        'request' AS Source
    FROM 
        c3_request
    LEFT JOIN 
        c3_locate ON c3_request.OngoingID = c3_locate.ID
    WHERE 
        DATE(c3_request.Date) = '$today'
";




$resultC3 = $conn->query($sqlC3);
$resultBrgy = $conn->query($sqlBrgy);
$resultCancel = $conn->query($sqlCancel);
$resultHelpOut = $conn->query($sqlHelpOut);
$resultReportUpdate = $conn->query($sqlReportUpdate);
$resultMobileLocate = $conn->query($sqlMobileLocate);
$resultMobileRespond = $conn->query($sqlMobileRespond);
$resultMobileRespondArrived = $conn->query($sqlMobileRespondArrived);
$resultMobileRespondArriving = $conn->query($sqlMobileRespondArriving);
$resultRequest = $conn->query($sqlRequest);


$fireAlerts = [];

// Fetch results from c3_locate
if ($resultC3->num_rows > 0) {
    while ($row = $resultC3->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from brgy_locate
if ($resultBrgy->num_rows > 0) {
    while ($row = $resultBrgy->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from brgy_cancel
if ($resultCancel->num_rows > 0) {
    while ($row = $resultCancel->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from brgy_helpout
if ($resultHelpOut->num_rows > 0) {
    while ($row = $resultHelpOut->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from brgy_report_update
if ($resultReportUpdate->num_rows > 0) {
    while ($row = $resultReportUpdate->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from mobilelocate
if ($resultMobileLocate->num_rows > 0) {
    while ($row = $resultMobileLocate->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

// Fetch results from mobile_respond for 'Request for Fire Out'
if ($resultMobileRespond->num_rows > 0) {
    while ($row = $resultMobileRespond->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

if ($resultMobileRespondArrived->num_rows > 0) {
    while ($row = $resultMobileRespondArrived->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

if ($resultMobileRespondArriving->num_rows > 0) {
    while ($row = $resultMobileRespondArriving->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}




if ($resultRequest->num_rows > 0) {
    while ($row = $resultRequest->fetch_assoc()) {
        $fireAlerts[] = $row;
    }
}

usort($fireAlerts, function($a, $b) {
    $dateA = isset($a['DateForRequest']) ? strtotime($a['DateForRequest']) : strtotime($a['Date']);
    $dateB = isset($b['DateForRequest']) ? strtotime($b['DateForRequest']) : strtotime($b['Date']);
    
    $timeA = isset($a['TimeArrived']) ? strtotime($a['TimeArrived']) : 0;
    $timeB = isset($b['TimeArrived']) ? strtotime($b['TimeArrived']) : 0;

    if ($dateA != $dateB) {
        return $dateB - $dateA;
    }
    
    return $timeB - $timeA;
});

?>

   

<!-- Container for side by side (Event log and Fire Truck Status) -->
<div class="combined-container">
<!-- Event Log Section -->
<div class="event-log-container">
    <div class="event-log-header">
    <h3>Event Log <span id="fireAlertCount" style="display: none;"><?php echo count($fireAlerts); ?></span></h3>
    </div>
    <hr>
    
<audio id="notifSound" src="https://pasigdrrmo.site/sounds/meow.mp3" preload="auto"></audio>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let previousCount = null;
    const notifSound = document.getElementById('notifSound');

    function fetchFireAlertCount() {
        $.ajax({
            url: 'getFireAlertCount.php',
            method: 'GET',
            success: function(response) {
                const currentCount = parseInt(response, 10);

                if (previousCount !== null && previousCount !== currentCount) {
                    notifSound.play().catch(function(error) {
                        console.log('Playback prevented by browser: ', error);
                    });

                    notifSound.onended = function() {
                        location.reload();
                    };
                }

                $('#fireAlertCount').text(currentCount);
                previousCount = currentCount;
            }
        });
    }

    setInterval(fetchFireAlertCount, 3000);
    fetchFireAlertCount();
});
</script>



<?php
if (empty($fireAlerts)) {
    echo "No fire alerts found for today.";
} else {

}

?>



<!-- Event Log Informations -->
<div class="event-log-content">
    <div class="event-log-info-container">
        <?php foreach ($fireAlerts as $alert): ?>
            <div class="event-log-entry">
                <?php 
                switch ($alert['Source']) {
                    case 'c3':
                        echo '<button class="event-log-button" 
                            onclick="showPopup(\'' . addslashes($alert['EventLog']) . '\', 
                            \'' . addslashes($alert['Caller']) . '\', 
                            \'' . addslashes($alert['Location']) . '\', 
                            \'' . addslashes($alert['Involve']) . '\', 
                            \'' . addslashes($alert['Date']) . '\', 
                            \'' . addslashes($alert['Barangay']) . '\', 
                            \'' . addslashes($alert['Status']) . '\', 
                            \'' . addslashes($alert['Source']) . '\')">
                            <div class="event-log-info">
                                <img src="images/alert.png" alt="Event Log" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                    <span style="font-size: 17px;">' . addslashes($alert['EventLog']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;

                    case 'brgy':
                        echo '<button class="event-log-button" 
                            onclick="showPopup(\'' . addslashes($alert['EventLog']) . '\', 
                            \'' . addslashes($alert['Caller']) . '\', 
                            \'' . addslashes($alert['Location']) . '\', 
                            \'' . addslashes($alert['Involve']) . '\', 
                            \'' . addslashes($alert['Date']) . '\', 
                            \'' . addslashes($alert['Barangay']) . '\', 
                            \'' . addslashes($alert['Status']) . '\', 
                            \'' . addslashes($alert['Source']) . '\')">
                            <div class="event-log-info">
                                <img src="images/report2.png" alt="Event Log" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                    <span style="font-size: 17px;">' . addslashes($alert['Caller']) . " " . addslashes($alert['EventLog']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;

                    case 'cancel':
                        echo '<button class="event-log-button" 
                            onclick="showCancelPopup(\'' . addslashes($alert['EventLog']) . '\', 
                            \'' . addslashes($alert['Caller']) . '\', 
                            \'' . addslashes($alert['Location']) . '\', 
                            \'' . addslashes($alert['Involve']) . '\', 
                            \'' . addslashes($alert['Date']) . '\', 
                            \'' . addslashes($alert['Barangay']) . '\', 
                            \'' . addslashes($alert['Status']) . '\', 
                            \'' . addslashes($alert['Reason']) . '\')">
                            <div class="event-log-info">
                                <img src="images/cancel1.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                    <span style="font-size: 17px;">' . addslashes($alert['Caller']) . ' ' . addslashes($alert['EventLog']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;

                    case 'helpout':
                        echo '<button class="event-log-button" 
                            onclick="showHelpOutPopup(\'' . addslashes(date("Y-m-d H:i:s", strtotime($alert['Date']))) . '\', 
                            \'' . addslashes($alert['Location']) . '\', 
                            \'' . addslashes($alert['Barangay']) . '\', 
                            \'' . addslashes($alert['Involve']) . '\', 
                            \'' . addslashes($alert['TypeOfTruck']) . '\')">
                            <div class="event-log-info">
                                <img src="images/help3.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; margin-top: 10px; width: 60px; height: 50px;">
                                <p>
                                    <span style="font-size: 15px;">' . addslashes($alert['Barangay']) . ' ' . 
                                    addslashes($alert['EventLog']) . ' ' . addslashes($alert['Location']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;

                    case 'report_update':
                        echo '<button class="event-log-button" 
                            onclick="showReportUpdatePopup(\'' . addslashes(date("Y-m-d H:i:s", strtotime($alert['Date']))) . '\', 
                            \'' . addslashes($alert['Location']) . '\', 
                            \'' . addslashes($alert['Barangay']) . '\', 
                            \'' . addslashes($alert['Involve']) . '\', 
                            \'' . addslashes($alert['Status']) . '\',
                            \'' . addslashes($alert['Caller']) . '\')">
                            <div class="event-log-info">
                                <img src="images/update1.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                    <span style="font-size: 17px;">' . addslashes($alert['Barangay']) . ' ' . 
                                    addslashes($alert['EventLog']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;

                    case 'mobile':
                        echo '<button class="event-log-button" 
                            onclick="showMobilePopup(\'' . addslashes($alert['Date']) . '\', 
                                \'' . addslashes($alert['Caller']) . '\', 
                                \'' . addslashes($alert['Location']) . '\', 
                                \'' . addslashes($alert['Barangay']) . '\', 
                                \'' . addslashes($alert['Involve']) . '\', 
                                \'' . addslashes($alert['Status']) . '\')">
                            <div class="event-log-info">
                                <img src="images/mobiler.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                    <span style="font-size: 17px;">' . addslashes($alert['Caller']) . ' ' . addslashes($alert['EventLog']) . '</span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;
                        
                    case 'mobile_respond':
                        echo '<button class="event-log-button" 
                            onclick="showMobileRespondPopup(
                                \'' . addslashes($alert['RespondersBarangay']) . '\', 
                                \'' . addslashes($alert['Username']) . '\', 
                                \'' . addslashes($alert['Location']) . '\', 
                                \'' . addslashes($alert['DateForRequest']) . '\',
                                \'' . addslashes($alert['Status']) . '\',
                                \'' . addslashes($alert['Involve']) . '\')">
                            <div class="event-log-info">
                                <img src="images/mobile_fireout.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                                <span style="font-size: 17px;">
                ' . addslashes($alert['Username']) . ' from ' . addslashes($alert['RespondersBarangay']) . ' 
                is Requesting for FIRE OUT!
            </span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['DateForRequest'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;
                        
                        
                        
                    case 'arrived':
                        echo '<button class="event-log-button" 
                            onclick="showArrivedPopup(
                                \'' . addslashes($alert['RespondersBarangay']) . '\', 
                                \'' . addslashes($alert['Username']) . '\', 
                                \'' . addslashes($alert['Location']) . '\', 
                                \'' . addslashes($alert['Date']) . '\',
                                \'' . addslashes($alert['Status']) . '\',
                                \'' . addslashes($alert['Involve']) . '\')">
                            <div class="event-log-info">
                                <img src="images/location.png" alt="" class="event-log-image" 
                                     style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                <p>
                                                <span style="font-size: 17px;">
                                                    ' . addslashes($alert['Username']) . ' from ' . addslashes($alert['RespondersBarangay']) . ' 
                                                    has arrived at the fire location
                                                </span><br>
                                    <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                </p>
                            </div>
                        </button>';
                        break;
                        
                        
                        case 'arriving':
                            echo '<button class="event-log-button" 
                                onclick="showArrivingPopup(
                                    \'' . addslashes($alert['RespondersBarangay']) . '\', 
                                    \'' . addslashes($alert['Username']) . '\', 
                                    \'' . addslashes($alert['Location']) . '\', 
                                    \'' . addslashes($alert['Date']) . '\',
                                    \'' . addslashes($alert['Status']) . '\',
                                    \'' . addslashes($alert['Involve']) . '\')">
                                <div class="event-log-info">
                                    <img src="images/responding.png" alt="" class="event-log-image" 
                                         style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                    <p>
                                        <span style="font-size: 17px;">
                                            ' . addslashes($alert['Username']) . ' from ' . addslashes($alert['RespondersBarangay']) . ' 
                                            is responding at the fire location
                                        </span><br>
                                        <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                    </p>
                                </div>
                            </button>';
                            break;

                        
                        
                        
                        case 'request':
                            echo '<button class="event-log-button" 
                                onclick="showRequestPopup(
                                    \'' . addslashes($alert['Barangay']) . '\', 
                                    \'' . addslashes($alert['Responder']) . '\', 
                                    \'' . addslashes($alert['Request']) . '\', 
                                    \'' . addslashes($alert['Location']) . '\', 
                                    \'' . addslashes($alert['Date']) . '\')">
                                <div class="event-log-info">
                                    <img src="images/mobile_request.png" alt="" class="event-log-image" 
                                         style="margin-left: 10px; margin-right: 10px; width: 50px; height: auto;">
                                    <p>
                                        <span style="font-size: 17px;">
                                            ' . addslashes($alert['Responder']) . ' from ' . addslashes($alert['Barangay']) . ' 
                                            made a request at the fire location
                                        </span><br>
                                        <span style="color: gray; font-size: 15px;">' . date("Y-m-d H:i:s", strtotime($alert['Date'])) . '</span>
                                    </p>
                                </div>
                            </button>';
                            break;

                    }
                ?>
            </div>
        <?php endforeach; ?>
    </div>





    <!-- POPUP FOR RESPONDING BARANGAY EVENT LOG -->
    <div id="popup" class="barangay-event-log-popup-container" style="display:none;">
        <div class="barangay-event-log-popup-content">
            <span class="close" onclick="BarangayclosePopup()"> ✖ </span>
            <div class="barangay-event-log-popup-header">
                <img src="images/DangerSign.png" alt="Danger Sign" class="barangay-popup-icon">
                <h2> Fire Alert - <strong><span id="popupStatus"></span></strong></h2>
            </div>
            <hr class="barangay-popup-line">
            <div class="barangay-popup-body">
                <p><strong>Date:</strong> <span id="popupTimestamp"></span></p>
                <p><strong>Caller:</strong> <span id="popupCaller"></span></p>
                <p><strong>Location:</strong> <span id="popupLocation"></span></p>
                <p><strong>Barangay:</strong> <span id="popupBarangay"></span></p>
                <iframe id='map' width='100%' height='327' style='border:0' loading='lazy' allowfullscreen src=''></iframe>
                <p><strong>Involved:</strong> <span id="popupInvolve"></span></p>
            </div>
            <div class="barangay-popup-button-container">
                <button class="barangay-popup-close" onclick="BarangayclosePopup()"> OK </button>
                <a href="C3_Ongoing.php" class="barangay-view-link">View</a>
            </div>
        </div>
    </div>
    
    

  <!-- POPUP FOR POSTED A FIRE ALERT -->
    <div id="Popup" class="alert-event-log-popup-container" style="display:none;">
        <div class="alert-event-log-popup-content">
            <span class="close" onclick="AlertclosePopup()"> ✖ </span>
            <div class="alert-event-log-popup-header">
                <img src="images/DangerSign.png" alt="Danger Sign" class="alert-popup-icon">
                <h2> Barangay Report - <strong><span id="fireAlertStatus"></span></strong></h2>
            </div>
            <hr class="alert-popup-line">
            <div class="alert-popup-body">

                <p><strong>Date:</strong> <span id="fireAlertTimestamp"></span></p>
                <p><strong>Caller:</strong> <span id="fireAlertCaller"></span></p>
                <p><strong>Location:</strong> <span id="fireAlertLocation"></span></p>
                <p><strong>Barangay:</strong> <span id="fireAlertBarangay"></span></p>
                <iframe id='mapFireAlert' width='100%' height='327' style='border:0' loading='lazy' allowfullscreen src=''></iframe>
                <p><strong>Involved:</strong> <span id="fireAlertInvolve"></span></p>
            </div>
            <div class="alert-popup-button-container">
                <button class="fire-alert-popup-accept" onclick="acceptAction()"> Accept </button>
                <button class="fire-alert-popup-decline" onclick="declineAction()"> Decline </button>
            </div>
        </div>
    </div>

<!-- POPUP FOR MOBILE LOCATE!!! -->
<div id="showMobilePopUp" class="alert-event-log-popup-container" style="display:none;">
    <div class="alert-event-log-popup-content">
        <span class="close" onclick="MobileclosePopup()"> ✖ </span>
        <div class="alert-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-popup-icon">
            <h2> Mobile Report - <strong><span id="mobileAlertStatus"></span></strong></h2>
        </div>
        <hr class="alert-popup-line">
        <div class="alert-popup-body">
            <p><strong>Date: </strong> <span id="mobileAlertTimestamp"></span></p>
            <p><strong>Caller: </strong> <span id="mobileAlertCaller"></span></p>
            <p><strong>Location: </strong> <span id="mobileAlertLocation"></span></p>
            <p><strong>Barangay: </strong> <span id="mobileAlertBarangay"></span></p>
            <p><strong>Involve: </strong> <span id="mobileAlertInvolve"></span></p>
        </div>
            <div class="alert-popup-button-container">
                <button class="fire-alert-popup-accept" onclick="acceptMobileAction()"> Accept </button>
                <button class="fire-alert-popup-decline" onclick="declineMobileAction()"> Decline </button>
            </div>
    </div>
</div>


 
<!-- POPUP FOR CANCELLATION -->
<div id="cancelPopup" class="alert-event-log-popup-container" style="display:none;">
    <div class="alert-event-log-popup-content">
        <span class="close" onclick="CancelclosePopup()"> ✖ </span>
        <div class="alert-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="alert-popup-icon">
            <h2><strong><span id="cancelAlertStatus"></span></strong></h2>
        </div>
            <hr class="alert-popup-line">
            <div class="alert-popup-body">
            <p><strong>Date:</strong> <span id="cancelAlertTimestamp"></span></p>
            <p><strong>Caller:</strong> <span id="cancelAlertCaller"></span></p>
            <p><strong>Location:</strong> <span id="cancelAlertLocation"></span></p>
            <p><strong>Barangay:</strong> <span id="cancelAlertBarangay"></span></p>
            <p><strong>Involved:</strong> <span id="cancelAlertInvolve"></span></p>
            <p><strong>Reason:</strong> <span id="cancelAlertReason"></span></p>
        </div>
        <div class="alert-popup-button-container">
            <button class="barangay-popup-close" onclick="CancelclosePopup()"> OK </button>
        </div>
    </div>
</div>

<!-- POPUP FOR HELP OUT EVENT LOG -->
<div id="helpOutPopup" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="helpOutClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Help Icon" class="barangay-popup-icon">
            <h2><strong><span id="helpOutPopupStatus"></span></strong></h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Date:</strong> <span id="helpOutPopupDate"></span></p>
            <p><strong>Location:</strong> <span id="helpOutPopupLocation"></span></p>
            
            <p><strong>Barangay:</strong> <span id="helpOutPopupBarangay"></span></p>
            <p><strong>Involved:</strong> <span id="helpOutPopupInvolve"></span></p>
            <p><strong>Type of Truck:</strong> <span id="helpOutPopupTypeOfTruck"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="helpOutClosePopup()"> OK </button>
            <a href="C3_HelpOut.php" class="barangay-view-link">View</a>
        </div>
    </div>
</div>




<!-- POPUP FOR REPORT UPDATE EVENT LOG -->
<div id="reportUpdatePopup" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="reportUpdateClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Report Update Icon" class="barangay-popup-icon">
            <h2><strong id="reportUpdatePopupCaller"></strong> updated a status</h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Date:</strong> <span id="reportUpdatePopupDate"></span></p>
            <p><strong>Location:</strong> <span id="reportUpdatePopupLocation"></span></p>
            <p><strong>Barangay:</strong> <span id="reportUpdatePopupBarangay"></span></p>
            <p><strong>Involved:</strong> <span id="reportUpdatePopupInvolve"></span></p>
            <p><strong>Status:</strong> <span id="reportUpdatePopupStatus"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="reportUpdateClosePopup()"> OK </button>
            <a href="C3_BReports.php" class="barangay-view-link">View</a>
        </div>
    </div>
</div>



<!-- POPUP FOR BARANGAY REQUESTING FOR FIRE OUT -->
<div id="popupRequestingForFireOut" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="FireOutClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="barangay-popup-icon">
            <h2> <strong id="FireOutBarangay"></strong>: Requesting For Fire Out </h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Responder:</strong>  <span id="FireOutUsername"></span> </p>
            <h4 class="fire-details"> Fire Details </h4>
            <p><strong>Location:</strong>  <span id="FireOutLocation"></span> </p>
            <p><strong>Status:</strong> <span id="FireOutStatus"></span></p>
            <p><strong>Involved:</strong> <span id="FireOutInvolve"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="FireOutClosePopup()"> OK </button>
            <a href="C3_Ongoing.php" class="barangay-view-link">View</a>
        </div>
    </div>
</div>


<!-- POPUP FOR BARANGAY ARRIVED-->
<div id="popupArrived" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="ArrivedClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="barangay-popup-icon">
            <h2> <strong id="ArrivedBarangay"></strong>: ARRIVED AT THE FIRE LOCATION!</h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Responder:</strong>  <span id="ArrivedUsername"></span> </p>
            <h4 class="fire-details"> Fire Details </h4>
            <p><strong>Location:</strong>  <span id="ArrivedLocation"></span> </p>
            <p><strong>Status:</strong> <span id="ArrivedStatus"></span></p>
            <p><strong>Involved:</strong> <span id="ArrivedInvolve"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="ArrivedClosePopup()"> OK </button>
            <a href="C3_Ongoing.php" class="barangay-view-link">View</a>
        </div>
    </div>
</div>


<!-- POPUP FOR BARANGAY RESPONDING -->
<div id="popupResponding" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="RespondingClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="barangay-popup-icon">
            <h2> <strong id="RespondingBarangay"></strong>: RESPONDING TO THE FIRE LOCATION!</h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Responder:</strong>  <span id="RespondingUsername"></span> </p>
            <h4 class="fire-details"> Fire Details </h4>
            <p><strong>Location:</strong>  <span id="RespondingLocation"></span> </p>
            <p><strong>Status:</strong> <span id="RespondingStatus"></span></p>
            <p><strong>Involved:</strong> <span id="RespondingInvolve"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="RespondingClosePopup()"> OK </button>
            <a href="C3_Ongoing.php" class="barangay-view-link">View</a>
        </div>
    </div>
</div>


<!-- POPUP FOR BARANGAY REQUEST -->
<div id="popupRequest" class="barangay-event-log-popup-container" style="display:none;">
    <div class="barangay-event-log-popup-content">
        <span class="close" onclick="RequestClosePopup()"> ✖ </span>
        <div class="barangay-event-log-popup-header">
            <img src="images/DangerSign.png" alt="Danger Sign" class="barangay-popup-icon">
            <h2> <strong id="RequestBarangay"></strong>: NEW REQUEST!</h2>
        </div>
        <hr class="barangay-popup-line">
        <div class="barangay-popup-body">
            <p><strong>Responder:</strong>  <span id="RequestResponder"></span> </p>
            <h4 class="fire-details"> Fire Request Details </h4>
            <p><strong>Request:</strong>  <span id="RequestRequest"></span> </p>
            <p><strong>Location:</strong> <span id="RequestLocation"></span></p>
            <p><strong>Date:</strong> <span id="RequestDate"></span></p>
        </div>
        <div class="barangay-popup-button-container">
            <button class="barangay-popup-close" onclick="RequestClosePopup()"> OK </button>
            <a href="#" class="barangay-view-link">View</a>
        </div>
    </div>
</div>



<script>
    function BarangayclosePopup() {
        document.getElementById("popup").style.display = "none";
    }

    function AlertclosePopup() {
        document.getElementById("Popup").style.display = "none"; 
    }
    
    function CancelclosePopup() {
        document.getElementById("cancelPopup").style.display = "none";
    }
    
    function helpOutClosePopup(){
        document.getElementById("helpOutPopup").style.display = "none";
    }
    
    function reportUpdateClosePopup(){
        document.getElementById("reportUpdatePopup").style.display = "none";
    }
    
    function MobileclosePopup(){
        document.getElementById("showMobilePopUp").style.display = "none";
    }
    
    function FireOutClosePopup() {
        document.getElementById("popupRequestingForFireOut").style.display = "none";
    }
    
    function ArrivedClosePopup() {
        document.getElementById("popupArrived").style.display = "none";
    }
    
    function RequestClosePopup() {
        document.getElementById('popupRequest').style.display = 'none';
    }
    
    function RespondingClosePopup() {
    document.getElementById("popupResponding").style.display = "none";
}

    

    
    

    function showPopup(accountName, caller, location, involve, timestamp, barangay, status, source) {
        const encodedLocation = encodeURIComponent(location);

        if (source === "brgy") {
            document.getElementById("fireAlertTimestamp").textContent = timestamp;
            document.getElementById("fireAlertCaller").textContent = caller;
            document.getElementById("fireAlertLocation").textContent = location;
            document.getElementById("fireAlertBarangay").textContent = barangay;
            document.getElementById("fireAlertInvolve").textContent = involve;
            document.getElementById("fireAlertStatus").textContent = status || 'No status';

            
            document.getElementById("mapFireAlert").src = `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3857.6384306655374!2d121.08317061478266!3d14.569443989816512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397dcb5516b78bd%3A0x4d6a793b0c4d1a4c!2s${encodedLocation}!5e0!3m2!1sen!2sus!4v1589793640761!5m2!1sen!2sus`;
            document.getElementById("Popup").style.display = "flex";
        } else {
            document.getElementById("popupTimestamp").textContent = timestamp;
            document.getElementById("popupCaller").textContent = caller;
            document.getElementById("popupLocation").textContent = location;
            document.getElementById("popupBarangay").textContent = barangay;
            document.getElementById("popupInvolve").textContent = involve;
            document.getElementById("popupStatus").textContent = status || 'No status';

            document.getElementById("map").src = `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3857.6384306655374!2d121.08317061478266!3d14.569443989816512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397dcb5516b78bd%3A0x4d6a793b0c4d1a4c!2s${encodedLocation}!5e0!3m2!1sen!2sus!4v1589793640761!5m2!1sen!2sus`;
            document.getElementById("popup").style.display = "flex";
        }
    }
    
        function showCancelPopup(eventLog, caller, location, involve, timestamp, barangay, status, reason) {
            document.getElementById("cancelAlertTimestamp").textContent = timestamp;
            document.getElementById("cancelAlertCaller").textContent = caller;
            document.getElementById("cancelAlertLocation").textContent = location;
            document.getElementById("cancelAlertBarangay").textContent = barangay;
            document.getElementById("cancelAlertInvolve").textContent = involve;
            document.getElementById("cancelAlertReason").textContent = reason;
        
        const statusText = `${barangay}: Fire Alert Cancelled`;
        document.getElementById("cancelAlertStatus").textContent = statusText;

        document.getElementById("cancelPopup").style.display = "flex";
    }
    
        function showHelpOutPopup(date, location, barangay, involve, typeOfTruck, status) {
            document.getElementById("helpOutPopupDate").textContent = date;
            document.getElementById("helpOutPopupLocation").textContent = location;
            document.getElementById("helpOutPopupBarangay").textContent = barangay;
            document.getElementById("helpOutPopupInvolve").textContent = involve;
            document.getElementById("helpOutPopupTypeOfTruck").textContent = typeOfTruck;
            
            const statusText = `Fire Alert: Help Out`;
            document.getElementById("helpOutPopupStatus").textContent = statusText;
        
            document.getElementById("helpOutPopup").style.display = "flex";
    }

        function showReportUpdatePopup(date, location, barangay, involve, status, caller) {
            document.getElementById("reportUpdatePopupDate").textContent = date;
            document.getElementById("reportUpdatePopupLocation").textContent = location;
            document.getElementById("reportUpdatePopupBarangay").textContent = barangay;
            document.getElementById("reportUpdatePopupInvolve").textContent = involve;
            
            document.getElementById("reportUpdatePopupStatus").textContent = status;
        
            document.getElementById("reportUpdatePopupCaller").textContent = caller;
            
            document.getElementById("reportUpdatePopup").style.display = "flex"; 
    }
    
        function showMobilePopup(date, caller, location, barangay, involve, status){
            document.getElementById("mobileAlertTimestamp").textContent = date;
            document.getElementById("mobileAlertStatus").textContent = status || 'No status';
            document.getElementById("mobileAlertCaller").textContent = caller;
            document.getElementById("mobileAlertLocation").textContent = location;
            document.getElementById("mobileAlertBarangay").textContent = barangay;
            document.getElementById("mobileAlertInvolve").textContent = involve;
        
            document.getElementById("showMobilePopUp").style.display = "flex";
    }
    
function showMobileRespondPopup(RespondersBarangay, username, location, dateForRequest, status, involve) {
    document.getElementById("FireOutBarangay").textContent = RespondersBarangay;
    document.getElementById("FireOutUsername").textContent = username;
    document.getElementById("FireOutLocation").textContent = location;
    document.getElementById("FireOutStatus").textContent = status;  // Display Status
    document.getElementById("FireOutInvolve").textContent = involve;  // Display Involve
    
    document.getElementById("popupRequestingForFireOut").style.display = "flex"; 
}

function showArrivedPopup(RespondersBarangay, username, location, timearrived, status, involve) {
    document.getElementById("ArrivedBarangay").textContent = RespondersBarangay;
    document.getElementById("ArrivedUsername").textContent = username;
    document.getElementById("ArrivedLocation").textContent = location;
    document.getElementById("ArrivedStatus").textContent = status;
    document.getElementById("ArrivedInvolve").textContent = involve;
    
    document.getElementById("popupArrived").style.display = "flex"; 
}

function showArrivingPopup(RespondersBarangay, username, location, timerespond, status, involve) {
    document.getElementById("RespondingBarangay").textContent = RespondersBarangay;
    document.getElementById("RespondingUsername").textContent = username;
    document.getElementById("RespondingLocation").textContent = location;
    document.getElementById("RespondingStatus").textContent = status;
    document.getElementById("RespondingInvolve").textContent = involve;

    document.getElementById("popupResponding").style.display = "flex"; 
}


function showRequestPopup(barangay, responder, request, location, date) {
    document.getElementById('RequestBarangay').innerText = barangay;
    document.getElementById('RequestResponder').innerText = responder;
    document.getElementById('RequestRequest').innerText = request;
    document.getElementById('RequestLocation').innerText = location;
    document.getElementById('RequestDate').innerText = date;

    // Show the popup
    document.getElementById('popupRequest').style.display = 'flex';
}








    
function refreshEventLog() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', window.location.href, true);
    xhr.onload = function () {
        if (this.status === 200) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(this.responseText, 'text/html');
            const newLog = doc.querySelector('.event-log-info-container');
            document.querySelector('.event-log-info-container').innerHTML = newLog.innerHTML;
        }
    };
    xhr.send();
}

function acceptAction() {
    const alertData = {
        id: document.getElementById("fireAlertTimestamp").textContent,
        caller: document.getElementById("fireAlertCaller").textContent,
        location: document.getElementById("fireAlertLocation").textContent,
        barangay: document.getElementById("fireAlertBarangay").textContent,
        involve: document.getElementById("fireAlertInvolve").textContent,
        status: document.getElementById("fireAlertStatus").textContent
    };

    fetch('accept_alert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(alertData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        refreshEventLog();
        AlertclosePopup();
    })
    .catch(error => console.error('Error:', error));
}

function declineAction() {
    const alertData = {
        id: document.getElementById("fireAlertTimestamp").textContent,
        caller: document.getElementById("fireAlertCaller").textContent,
        location: document.getElementById("fireAlertLocation").textContent,
        barangay: document.getElementById("fireAlertBarangay").textContent,
        involve: document.getElementById("fireAlertInvolve").textContent,
        status: document.getElementById("fireAlertStatus").textContent
    };

    fetch('decline_alert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(alertData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        refreshEventLog();
        AlertclosePopup();
    })
    .catch(error => console.error('Error:', error));
}






function acceptMobileAction() {
    const alertData = {
        id: document.getElementById("mobileAlertTimestamp").textContent,
        caller: document.getElementById("mobileAlertCaller").textContent,
        location: document.getElementById("mobileAlertLocation").textContent,
        barangay: document.getElementById("mobileAlertBarangay").textContent,
        involve: document.getElementById("mobileAlertInvolve").textContent,
        status: document.getElementById("mobileAlertStatus").textContent
    };

    fetch('accept_mobile_alert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(alertData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
        MobileclosePopup();
    })
    .catch(error => console.error('Error:', error));
}


function declineMobileAction() {
    const alertData = {
        id: document.getElementById("mobileAlertTimestamp").textContent,
        caller: document.getElementById("mobileAlertCaller").textContent,
        location: document.getElementById("mobileAlertLocation").textContent,
        barangay: document.getElementById("mobileAlertBarangay").textContent,
        involve: document.getElementById("mobileAlertInvolve").textContent,
        status: document.getElementById("mobileAlertStatus").textContent
    };

    fetch('decline_mobile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(alertData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
        MobileclosePopup();
    })
    .catch(error => console.error('Error:', error));
}


    

</script>
</div>



</div>



<?php
$query = "
    SELECT 
        Barangay, 
        SUM(CASE WHEN Availability = 'Serviceable' THEN 1 ELSE 0 END) as serviceable_count,
        SUM(CASE WHEN Availability = 'Unserviceable' THEN 1 ELSE 0 END) as unserviceable_count
    FROM brgy_profile
    GROUP BY Barangay
";
$result = mysqli_query($conn, $query);

$barangay_counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $barangay_counts[$row['Barangay']] = [
        'serviceable' => $row['serviceable_count'],
        'unserviceable' => $row['unserviceable_count']
    ];
}
?>

<div class="fire-truck-status-container">
    <div class="fire-truck-status-header">
        <h3>Fire Truck Status</h3>
    </div>
    <hr>

    <div class="fire-truck-status-table-container">
        <table class="fire-truck-status-table">
            <thead>
                <tr>
                    <th>Barangay</th>
                    <th>Serviceable</th>
                    <th>Unserviceable</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($barangay_counts as $barangay => $counts) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($barangay) . "</td>";
                    echo "<td>" . htmlspecialchars($counts['serviceable']) . "</td>";
                    echo "<td>" . htmlspecialchars($counts['unserviceable']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


    </section>

</body>
</html>
