<?php
session_start();
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
    <title> PCDRRMO | BRGY Ongoing </title>
    <link rel="stylesheet" href="BRGY_Status80.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>
    
    
<?php
include 'connection.php';

$id = null;
$currentBarangay = $date = $caller = $location = $involve = $status = "";

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $sql = "SELECT Date, Caller, Location, Barangay, Involve, Status FROM c3_locate WHERE ID = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Populate the fields with the retrieved data
        $currentBarangay = $row["Barangay"];
        $currentInvolve = $row["Involve"];

        $date = $row["Date"];
        $caller = $row["Caller"];
        $location = $row["Location"];
        $involve = $row["Involve"];
        $status = $row["Status"];
    } else {
        echo "No data found";
    }
}

// Handle form submission to update status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ID'])) {
    $id = $_POST["ID"];
    $newDate = $_POST["Date"];
    $newCaller = $_POST["Caller"];
    $newLocation = $_POST["Location"];
    $newInvolve = $_POST["Involve"];
    $newStatus = $_POST["Status"];
    $newBarangay = $_POST["Barangay"];
    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];

    // Update the record in the database
    $updateSql = "UPDATE c3_locate SET Date ='$newDate', Caller ='$newCaller', Location = '$newLocation', Barangay = '$newBarangay', Involve = '$newInvolve', Status = '$newStatus' WHERE ID = $id";

    // Insert action into reports table
    $timestamp = date("Y-m-d H:i:s");
    $action = "Updated status";
    $stmt = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $position, $action, $timestamp);

    $success = $stmt->execute();

    if ($conn->query($updateSql) === TRUE) {
        echo "Status updated successfully";
        header("Location: BRGY_Ongoing.php");
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
}

if (isset($_SESSION["Username"]) && isset($_SESSION["Position"])) {
    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];

    $timestamp = date("Y-m-d H:i:s");
    $action = "Viewed status information";
    $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);

    if ($stmt2->execute()) {
        echo "New record inserted successfully";
    } else {
        echo "Error: " . $stmt2->error;
    }

    $stmt2->close();
}
?>


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

    <h2>    
            <?php 
                    $user = $_SESSION['Username'];
                    $user_display = str_replace('BRGY_', 'BARANGAY ', $user);
                    $user_words = explode(' ', $user_display);
                    $second_word = isset($user_words[1]) ? $user_words[1] : '';
                    echo "{$user_words[0]} <span style='color: #062B82;'>{$second_word}</span>";
                ?>
            </h2>
            
        <button class="btn" id="notifBell">
            <img src="images/Notif_Bell.png">
            <span id="notifCount" class="notif-count"> 99+ </span>
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
        <a href="BRGY_Ongoing.php" class="nav_link active">
            <span class="navlink_icon">
                <img src="images/Ongoing.png" class="navlink_image">
            </span>
            <span class="navlink">Ongoing</span>
        </a>
    </li>
    <!-- COMPLETED -->
    <li class="item">
        <a href="BRGY_Completed.php" class="nav_link">
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
        <a href="BRGY_Announcements.php" class="nav_link">
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
        <a href="BRGY_DataAnalytics.php" class="nav_link submenu_item">
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
        <h1> STATUS UPDATE </h1>

    <form method="post" action="BRGY_Status.php?id=<?php echo $id; ?>"> 
        <!-- Correct hidden ID field -->
        <input type="hidden" name="ID" value="<?php echo $id; ?>">
        
    <div class="home-container1">
    <h2>INFORMATION</h2> 
    <div class="x-account">
        <a href="BRGY_Ongoing.php" class="x-link">X</a>
    </div>
    </div> 

        <div class="home-container">

            <label for="caller">Date:</label>
            <input type="datetime-local" id="datetime" name="Date" class="date" value="<?php echo isset($date) ? $date : ''; ?>"> <br>

            <label for="caller">Caller:</label>
            <input type="text" id="caller" name="Caller" class="location" value="<?php echo isset($caller) ? $caller : ''; ?>"> <br>

            <!-- Location input -->
            <label for="location">Location:</label>
            <input type="text" id="location" name="Location" class="location" value="<?php echo isset($location) ? $location : ''; ?>"> <br>

            <?php
            $query = "SELECT DISTINCT Barangay FROM c3_locate WHERE Barangay LIKE 'BRGY_%' ORDER BY Barangay ASC";
            $result = $conn->query($query);
            if (!$result) {
                die("Query failed: " . $conn->error);
            }
            $barangays = [];
            while ($row = $result->fetch_assoc()) {
                $barangays[] = $row['Barangay'];
            }
            ?>
            <div class="form-group">
            <label for="Barangay">Barangay:</label>
            <select id="Barangay" name="Barangay">
                <option value="" selected disabled>Choose Barangay</option>
                <?php foreach ($barangays as $barangay): ?>
                    <option value="<?php echo htmlspecialchars($barangay); ?>" 
                        <?php echo htmlspecialchars($barangay); ?>
                    </option>
                <?php endforeach; ?>
            </select> <br>
            </div>


<?php

$query = "SELECT Involve FROM c3_involve ORDER BY Involve ASC";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$involves = [];
while ($row = $result->fetch_assoc()) {
    $involves[] = $row;
}
?>
<!-- Involve dropdown -->
<label for="involve">Involve:</label>
<select id="involve" class="involve" name="Involve">
    <?php foreach ($involves as $involve): ?>
        <option value="<?php echo htmlspecialchars($involve['Involve']); ?>" <?php if (isset($currentInvolve) && $currentInvolve == $involve['Involve']) echo "selected"; ?>>
            <?php echo htmlspecialchars($involve['Involve']); ?>
        </option>
    <?php endforeach; ?>
</select> <br>

            <!-- Status dropdown -->
            <?php
            $query = "SELECT Name FROM c3_status ORDER BY Name ASC";
            $result = $conn->query($query);
            if (!$result) {
                die("Query failed: " . $conn->error);
            }
            $statuses = [];
            while ($row = $result->fetch_assoc()) {
                $statuses[] = $row['Name'];
            }
            ?>

            <label for="status">Status:</label>
            <select id="status" class="status" name="Status">
                <option value="" selected disabled>Choose Status</option>
                <?php foreach ($statuses as $statusOption): ?>
                    <option value="<?php echo htmlspecialchars($statusOption); ?>" 
                        <?php echo isset($status) && $status === $statusOption ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($statusOption); ?>
                    </option>
                <?php endforeach; ?>
            </select> <br>

        </div>

        <button type="submit">Update</button> 
    </form>
    </body>
</html>