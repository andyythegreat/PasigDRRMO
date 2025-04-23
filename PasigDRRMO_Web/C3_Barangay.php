<?php
session_start();
include 'connection.php';

date_default_timezone_set('Asia/Manila');

include 'connection.php';

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | C3 Barangay </title>
    <link rel="stylesheet" href="C3_Barangay111.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>

<!-- navbar -->
<nav class="navbar">
    <div class="logo_item">
        <img src="images/PCDRRMO_LOGO1.png" alt=""></i>Pasig City DRRMO
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

        <button class="btn" id="notifBell">
            <img src="images/Notif_Bell.png">
            <span id="notifCount" class="notif-count"> 99+ </span>
        </button>
        <?php if ($showLogo): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="User Logo" class="user-logo">
            <?php endif; ?>
    </div>


    <div id="notificationDiv">
        <h2>Notifications</h2>
        <p id="notifications">No notifications yet</p>
    </div>


    <script>
    document.getElementById('notifBell').addEventListener('click', function(event) {
        var notificationDiv = document.getElementById('notificationDiv');
        if (notificationDiv.style.display === 'none') {
            fetchNotifications();
            notificationDiv.style.display = 'block';
        } else {
            notificationDiv.style.display = 'none';
        }
        event.stopPropagation(); 
    });

    document.addEventListener('click', function(event) {
        var notificationDiv = document.getElementById('notificationDiv');
        if (event.target !== notificationDiv && event.target !== document.getElementById('notifBell')) {
            notificationDiv.style.display = 'none';
        }
    });

    function fetchNotifications() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_notifications.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var notifications = JSON.parse(xhr.responseText);
                var notificationsElement = document.getElementById('notifications');
                notificationsElement.innerHTML = '';
                if (notifications.length > 0) {
                    notifications.forEach(function(notification) {
                        var notificationDiv = document.createElement('div'); // Create a div for each notification
                        notificationDiv.classList.add('notification-item'); // Add a class for styling
                        var p = document.createElement('p');
                        p.textContent = notification;
                        notificationDiv.appendChild(p);
                        notificationsElement.appendChild(notificationDiv);
                    });
                } else {
                    notificationsElement.textContent = 'No notifications yet';
                }
            } else {
                console.error('Failed to fetch notifications');
            }
        };
        xhr.send();
    }
</script>

</nav>

<!-- sidebar -->
<nav class="sidebar">
<div class="menu_content">

<!-- REPORT A FIRE -->
<ul class="menu_items">
    <div class="menu_title menu_fire"></div>
    <!-- LOCATE -->
    <li class="item">
        <a href="C3_Locate.php" class="nav_link submenu_item">
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
                <img src="images/Mobile_Reports.png" class="navlink_image">
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
    <!-- COMPLETED -->
    <li class="item">
        <a href="C3_Completed.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Completed.png" class="navlink_image">
            </span>
            <span class="navlink">Completed</span>
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

<!-- MAINTENANCE -->
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

    <!-- BARANGAY -->
    <li class="item">
        <a href="C3_Barangay.php" class="nav_link active">
            <span class="navlink_icon">
                <img src="images/Barangay.png" style="width:25px; height:25px;" class="navlink_image">
            </span>
            <span class="navlink">Barangay</span>
        </a>
    </li>

    <!-- Account -->
    <li class="item">
            <a href="C3_Account.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Account.png" class="navlink_image">
                </span>
                <span class="navlink">Account</span>
            </a>
        </li>

    <!-- Contact Information -->
    <li class="item">
            <a href="C3_ContactInfo.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Contact.png" style="width:25px; height:25px;" class="navlink_image">
                </span>
                <span class="navlink">Contact Info</span>
            </a>
        </li>

<!-- ANALYTICS -->
<ul class="menu_items">
    <div class="menu_title menu_data"></div>
    <!-- DATA ANALYTICS -->
    <li class="item">
        <a href="C3_DataAnalytics.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Data_Analytics.png" class="navlink_image">
            </span>
            <span class="navlink">Analytics</span>
        </a>
    </li>
    <!-- REPORTS -->
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
    <h1> BARANGAY </h1>

    <!-- Table -->
    <div class="table-container">
    <table>
        <thead>
            <tr>
            <th class="id-column">ID</th>
                <th>Barangay</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

        <?php
        // Establish database connection
        include 'connection.php';


        // Fetch barangay data from the database
        $sql = "SELECT ID, Barangay, Last_seen, Status FROM c3_barangay";
        $result = $conn->query($sql);

        // Check if there are barangay accounts
        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td class='id-column'><span>" . $row["ID"] . "</span></td>";
                echo "<td>" . $row["Barangay"] . "</td>";
                echo "<td>" . $row["Status"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No barangay accounts available</td></tr>";
        }

        $conn->close();
        ?>

        </tbody>
    </table>
    </div>
    <style>
    .id-column {
        display: none;
    }
</style>
</section>




</body>
</html>
