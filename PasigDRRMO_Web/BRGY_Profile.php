<?php
session_start();
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

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
    <title> PCDRRMO | BRGY Profile </title>
    <link rel="stylesheet" href="BRGY_Profile65.css">
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
        <a href="BRGY_Locate.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Locate.png" class="navlink_image">
            </span>
            <span class="navlink">Locate</span>
        </a>
    </li>
    <!-- REPORTED ALARM -->
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

<section class="home-section">
    <h1> ACCOUNT INFORMATION </h1>


    <!-- Section For Fire Trucks, Responders, and Drivers -->
    <div class="profile-services-container">

    <!-- BRGY PROFILE Section -->
    
<?php
$sql = "SELECT Address, Logo FROM c3_addaccount WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$address = $row['Address'] ?? 'Address not available';
$logoPath = $row['Logo'] ?? '';
$showLogo = !empty($logoPath);

$stmt->close();
?>

    <div class="barangay-profile">
        <div class="image-details">
        <div class="barangay-image">
            <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Barangay Image" />
        </div>
        <div class="barangay-details">
            <h2><?php echo $user; ?></h2>
        <p><strong>ADDRESS:</strong> <?php echo htmlspecialchars($address); ?></p>

        </div>
        </div>
        <div class="profile-button"> 
        <button class="small-button" onclick="openProfilePopup()">
            <img src="images/BRGYProfile.png" alt="Profile" class="button-image-profile" />
        </button>
        </div>
    </div>

<!-- Popup for Editing Profile Information -->



<div id="profilePopup" class="profile-popup">
    <div class="profile-popup-content">
        <div class="profile-popup-header">
            <h2>Edit Profile</h2>
            <span class="close" onclick="closeProfilePopup()">✖</span>
        </div>
        <hr>
        <div class="profile-popup-container">   
            <form class="profile-popup-form" action="update_profile.php" method="POST" enctype="multipart/form-data">

                <label for="logo">Logo:</label>
                <input type="file" id="logo" name="logo"> 

                <div id="logo-preview-container" style="width: 140px; height: 140px; border-radius: 50%; background-color: #0056b3; display: flex; align-items: center; justify-content: center; margin-top: 10px; color: white; font-size: 170px; text-align: center;">
                <img id="logo-preview" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;" alt=""> 
                <span id="default-text" style="font-size: 120px;">+</span>
                </div> 
                
                <script>
                    document.getElementById('logo').addEventListener('change', function(event) {
                        const file = event.target.files[0];
                        const preview = document.getElementById('logo-preview');
                        const defaultText = document.getElementById('default-text');

                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                                defaultText.style.display = 'none';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.src = '';
                            preview.style.display = 'none';
                            defaultText.style.display = 'block';
                        }
                    });

                    // Set the initial logo preview if a logo exists
                    const initialLogoPath = "<?php echo htmlspecialchars($logoPath); ?>";
                    if (initialLogoPath) {
                        document.getElementById('logo-preview').src = initialLogoPath;
                        document.getElementById('logo-preview').style.display = 'block';
                        document.getElementById('default-text').style.display = 'none';
                    }
                </script>

                

                <label for="barangayName">Barangay Name:</label>
                <input type="text" id="barangayName" name="barangayName" value="<?php echo htmlspecialchars($user); ?>" placeholder="Enter Barangay" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" placeholder="Enter Address" required>
                
                
                    <div class="profile-popup-buttons">
                        <button type="submit" class="profile-update">Update</button>
                    </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function to open the popup
function openProfilePopup() {
    document.getElementById('profilePopup').style.display = 'flex';
}

// Function to close the popup
function closeProfilePopup() {
    document.getElementById('profilePopup').style.display = 'none';
}

// Close the profile popup when clicking outside the popup content area
window.addEventListener('click', function(event) {
    const profilePopup = document.getElementById('profilePopup');
    const profilePopupContent = document.querySelector('.profile-popup-content');

    // Check if the click is outside the popup content
    if (event.target === profilePopup && !profilePopupContent.contains(event.target)) {
        closeProfilePopup();
    }
});
</script>

    <!-- Fire Trucks Section -->
    
    <?php
    $sql = "SELECT COUNT(*) as total_trucks FROM brgy_profile WHERE Barangay = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalTrucks = $row['total_trucks'];
    
    ?>
    <div class="Services-link">
        <div class="Services-container">
            <div class="Services-header">
                <h3> Fire Trucks </h3> 
            </div>
        <p class="Services-number"> <?php echo $totalTrucks; ?> </p> 
        </div>
    </div>
    
    <!-- Team Leader Section -->
    
    <?php
    $sql = "SELECT COUNT(*) as responderCount FROM firerespondersaccount WHERE Barangay = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $responderCount = $row['responderCount'];
    ?>
    <div class="Services-link">
        <div class="Services-container">
            <div class="Services-header">
                <h3> Team Leader </h3> 
            </div>
        <p class="Services-number"> <?php echo $responderCount; ?> </p>
        </div>
    </div>

  </div>







<!-- Fire Trucks Section -->
<div class="fire-truck-container">
    <div class="fire-truck-header">
        <h3>Fire Trucks</h3>
    <div class="fire-trucks-button-container">
        <button class="archived-fire-truck-btn" onclick="archivedOpenPopup()"> Archived </button>
        <button class="fire-truck-btn" onclick="openPopup()">Add Fire Truck</button>
    </div>
    </div>
    <hr>
    
    
<?php


 if (isset($_GET['restore_id'])) {
            $restore_id = $_GET['restore_id'];

            
    $restore_sql = "INSERT INTO brgy_profile (ID, Barangay, Photo, UnitName, PlateNumber, TypeOfTruck, Status, Availability) SELECT ID, Barangay, Photo, UnitName, PlateNumber, TypeOfTruck, Status, Availability FROM archive_truck WHERE ID = $restore_id";
            
        
            if ($conn->query($restore_sql) === TRUE) {
                $delete_sql = "DELETE FROM archive_truck WHERE ID = $restore_id";
                if ($conn->query($delete_sql) === TRUE) {
                    echo "<script>alert('Record restored successfully!');</script>";
                } else {
                    echo "Error deleting record: " . $conn->error;
                }
            } else {
                echo "Error restoring record: " . $conn->error;
            }
        }

$archived_sql = "SELECT * FROM archive_truck WHERE Barangay = ?";
$archived_stmt = $conn->prepare($archived_sql);
$archived_stmt->bind_param("s", $user);
$archived_stmt->execute();
$archived_result = $archived_stmt->get_result();
?>


<!-- Popup for Archived Fire Truck Information -->
<div id="archivedTruckPopup" class="fire-truck-popup" style="display: none;">
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Archived Truck Information</h2>
            <span class="close" onclick="closeArchivedPopup()">✖</span>
        </div>
        <hr>

        <div class="archived-popup-container">
            <table class="archived-trucks-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Unit Name</th>
                        <th>Plate Number</th>
                        <th>Type of Truck</th>
                        <th>Status</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($archived_result->num_rows > 0) {
                        while ($row = $archived_result->fetch_assoc()) {
                            $photoPath = htmlspecialchars($row['Photo']);
                            $unitName = htmlspecialchars($row['UnitName']);
                            $plateNumber = htmlspecialchars($row['PlateNumber']);
                            $truckType = htmlspecialchars($row['TypeOfTruck']);
                            $availability = htmlspecialchars($row['Availability']);
                            $status = htmlspecialchars($row['Status']);

                           echo "<tr>
                            <td><img src='$photoPath' alt='Fire Truck Photo' class='fire-truck-images' width='50'></td>
                            <td>$unitName</td>
                            <td>$plateNumber</td>
                            <td>$truckType</td>
                            <td>$status</td>
                            <td>$availability</td>
                                    <td>
                                        <button class='restore-button'><a href='BRGY_Profile.php?restore_id=" . $row["ID"] . "' class='restore' type='submit'>
                                            <img src='images/restore.png' alt='Restore Icon' class='restore-icon'>
                                        </button>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No archived trucks available</td></tr>";
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

    function closeArchivedPopup() {
         document.getElementById('archivedTruckPopup').style.display = 'none';
    }
 </script>



<!-- Popup for Editing Profile Information -->
<div id="profilePopup" class="profile-popup">
    <div class="profile-popup-content">
        <div class="profile-popup-header">
            <h2>Edit Profile</h2>
            <span class="close" onclick="closeProfilePopup()"> ✖ </span>
        </div>
        <hr>
        <div class="profile-popup-container">
            <form class="profile-popup-form">
                <label for="barangayName">Barangay Name:</label>
                <input type="text" id="barangayName" name="barangayName" placeholder="Enter Barangay Name" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" placeholder="Enter Address" required>
            </form>
        </div>
        <div class="profile-popup-buttons">
            <button type="submit" class="profile-update">Update</button>
        </div>
    </div>
</div>



<script>
function openProfilePopup() {
    document.getElementById('profilePopup').style.display = 'flex';
}

function closeProfilePopup() {
    document.getElementById('profilePopup').style.display = 'none';
}

window.onclick = function(event) {
    const profilePopup = document.getElementById('profilePopup');
    const profilePopupContent = document.querySelector('.profile-popup-content');

    if (event.target === profilePopup && !profilePopupContent.contains(event.target)) {
        closeProfilePopup();
    }
}
</script>

<!-- Popup for Adding Fire Truck Information -->

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $barangay = $_SESSION['Username'];
    $unitName = $_POST['unitName'];
    $plateNumber = $_POST['plateNumber'];
    $truckType = $_POST['truckType'];

    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileSize = $_FILES['photo']['size'];
        $fileType = $_FILES['photo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = array('jpg', 'jpeg', 'png');
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $photoPath = $dest_path;
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type.";
        }
    } else {
        echo "Error uploading file: " . $_FILES['photo']['error'];
    }

    $sql = "INSERT INTO brgy_profile (Barangay, Photo, UnitName, PlateNumber, TypeOfTruck)
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $barangay, $photoPath, $unitName, $plateNumber, $truckType);

    if ($stmt->execute()) {
        echo "<script>alert('Truck information added successfully!'); window.location.href='BRGY_Profile.php';</script>";
    } else {
        echo "Error executing query: " . $stmt->error;
    }
}
?>



<div id="fireTruckPopup" class="fire-truck-popup">
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Add Truck Information</h2>
            <span class="close" onclick="closePopup()"> ✖ </span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
<form class="fire-truck-popup-form" action="BRGY_Profile.php" method="POST" enctype="multipart/form-data">
    <label for="barangay">Barangay:</label>
    <p id="barangay"><?php echo htmlspecialchars($user); ?></p>
            
    <label for="photo">Photo:</label>
    <input type="file" id="photo" name="photo" required>

        <div id="photo-preview-container" style="width: 140px; height: 140px; border-radius: 50%; background-color: #0056b3; display: flex; align-items: center; justify-content: center; margin-top: 10px; color: white; font-size: 170px; text-align: center;">
            <img id="photo-preview" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;" alt="Photo Preview"> 
        <span id="photo-default-text" style="font-size: 120px;">+</span>
        </div> 

        <script>
            document.getElementById('photo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('photo-preview');
            const defaultText = document.getElementById('photo-default-text');

            if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result; 
                preview.style.display = 'block'; 
                defaultText.style.display = 'none'; 
            };
            reader.readAsDataURL(file); 
            } else {
            preview.src = ''; 
            preview.style.display = 'none'; 
            defaultText.style.display = 'block'; 
            }
        });
        </script>

            <label for="unitName">Unit Name:</label>
            <input type="text" id="unitName" name="unitName" placeholder="Enter unit name" required>

            <label for="plateNumber">Plate Number:</label>
            <input type="text" id="plateNumber" name="plateNumber" placeholder="Enter plate number" required>

            <label for="truckType">Type of Truck:</label>
            <select id="truckType" name="truckType" required>
                <option value="Tanker">Tanker</option>
                <option value="Mini Tanker">Mini Tanker</option>
                <option value="Engine">Engine</option>
                <option value="Pumper">Pumper</option>
            </select>
        </div>

            <div class="fire-truck-popup-buttons">
                <button type="submit" class="fire-truck-add">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPopup() {
    document.getElementById('fireTruckPopup').style.display = 'flex';
}

function closePopup() {
    document.getElementById('fireTruckPopup').style.display = 'none';
}
window.onclick = function(event) {
    const popup = document.getElementById('fireTruckPopup');
    const popupContent = document.querySelector('.fire-truck-popup-content');
    
    if (event.target === popup && !popupContent.contains(event.target)) {
        closePopup();
    }
}
</script>


<?php

if (isset($_GET['archive_id'])) {
    $archive_id = $_GET['archive_id'];

    $user = $_SESSION["Username"];
    
    $archive_sql = "INSERT INTO archive_truck SELECT ID, Barangay, Photo, UnitName, PlateNumber, TypeOfTruck, Status, Availability FROM brgy_profile WHERE ID = ?";
    $stmt = $conn->prepare($archive_sql);
    $stmt->bind_param("i", $archive_id);
    if ($stmt->execute()) {
        $delete_sql = "DELETE FROM brgy_profile WHERE ID = ?";
        $stmt_delete = $conn->prepare($delete_sql);
        $stmt_delete->bind_param("i", $archive_id);
        if ($stmt_delete->execute()) {
            echo "<script>alert('Fire Truck archived successfully!');</script>";
        } else {
            echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error archiving record: " . $conn->error . "');</script>";
    }
}

$sql = "
    SELECT 
        bp.ID, 
        bp.Photo, 
        bp.UnitName, 
        bp.PlateNumber, 
        bp.TypeOfTruck, 
        bp.Status AS DefaultStatus, 
        bp.Availability,
        CASE 
            WHEN mr.TruckID IS NOT NULL THEN 'Responding' 
            ELSE bp.Status 
        END AS FinalStatus
    FROM brgy_profile bp
    LEFT JOIN mobile_respond mr ON bp.ID = mr.TruckID
    WHERE bp.Barangay = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Fire Truck Table Information Section -->
<div class="fire-truck-status-table-container">
    <table class="fire-truck-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Unit Name</th>
                <th>Plate Number</th>
                <th>Type of Truck</th>
                <th>Availability</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photoPath = htmlspecialchars($row['Photo']);
                    $unitName = htmlspecialchars($row['UnitName']);
                    $plateNumber = htmlspecialchars($row['PlateNumber']);
                    $truckType = htmlspecialchars($row['TypeOfTruck']);
                    $availability = htmlspecialchars($row['Availability']);                    
                    $status = htmlspecialchars($row['FinalStatus']); // Use FinalStatus

                    $truckId = htmlspecialchars($row['ID']);

                    echo "<tr>
                            <td><img src='$photoPath' alt='Fire Truck Photo' class='fire-truck-images' width='50'></td>
                            <td>$unitName</td>
                            <td>$plateNumber</td>
                            <td>$truckType</td>
                            <td>$availability</td>
                            <td>$status</td>
                            <td class='action-buttons'>
                                <button class='update-button' onclick='openUpdatePopup(\"$photoPath\", \"$unitName\", \"$plateNumber\", \"$truckType\", \"$truckId\", \"$availability\")'>
                                    <img src='images/update.png' alt='Update' id='update-button'>
                                </button>

                                <button class='message-button' onclick='openMessagePopup(\"$unitName\")'>
                                    <img src='images/message.png' alt='Message'> 
                                </button>
                                <button class='archive-button'>
                                    <a href='BRGY_Profile.php?archive_id=$truckId' class='archive-button'>
                                        <img src='images/archive.png' alt='Archive'> 
                                    </a>
                                </button>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No trucks available</td></tr>";
            }
            $stmt->close();
            ?>
        </tbody>
    </table>
</div>

<!-- Popup for Updating Fire Truck Information -->
<div id="updateTruckPopup" class="fire-truck-popup"> 
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Update Truck Information</h2> 
            <span class="close" onclick="closeUpdatePopup()">✖</span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
            <form class="fire-truck-popup-form" action="update_truck.php" id="updateTruckForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="truckId" id="truckIdUpdate">
                <div class="update-form-group">
                    <label for="updatePhotos">Photo:</label>
                    <input type="file" id="updatePhotos" name="photos">  
                </div>

                <div id="update-photo-preview-container" style="width: 140px; height: 140px; border-radius: 50%; background-color: #0056b3; display: flex; align-items: center; justify-content: center; margin-top: 10px; color: white; font-size: 170px; text-align: center;">
                    <img id="update-photos-preview" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;" alt="Photo Preview">  
                    <span id="update-photos-default-text" style="font-size: 120px;">+</span> 
                </div> 

                <script>
                    document.getElementById('updatePhotos').addEventListener('change', function(event) { 
                        const file = event.target.files[0];
                        const preview = document.getElementById('update-photos-preview'); 
                        const defaultText = document.getElementById('update-photos-default-text'); 

                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result; 
                                preview.style.display = 'block'; 
                                defaultText.style.display = 'none'; 
                            };
                            reader.readAsDataURL(file); 
                        } else {
                            preview.src = ''; 
                            preview.style.display = 'none'; 
                            defaultText.style.display = 'block'; 
                        }
                    });
                </script>

                <div class="update-form-group">
                    <label for="unitNameUpdate">Unit Name:</label>
                    <input type="text" id="unitNameUpdate" name="unitName" placeholder="Enter unit name" required>
                </div>

                <div class="update-form-group">
                    <label for="plateNumberUpdate">Plate Number:</label>
                    <input type="text" id="plateNumberUpdate" name="plateNumber" placeholder="Enter plate number" required>
                </div>

                <div class="update-form-group">
                    <label for="truckTypeUpdate">Type of Truck:</label>
                    <select id="truckTypeUpdate" name="truckType" required>
                        <option value="" disabled selected>Choose Fire Truck</option>
                        <option value="Tanker">Tanker</option>
                        <option value="Mini Tanker">Mini Tanker</option>
                        <option value="Engine">Engine</option>
                        <option value="Pumper">Pumper</option>
                    </select>
                </div>
                    <label for="unitNameUpdate"> Availability:</label>
                    <div class="message-form-group">
                        <div class="radio-container">
                            <input type="radio" id="Serviceable" name="availability" value="Serviceable">
                            <label for="Serviceable"> Serviceable </label>
                        </div>
                    </div>
                    
                    <div class="message-form-group">
                        <div class="radio-container">
                            <input type="radio" id="Unserviceable" name="availability" value="Unserviceable">
                            <label for="Unserviceable"> Unserviceable </label>
                        </div>
                    </div>
                    
                                    
                <div class="fire-truck-popup-buttons">
                    <button type="submit" class="fire-truck-add">Update</button> 
                </div>
            </form>
        </div>
    </div>
</div>

<script>


function closeUpdatePopup() {
    document.getElementById('updateTruckPopup').style.display = 'none';
}

function openUpdatePopup(photo, unitName, plateNumber, truckType, truckId, availability) {
    document.getElementById('updateTruckPopup').style.display = 'flex';

    document.getElementById('update-photos-preview').src = photo;
    document.getElementById('update-photos-preview').style.display = 'block';
    document.getElementById('update-photos-default-text').style.display = 'none';

    document.getElementById('unitNameUpdate').value = unitName;
    document.getElementById('plateNumberUpdate').value = plateNumber;
    document.getElementById('truckTypeUpdate').value = truckType;
    document.getElementById('truckIdUpdate').value = truckId;

    if (availability === "Serviceable") {
        document.getElementById("Serviceable").checked = true;
        document.getElementById("Unserviceable").checked = false;
    } else if (availability === "Unserviceable") {
        document.getElementById("Unserviceable").checked = true;
        document.getElementById("Serviceable").checked = false;
    }
}




document.querySelector('.close').addEventListener('click', closeUpdatePopup);



document.getElementById('updateTruckForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('update_truck.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('Error')) {
            alert('Error updating truck: ' + data);
        } else {
            alert('Truck information updated!');
            closeUpdatePopup(); 
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error updating truck:', error);
    });
});
</script>
            

<!-- Popup for Deployment Note -->
<div id="messagePopup" class="fire-truck-popup"> 
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2> Deployment Note</h2> 
            <span class="close" onclick="closeMessagePopup()">✖</span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
            <form class="fire-truck-popup-form" id="messageForm">
                <div class="message-form-group">
                    <label for="unitname">Unit Name:</label>
                    <input type="text" id="unitname" name="unitname" value=" " readonly>
                </div>

                <div class="message-form-group">
                    <div class="radio-container">
                        <input type="radio" id="refueling" name="activity" value="Refueling">
                        <label for="refueling">Refueling</label>
                    </div>
                </div>

                <div class="message-form-group">
                    <div class="radio-container">
                        <input type="radio" id="tire-inflation" name="activity" value="Tire Inflation">
                        <label for="tire-inflation">Tire Inflation</label>
                    </div>
                </div>

                <div class="message-form-group">
                    <div class="radio-container">
                        <input type="radio" id="maintenance" name="activity" value="Maintenance">
                        <label for="maintenance">Maintenance</label>
                    </div>
                </div>

                <!-- Add Standby Option -->
                <div class="message-form-group">
                    <div class="radio-container">
                        <input type="radio" id="standby" name="activity" value="Standby">
                        <label for="standby">Standby</label>
                    </div>
                </div>

                <div class="message-form-group">
                    <div class="radio-container">
                        <input type="radio" id="others" name="activity" value="Others">
                        <label for="others"> Others</label>
                    </div>
                </div>
                
                <div class="message-form-group1">
                    <input type="text" id="otherActivity" name="otherActivity" placeholder="Please specify..." style="display: none;">  <!-- Hidden by default -->
                </div>

            </div>

            <div class="fire-truck-popup-buttons">
                <button type="submit" class="fire-truck-add"> Deploy </button> 
            </div>
            </form>
        </div>
    </div>
</div>


<script>
function toggleOtherActivityInput() {
    const otherActivityInput = document.getElementById('otherActivity');
    const otherRadioButton = document.getElementById('others');

    if (otherRadioButton.checked) {
        otherActivityInput.style.display = 'block';  
        otherActivityInput.disabled = false;         
    } else {
        otherActivityInput.style.display = 'none';  
        otherActivityInput.disabled = true;         
        otherActivityInput.value = '';              
    }
}

const radioButtons = document.querySelectorAll('input[name="activity"]');
radioButtons.forEach(radio => {
    radio.addEventListener('change', toggleOtherActivityInput);
});

toggleOtherActivityInput();


</script>

<script>
function openMessagePopup(unitName) {
    document.getElementById('unitname').value = unitName;
    document.getElementById('messagePopup').style.display = 'flex';
}


function closeMessagePopup() {
    document.getElementById('messagePopup').style.display = 'none';
}

window.onclick = function(event) {
    const messagePopup = document.getElementById('messagePopup');
    if (event.target === messagePopup) {
        closeMessagePopup();
    }
}

document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const unitName = document.getElementById('unitname').value;
    const activity = document.querySelector('input[name="activity"]:checked').value;
    const otherActivity = document.getElementById('otherActivity').value;

    const selectedActivity = (activity === 'Others' && otherActivity.trim() !== '') ? otherActivity : activity;

    fetch('update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `unitName=${unitName}&status=${encodeURIComponent(selectedActivity)}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        closeMessagePopup();
        location.reload();
    })
    .catch(error => console.error('Error:', error));
});


</script>

</div>
</div>


   
<!-- Fire Responders Volunteer Section -->
<div class="event-log-container">
    <div class="event-log-header">
        <h3> Officers In Charged </h3>
        <a href="BRGY_RespondersAccount.php" class="see-all-btn"> Add Responders </a>
    </div>
    <hr>

<!-- Responders and drivers -->
<div class="event-log-content">
                <?php
                
                // Updated SQL query to select only Name
                $sql = "SELECT Name FROM firerespondersaccount WHERE Barangay = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="event-log-info-container">';
                        echo '<div class="event-log-button">';
                        echo '<div class="event-log-info">';
                        $responderName = htmlspecialchars($row['Name']);
                        echo "$responderName<br>"; // Display only name
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No responders available</p>";
                }
                
                $stmt->close();
                ?>

            </div>
            
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

        </section>
    </body>
</html>
