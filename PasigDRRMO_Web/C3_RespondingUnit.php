<?php
session_start();
date_default_timezone_set('Asia/Manila');

include 'connection.php';

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasig DRRMO | Responding Unit </title>
    <link rel="stylesheet" href="C3_RespondingUnit55.css">
    <link rel="shortcut icon" type="image/png" href="<?php echo $faviconPath; ?>">
</head>
<body>


<?php
include 'connection.php';
$id = null;

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $sql = "SELECT Date, Caller, Location, Involve, Status FROM c3_locate WHERE ID = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Store fetched data in variables

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
if ($_SERVER["REQUEST_METHOD"] == "POST" && $id !== null) {
    $newDate = $_POST["Date"];
    $newCaller = $_POST["Caller"];
    $newLocation = $_POST["Location"];
    $newInvolve = $_POST["Involve"];
    $newStatus = $_POST["Status"];

    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];

    

    // Update status in the database
    $updateSql = "UPDATE c3_locate SET Date ='$newDate', Caller ='$newCaller', Location = '$newLocation', Involve = '$newInvolve', Status = '$newStatus' WHERE ID = $id";

    $timestamp = date("Y-m-d H:i:s");
    $action = "Updated status";
    $stmt = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $position, $action, $timestamp);

    $success = $stmt->execute();

    if ($conn->query($updateSql) === TRUE) {
        echo "Status updated successfully";
        header("Location: C3_Ongoing.php");
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
            <a href="C3_Ongoing.php" class="nav_link active">
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
include 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    die("ID parameter is missing.");
}

$query = "SELECT Username AS Responder, RespondersBarangay AS Barangay, RespondStatus AS Status, TimeRespond, TimeArrived
          FROM mobile_respond
          WHERE OngoingID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$respondingUnits = [];
while ($row = $result->fetch_assoc()) {
    $respondingUnits[] = $row;
}

?>

<!-- Home Section in C3_RespondingUnit.php -->
<section class="home-section">
    <h1>RESPONDING UNIT</h1>

    <form method="post" action="#"> 
        <input type="hidden" name="ID" value="<?php echo $id; ?>">

        <div class="home-container1">
            <h2>INFORMATION</h2> 
            <div class="x-account">
        <a href="C3_Status.php?id=<?php echo $id; ?>" class="x-link">X</a>

            </div>
        </div> 

        <div class="home-container1">
            <div class="table-container">
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
                    <tbody id="respondingUnitsTable">
                        <?php 
                        if (!empty($respondingUnits)) {
                            foreach ($respondingUnits as $unit) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($unit['Responder']) . "</td>
                                        <td>" . htmlspecialchars($unit['Barangay']) . "</td>
                                        <td>" . htmlspecialchars($unit['TimeRespond']) . "</td>
                                        <td>" . htmlspecialchars($unit['TimeArrived']) . "</td>
                                        <td>" . htmlspecialchars($unit['Status']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5">No responding units found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</section>

<script>
    function fetchRespondingUnits() {
        var id = <?php echo $id; ?>;
        
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_responding_units.php?id=" + id, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var respondingUnits = JSON.parse(xhr.responseText);
                var tableBody = document.getElementById("respondingUnitsTable");

                tableBody.innerHTML = "";

                if (respondingUnits.length > 0) {
                    respondingUnits.forEach(function(unit) {
                        var row = document.createElement("tr");
                        
                        row.innerHTML = `
                            <td>${unit.Responder}</td>
                            <td>${unit.Barangay}</td>
                            <td>${unit.TimeRespond}</td>
                            <td>${unit.TimeArrived}</td>
                            <td>${unit.Status}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5">No responding units found.</td></tr>';
                }
            }
        };
        xhr.send();
    }

    setInterval(fetchRespondingUnits, 5000);

    fetchRespondingUnits();
</script>
