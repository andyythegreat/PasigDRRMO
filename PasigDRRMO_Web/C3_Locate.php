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
        <title> PCDRRMO | C3 Locate </title>
        <link rel="stylesheet" href="C3_Locate55.css">
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
            <a href="C3_Locate.php" class="nav_link active">
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
                    <img src="images/Cellphone.png" style="width:25px; height:25px;" class="navlink_image">
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

    <!-- ANALYTICS -->
    <ul class="menu_items">
        <div class="menu_title menu_data"></div>
        <!-- DATA ANALYTICS -->
        <li class="item">
            <a href="C3_DataAnalytics.php" class="nav_link submenu_item">
                <span class="navlink_icon">
                    <img src="images/Data_Analytics.png" class="navlink_image">
                </span>
                <span class="navlink"> Data Analytics</span>
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

<?php

require 'vendor/autoload.php';

use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $caller = $_POST["Caller"] != "" ? $_POST["Caller"] : "N/A";
    $location = $_POST["Location"];
    $barangay = $_POST["Barangay"];
    $involve = $_POST["Involve"];

    // Set the value for the 'Date' column to the current timestamp
    $date = date("Y-m-d H:i:s");

    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];

    $stmt1 = $conn->prepare("INSERT INTO c3_locate (Date, Caller, Location, Barangay, Involve) VALUES (?,?, ?, ?, ?)");
    $stmt1->bind_param("sssss",$date, $caller, $location,$barangay, $involve);

    $timestamp = date("Y-m-d H:i:s");
    $action = "Forwarded a fire report";
    $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, Caller, Location, Involve, ROLE, ACTION, TIMESTAMP, Barangay) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("ssssssss", $user, $caller, $location, $involve, $position, $action, $timestamp, $barangay);

    

    $success = $stmt1->execute() && $stmt2->execute();

    if ($success) {
        // Fetch tokens from the 'notif' table
        $notifQuery = "SELECT Token FROM notif";
        $notifResult = $conn->query($notifQuery);
        $tokens = [];
        while ($row = $notifResult->fetch_assoc()) {
            $tokens[] = $row['Token'];
        }

        // Check if there are tokens to notify
        if (!empty($tokens)) {
            $expo = new Expo();
            $messages = [];
            foreach ($tokens as $token) {
                // Create the message with location
                $messages[] = (new ExpoMessage())
                    ->setTitle('NEW FIRE ALERT!')
                    ->setBody("Fire reported at: $location. Stay safe and informed!")
                    ->setTo($token);           
            }

            // Send the notifications
            try {
                $expo->send($messages)->push();
            } catch (Exception $e) {
                error_log("Notification error: " . $e->getMessage());
            }

        echo "<script>alert('New records created successfully');</script>";
         } else {
            $error_msg .= "Error: No tokens found in 'notif' table.<br>";
        }
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt1->close();
    $stmt2->close();
}



?>




    <!-- Home Section -->
    <section class="home-section">
        <h1>FOR VERIFICATION</h1>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

        <div class="home-container">
        <br> 

        <div class="home-container1">
        <h2> INFORMATION </h2> 
        <div class="date-container">
          <input type="date" id="date" name="date" class="date" readonly>
          <label for="date" class="date-label">Date:</label>
        </div>
      </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
        const dateInput = document.getElementById('date');
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        dateInput.value = formattedDate;
        });
    </script>
       

<label for="caller">Caller:</label>
<input type="tel" id="caller" name="Caller" class="caller" maxlength="15" inputmode="numeric" 
       value="+63 " oninput="enforcePrefix()">

<script>
    function enforcePrefix() {
        const input = document.getElementById("caller");
        if (!input.value.startsWith("+63 ")) {
            input.value = "+63 ";
        }
        input.value = "+63 " + input.value.slice(4).replace(/[^0-9]/g, '').slice(0, 11);
    }
</script>

    <div class="location-barangay-container">

    <div>
    <label for="location">Location:</label>
    <div style="display: flex; align-items: center; width: 100%; border: 1px solid rgba(204, 204, 204, 0.1); box-sizing: border-box;">
    <input type="text" id="location" name="Location" class="location">
    </div>
    </div>

    <div>
        
<?php
$query = "
    SELECT DISTINCT Username 
    FROM c3_addaccount 
    WHERE Username LIKE 'BRGY_%' 
    ORDER BY Username ASC
";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$usernames = [];
while ($row = $result->fetch_assoc()) {
    $usernames[] = $row['Username'];
}
?>

<div>
    <label for="Barangay">Barangay:</label>
    <select id="Barangay" name="Barangay" class="location">
        <option value="">Select Barangay</option>
        <?php foreach ($usernames as $username): ?>
            <option value="<?= $username ?>"><?= $username ?></option>
        <?php endforeach; ?>
    </select>
</div>



                </div>
            </div>



        <!-- Map Container -->
        <div class="api-container">
            <div id="map" style="height: 500px; width: 100%;"></div>
        </div>
        
        <!-- Google Maps JavaScript API and Places library -->
        <script></script>
        
        <script>
        let map;
        let userMarker;
        let geocoder;
        let lastFireMarker = null; 
        
        const pasigCoordinates = [
            {lat:14.568020, lng:121.051401},
            {lat:14.568226, lng:121.051438},
            {lat:14.568405, lng:121.051721},
            {lat: 14.568993, lng: 121.051963},
            {lat: 14.570392, lng: 121.052360},
            {lat: 14.570679, lng: 121.052971},
            {lat: 14.570553, lng: 121.053069},
            {lat: 14.570935, lng: 121.053671},
            {lat: 14.571993, lng: 121.054774},
            {lat: 14.572746, lng: 121.056217},
            {lat: 14.573424, lng: 121.056728},
            {lat: 14.573734, lng: 121.057000},
            {lat: 14.573849, lng: 121.057096},
            {lat: 14.574610, lng: 121.057902},
            {lat: 14.576543, lng: 121.055978},
            {lat: 14.581752, lng: 121.059905},
            {lat: 14.590166, lng: 121.059664},
            {lat: 14.590425, lng: 121.061664},
            {lat: 14.591903, lng: 121.068055},
            {lat: 14.592900, lng: 121.069516},
            {lat: 14.593179, lng: 121.071007},
            {lat: 14.593148, lng:121.072774},
            {lat: 14.593128, lng: 121.073012},
            {lat: 14.593064, lng: 121.073296},
            {lat: 14.592397, lng: 121.077719},
            {lat: 14.591649, lng: 121.079623},
            {lat: 14.590744, lng: 121.082919},
            {lat: 14.591559, lng:121.082681},
            {lat: 14.593594, lng: 121.082359},
            {lat: 14.595586, lng: 121.082383},
            {lat: 14.597310, lng: 121.082209},
            {lat: 14.598742, lng: 121.082214},
            {lat: 14.600861, lng: 121.082126},
            {lat: 14.602254, lng: 121.082169},
            {lat: 14.602854, lng: 121.082256},
            {lat: 14.603332, lng:121.082771},
            {lat: 14.603724, lng: 121.083732},
            {lat: 14.603206, lng: 121.085454},
            {lat: 14.602169, lng: 121.087146},
            {lat: 14.599860, lng: 121.087703},
            {lat: 14.599326, lng: 121.088129},
            {lat: 14.599095, lng: 121.088670},
            {lat: 14.599220, lng: 121.089700},
            {lat: 14.600943, lng: 121.090428},
            {lat: 14.606082, lng:121.087600},
            {lat: 14.612230, lng: 121.079780},
            {lat: 14.614429, lng: 121.078526},
            {lat: 14.616275, lng: 121.078281},
            {lat: 14.617133, lng: 121.078649},
            {lat: 14.618023, lng: 121.079201},
            {lat: 14.620279, lng: 121.081442},
            {lat: 14.623634, lng: 121.083841},
            {lat: 14.622115, lng: 121.085791},
            {lat: 14.622778, lng: 121.086678},
            {lat: 14.622739, lng: 121.088711},
            {lat: 14.622386, lng: 121.088735},
            {lat: 14.622315, lng: 121.088772},
            {lat: 14.622293, lng: 121.088852},
            {lat: 14.622327, lng:  121.089277},
            {lat: 14.621992, lng: 121.089297},
            {lat: 14.621975, lng: 121.092704},
            {lat: 14.621568, lng: 121.092703},
            {lat: 14.621556, lng: 121.093332},
            {lat: 14.621527, lng: 121.094720},
            {lat: 14.621234, lng: 121.094741},
            {lat: 14.621169, lng: 121.095305},
            {lat: 14.621381, lng: 121.096024},
            {lat: 14.621124, lng: 121.096095},
            {lat: 14.621026, lng: 121.096180},
            {lat: 14.621041, lng: 121.096385},
            {lat: 14.621366, lng: 121.097154},
            {lat: 14.620185, lng: 121.097656},
            {lat: 14.619814, lng: 121.097740},
            {lat: 14.620279, lng: 121.100668},
            {lat: 14.619504, lng: 121.100578},
            {lat: 14.619488, lng: 121.100982},
            {lat: 14.618601, lng: 121.100923},
            {lat: 14.618769, lng: 121.098759},
            {lat: 14.616999, lng: 121.098436},
            {lat: 14.616985, lng: 121.098316},
            {lat: 14.616378, lng: 121.098192},
            {lat: 14.616305, lng: 121.098246},
            {lat: 14.615892, lng: 121.098140},
            {lat: 14.615838, lng: 121.098233},
            {lat: 14.615759, lng: 121.098282},
            {lat: 14.615570, lng: 121.098299},
            {lat: 14.615587, lng: 121.098451},
            {lat: 14.615573, lng: 121.098449},
            {lat: 14.615537, lng: 121.099546},
            {lat: 14.615984, lng: 121.099622},
            {lat: 14.616360, lng: 121.099789},
            {lat: 14.616435, lng: 121.099910},
            {lat: 14.616248, lng: 121.101061},
            {lat: 14.616127, lng: 121.101454},
            {lat: 14.615968, lng: 121.101821},
            {lat: 14.603809, lng: 121.105533},
            {lat: 14.603041, lng: 121.105910},
            {lat: 14.597001, lng: 121.109584},
            {lat: 14.596252, lng: 121.109522},
            {lat: 14.596145, lng: 121.109446},
            {lat: 14.595568, lng: 121.109461},
            {lat: 14.594873, lng: 121.109782},
            {lat: 14.594463, lng: 121.110041},
            {lat: 14.593842, lng: 121.110278},
            {lat: 14.593166, lng: 121.110286},
            {lat: 14.592793, lng: 121.110312},
            {lat: 14.591293, lng: 121.110938},
            {lat: 14.591113, lng: 121.110944},
            {lat: 14.590844, lng: 121.110806},
            {lat: 14.590624, lng: 121.110615},
            {lat: 14.590271, lng: 121.110126},
            {lat: 14.589585, lng: 121.109683},
            {lat: 14.587851, lng: 121.108694},
            {lat: 14.587123, lng: 121.108354},
            {lat: 14.586936, lng: 121.108407},
            {lat: 14.586560, lng: 121.108470},
            {lat: 14.586166, lng: 121.108401},
            {lat: 14.585854, lng: 121.108279},
            {lat: 14.585669, lng: 121.108249},
            {lat: 14.585490, lng:  121.108350},
            {lat: 14.584665, lng: 121.108183},
            {lat: 14.584419, lng: 121.108162},
            {lat: 14.584336, lng: 121.108207},
            {lat: 14.583823, lng: 121.108206},
            {lat: 14.583502, lng:  121.108424},
            {lat: 14.583310, lng: 121.108463},
            {lat: 14.583038, lng: 121.108676},
            {lat: 14.582676, lng: 121.108884},
            {lat: 14.582491, lng: 121.108979},
            {lat: 14.582121, lng: 121.109043},
            {lat: 14.581576, lng: 121.108893},
            {lat: 14.581043, lng: 121.109095},
            {lat: 14.580643, lng: 121.108984},
            {lat: 14.579711, lng: 121.108613},
            {lat: 14.579393, lng: 121.108440},
            {lat: 14.578994, lng: 121.107972},
            {lat: 14.578790, lng: 121.106917},
            {lat: 14.578724, lng: 121.106785},
            {lat: 14.578694, lng: 121.106551},
            {lat: 14.577727, lng: 121.105771},
            {lat: 14.577719, lng: 121.105306},
            {lat: 14.577560, lng: 121.104971},
            {lat: 14.577076, lng: 121.104456},
            {lat: 14.575242, lng: 121.101382},
            {lat: 14.574646, lng: 121.100328},
            {lat: 14.573498, lng: 121.101163},
            {lat: 14.573171, lng: 121.101284},
            {lat: 14.572841, lng: 121.100913},
            {lat: 14.572618, lng: 121.100605},
            {lat: 14.570096, lng: 121.102263},
            {lat: 14.570192, lng: 121.102373},
            {lat: 14.569123, lng: 121.103003},
            {lat: 14.568998, lng:  121.103102},
            {lat: 14.568515, lng:  121.103389},
            {lat: 14.568284, lng:  121.103556},
            {lat: 14.567656, lng: 121.103499},
            {lat: 14.567550, lng: 121.104073},
            {lat: 14.567243, lng: 121.104044},
            {lat: 14.567129, lng: 121.104200},
            {lat: 14.566623, lng: 121.104543},
            {lat: 14.566651, lng: 121.104677},
            {lat: 14.566249, lng: 121.104946},
            {lat: 14.566055, lng: 121.104828},
            {lat: 14.565985, lng: 121.104497},
            {lat: 14.565859, lng: 121.104522},
            {lat: 14.565418, lng:  121.104368},
            {lat: 14.565347, lng: 121.105220},
            {lat: 14.565112, lng: 121.105567},
            {lat: 14.564763, lng: 121.105813},
            {lat: 14.564744, lng: 121.106002},
            {lat: 14.563388, lng: 121.106982},
            {lat: 14.563124, lng: 121.107101},
            {lat: 14.563056, lng: 121.107031},
            {lat: 14.562874, lng: 121.107005},
            {lat: 14.561835, lng: 121.106316},
            {lat: 14.561762, lng: 121.105731},
            {lat: 14.562052, lng: 121.105379},
            {lat: 14.562986, lng: 121.104789},
            {lat: 14.562999, lng: 121.104704},
            {lat: 14.562949, lng: 121.104527},
            {lat: 14.563087, lng: 121.104051},
            {lat: 14.563165, lng: 121.103937},
            {lat: 14.563662, lng: 121.103650},
            {lat: 14.563650, lng: 121.103500},
            {lat: 14.563454, lng: 121.103138},
            {lat: 14.562958, lng: 121.102652},
            {lat: 14.563812, lng:  121.100118},
            {lat: 14.564522, lng: 121.100365},
            {lat: 14.564570, lng: 121.100357},
            {lat: 14.564763, lng: 121.099601},
            {lat: 14.564723, lng: 121.099574},
            {lat: 14.564527, lng: 121.099508},
            {lat: 14.565139, lng: 121.096309},
            {lat: 14.564631, lng: 121.096284},
            {lat: 14.564401, lng: 121.096080},
            {lat: 14.563918, lng: 121.095941},
            {lat: 14.563253, lng: 121.097074},
            {lat: 14.562734, lng: 121.097747},
            {lat: 14.562415, lng: 121.097939},
            {lat: 14.561370, lng: 121.096900},
            {lat: 14.560248, lng: 121.096321},
            {lat: 14.559960, lng: 121.096337},
            {lat: 14.559381, lng: 121.096940},
            {lat: 14.558419, lng: 121.098205},
            {lat: 14.557693, lng: 121.097928},
            {lat: 14.557050, lng:  121.097913},
            {lat: 14.556509, lng:  121.097965},
            {lat: 14.555490, lng: 121.098297},
            {lat: 14.554786, lng: 121.098170},
            {lat: 14.553178, lng: 121.099375},
            {lat: 14.553461, lng: 121.100227},
            {lat: 14.553636, lng: 121.101181},
            {lat: 14.552765, lng: 121.101815},
            {lat: 14.550866, lng: 121.104915},
            {lat: 14.548310, lng: 121.108069},
            {lat: 14.546532, lng: 121.109653},
            {lat: 14.546350, lng: 121.109622},
            {lat: 14.545995, lng: 121.109426},
            {lat: 14.545917, lng: 121.109284},
            {lat: 14.545956, lng: 121.107349},
            {lat: 14.545842, lng: 121.107239},
            {lat: 14.545796, lng:  121.107174},
            {lat: 14.545676, lng: 121.107197},
            {lat: 14.540421, lng: 121.105863},
            {lat: 14.539740, lng: 121.105786},
            {lat: 14.539558, lng: 121.105737},
            {lat: 14.539556, lng:  121.105579},
            {lat: 14.539348, lng: 121.105581},
            {lat: 14.539090, lng: 121.105574},
            {lat: 14.538103, lng: 121.105367},
            {lat: 14.537643, lng: 121.105171},
            {lat: 14.537452, lng: 121.105052},
            {lat: 14.535738, lng: 121.104547},
            {lat: 14.533892, lng: 121.104059},
            {lat: 14.537556, lng: 121.098804},
            {lat: 14.539005, lng: 121.097924},
            {lat: 14.541420, lng: 121.095971},
            {lat: 14.542780, lng: 121.096149},
            {lat: 14.543736, lng: 121.096030},
            {lat: 14.544447, lng: 121.095269},
            {lat: 14.544712, lng: 121.095076},
            {lat: 14.544665, lng: 121.092013},
            {lat: 14.545060, lng: 121.090586},
            {lat: 14.543676, lng: 121.087529},
            {lat: 14.543625, lng: 121.086364},
            {lat: 14.543947, lng: 121.085586},
            {lat: 14.542903, lng: 121.083569},
            {lat: 14.543220, lng: 121.083328},
            {lat: 14.543485, lng: 121.082856},
            {lat: 14.543568, lng: 121.082164},
            {lat: 14.543376, lng: 121.081692},
            {lat: 14.543064, lng: 121.081209},
            {lat: 14.544663, lng: 121.080855},
            {lat: 14.545473, lng:  121.081123},
            {lat: 14.546512, lng:  121.081214},
            {lat: 14.546896, lng: 121.080957},
            {lat: 14.546964, lng: 121.080533},
            {lat: 14.546829, lng: 121.080286},
            {lat: 14.546730, lng: 121.079744},
            {lat: 14.547062, lng: 121.078387},
            {lat: 14.548584, lng: 121.078505},
            {lat: 14.548670, lng: 121.078124},
            {lat: 14.550084, lng: 121.078210},
            {lat: 14.550043, lng: 121.077792},
            {lat: 14.550851, lng: 121.077554},
            {lat: 14.550931, lng: 121.076242},
            {lat: 14.551624, lng: 121.074536},
            {lat: 14.551316, lng: 121.074252},
            {lat: 14.552831, lng: 121.072180},
            {lat: 14.552747, lng: 121.072025},
            {lat: 14.552694, lng: 121.071702},
            {lat: 14.552603, lng: 121.071467},
            {lat: 14.552363, lng: 121.071112},
            {lat: 14.552006, lng: 121.071123},
            {lat: 14.551829, lng: 121.070721},
            {lat: 14.552105, lng: 121.070188},
            {lat: 14.552030, lng: 121.069897},
            {lat: 14.551959, lng: 121.069872},
            {lat: 14.551910, lng: 121.069417},
            {lat: 14.551980, lng: 121.069335},
            {lat: 14.551971, lng: 121.069054},
            {lat: 14.552671, lng: 121.068834},
            {lat: 14.552300, lng: 121.067482},
            {lat: 14.552590, lng: 121.065835},
            {lat: 14.552713, lng: 121.065839},
            {lat: 14.552684, lng: 121.064442},
            {lat: 14.553495, lng: 121.064615},
            {lat: 14.555369, lng: 121.065417},
            {lat: 14.555902, lng: 121.065605},
            {lat: 14.556157, lng: 121.065762},
            {lat: 14.556742, lng: 121.066272},
            {lat: 14.557199, lng: 121.066502},
            {lat: 14.557320, lng: 121.066648},
            {lat: 14.557530, lng: 121.067012},
            {lat: 14.558757, lng: 121.067107},
            {lat: 14.559053, lng: 121.067089},
            {lat: 14.560166, lng: 121.066503},
            {lat: 14.560769, lng: 121.065553},
            {lat: 14.561532, lng: 121.062682},
            {lat: 14.564810, lng: 121.058697},
            {lat: 14.565264, lng: 121.057393},
            {lat: 14.565678, lng: 121.055151},
            {lat: 14.568036, lng: 121.051413},
        ]; 
        
        // Function to fetch fire truck locations from PHP
        async function fetchFireTruckLocations() {
            try {
                const response = await fetch('https://pasigdrrmo.site/truck-markers.php'); 
                const locations = await response.json(); 
                return locations;
            } catch (error) {
                console.error("Error fetching fire truck locations:", error);
            }
        }
        
        function initMap() {
            geocoder = new google.maps.Geocoder();
        
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
        
                        map = new google.maps.Map(document.getElementById("map"), {
                            zoom: 13,
                            center: userLocation,
                            mapTypeId: 'roadmap'
                        });
        
                        userMarker = new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: "Your Location"
                        });
        
                        // Draw Pasig City outline
                        const pasigOutline = new google.maps.Polygon({
                            paths: pasigCoordinates,
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                            clickable: false 
                        });
                        pasigOutline.setMap(map);
        
                        await addPasigCityMarkers(); 
        
                        map.addListener("click", function (event) {
                            if (google.maps.geometry.poly.containsLocation(event.latLng, pasigOutline)) {
                                getAddress(event.latLng);
        
                                if (lastFireMarker) {
                                    lastFireMarker.setMap(null); 
                                }

                                var icon = {
                                    url: 'https://pasigdrrmo.site/images/flamesMarker.png', 
                                    scaledSize: new google.maps.Size(50, 50) 
                                };
        
                                lastFireMarker = new google.maps.Marker({
                                    position: event.latLng,
                                    map: map,
                                    icon: icon,
                                    title: 'Fire Location'
                                });
                            } else {
                                alert("You are outside of Pasig City. Please click within Pasig City.");
                            }
                        });
                    },
                    () => {
                        handleLocationError(true, map.getCenter());
                    }
                );
            } else {
                handleLocationError(false, map.getCenter());
            }
        }
        
        function handleLocationError(browserHasGeolocation, pos) {
            const fallbackLocation = { lat: 14.559622, lng: 121.081197 };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: fallbackLocation,
                mapTypeId: 'roadmap'
            });
        
            // Fallback location
            userMarker = new google.maps.Marker({
                position: fallbackLocation,
                map: map,
                title: "Fallback Location"
            });
        
            // Add fire truck markers 
            addPasigCityMarkers();
        
            map.addListener("click", function (event) {
                getAddress(event.latLng);
            });
        }
        
        // Add fire truck markers 
        async function addPasigCityMarkers() {
            const pasigLocations = await fetchFireTruckLocations();
        
            pasigLocations.forEach(location => {
                new google.maps.Marker({
                    position: {
                        lat: parseFloat(location.Latitude), 
                        lng: parseFloat(location.Longitude)
                    },
                    map: map,
                    title: "Fire Truck Location in Pasig City",
                    icon: {
                        url: "https://img.icons8.com/external-goofy-color-kerismaker/96/external-Fire-Truck-transportation-obivous-color-kerismaker.png",
                        scaledSize: new google.maps.Size(50, 50)
                    }
                });
            });
        }
        
        function initAutocomplete() {
          const input = document.getElementById("location");
        
          // Define more precise bounds for Pasig City
          const pasigBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(14.5323, 121.0555), // SW corner (approximate)
            new google.maps.LatLng(14.6074, 121.0926)  // NE corner (approximate)
          );
        
          const autocomplete = new google.maps.places.Autocomplete(input, {
            bounds: pasigBounds,
            componentRestrictions: { country: "PH" },
            types: [],
          });
        
          autocomplete.addListener("place_changed", function () {
            const place = autocomplete.getPlace();
        
            if (!place.geometry) {
              window.alert("No details available for the selected location.");
              return;
            }
        
            console.log("Selected location coordinates:", place.geometry.location.lat(), place.geometry.location.lng());
            console.log("Bounds:", pasigBounds.toString());
        
            const addressComponents = place.address_components;
            const isPasig = addressComponents.some(component => 
              component.long_name.includes("Pasig") || component.short_name.includes("Pasig")
            );
        
            if (!isPasig) {
              window.alert("The selected location is outside of Pasig City.");
              input.value = '';
              return;
            }
        
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        
            if (lastFireMarker) {
              lastFireMarker.setMap(null);
            }
        
            userMarker.setPosition(place.geometry.location);
        
            const icon = {
              url: "https://pasigdrrmo.site/images/flamesMarker.png",
              scaledSize: new google.maps.Size(50, 50),
            };
        
            lastFireMarker = new google.maps.Marker({
              position: place.geometry.location,
              map: map,
              icon: icon,
              title: "Fire Location",
            });
          });
        }

        function getAddress(latLng) {
            geocoder.geocode({ location: latLng }, function (results, status) {
                if (status === "OK") {
                    if (results[0]) {
                        const input = document.getElementById("location");
                        input.value = results[0].formatted_address;
        
                        // Optionally, move the userMarker to the clicked location
                        userMarker.setPosition(latLng);
                        map.setCenter(latLng);
                        map.setZoom(17);
        
                        // Find the Barangay (or sub-region) from the address components
                        let barangay = null;
                        for (let i = 0; i < results[0].address_components.length; i++) {
                            const component = results[0].address_components[i];
                            if (component.types.includes("sublocality_level_1") || component.types.includes("political")) {
                                barangay = component.long_name; // You may adjust the component type based on your needs
                                break;
                            }
                        }
        
                        // Set the Barangay input field
                        if (barangay) {
                            const barangayInput = document.getElementById("Barangay");
                            barangayInput.value = barangay; // Automatically set Barangay based on address
                        }
                    } else {
                        window.alert("No results found");
                    }
                } else {
                    window.alert("Geocoder failed due to: " + status);
                }
            });
        }
        
        function loadMap() {
            initMap();
            initAutocomplete();
        }
        
        window.onload = loadMap;
        </script>


<?php
$query = "SELECT DISTINCT Involve FROM c3_involve";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$usernames = [];
while ($row = $result->fetch_assoc()) {
    $usernames[] = $row['Involve'];
}
?>

<label for="involve">Involve:</label>
<select id="involve" name="Involve" class="involve">
    <option value="Not specified">Not specified by caller</option>
    
    <?php foreach ($usernames as $involve): ?>
        <option value="<?php echo htmlspecialchars($involve); ?>"><?php echo htmlspecialchars($involve); ?></option>
    <?php endforeach; ?>
</select>
        </div> <br>

        <button type="button" id="forwardButton">Forward</button> <!-- Change type to button -->

<script>
    document.getElementById('forwardButton').addEventListener('click', function() {
        document.querySelector('form').submit(); // Submit the form
    });
</script>
        </form>
        </section>
        
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

        

    </body>
    </html>
