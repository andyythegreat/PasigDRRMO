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
    <title>Pasig DRRMO | Status </title>
    <link rel="stylesheet" href="C3_ResponderDetails321.css">
    <link rel="shortcut icon" type="image/png" href="Title.png">
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
        <img src="images/Title.png" alt=""></i>Pasig City DRRMO
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
    
        <?php if ($showLogo): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="User Logo" class="user-logo">
            <?php endif; ?>
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

    <!-- Account -->
    <li class="item">
            <a href="C3_Account.php" class="nav_link">
                <span class="navlink_icon">
                    <img src="images/Account.png" class="navlink_image">
                </span>
                <span class="navlink"> Barangay Accounts </span>
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

<?php
include 'connection.php';

$responderID = $_GET['id'] ?? null;

if ($responderID) {
    // Fetch responder details
    $sql = "SELECT * FROM mobile_respond WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $responderID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $responder = $result->fetch_assoc();
    } else {
        echo "No responder found.";
    }
} else {
    echo "Invalid request.";
}
?>



 <!-- Home Section -->
    <section class="home-section">
        <h1>RESPONDER DETAILS</h1>

        <div class="home-container">
        <br> 

        <div class="home-container1">
        <h2> INFORMATION </h2> 
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
        const dateInput = document.getElementById('date');
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        dateInput.value = formattedDate;
        });
    </script>
       

    <label for="name">Name:</label>
<input type="text" id="name" name="name" class="caller" value="<?php echo htmlspecialchars($responder['Username'] ?? ''); ?>" readonly>

    <label for="location">Location:</label>
<input type="text" id="location" name="Location" class="location" value="<?php echo htmlspecialchars($responder['Location'] ?? ''); ?>" readonly>
    
    <div id="barangaySuggestions"></div>


    <div class="api-container">
        <iframe id="map"
                width="100%"
                height="500"
                style="border:0"
                loading="lazy"
                allowfullscreen
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3857.6384306655374!2d121.08317061478266!3d14.569443989816512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397dcb5516b78bd%3A0x4d6a793b0c4d1a4c!2sPasig%2C%20Metro%20Manila%2C%20Philippines!5e0!3m2!1sen!2sus!4v1589793640761!5m2!1sen!2sus">
        </iframe>    
    </div>

        <script>
        function updateMap() {
            var location = document.getElementById("location").value;
            var map = document.getElementById("map");
            map.src = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3857.6384306655374!2d121.08317061478266!3d14.569443989816512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397dcb5516b78bd%3A0x4d6a793b0c4d1a4c!2s" + location + "!5e0!3m2!1sen!2sus!4v1589793640761!5m2!1sen!2sus";
        }

        // Define an array of barangay names in Pasig City
// Define an array of barangay names in Pasig City
var barangays = [
    "Bagong Ilog, Pasig City",
    "Bagong Katipunan, Pasig City",
    "Bagong Lipunan ng Crame, Pasig City",
    "Bagong Pag-asa, Pasig City",
    "Bagong Silang, Pasig City",
    "Bagong Tanyag, Pasig City",
    "Bambang, Pasig City",
    "Buting, Pasig City",
    "Caniogan, Pasig City",
    "Dela Paz, Pasig City",
    "Kalawaan, Pasig City",
    "Kapasigan, Pasig City",
    "Kapitolyo, Pasig City",
    "Malinao, Pasig City",
    "Manggahan, Pasig City",
    "Maybunga, Pasig City",
    "Oranbo, Pasig City",
    "Palatiw, Pasig City",
    "Pinagbuhatan, Pasig City",
    "Pineda, Pasig City",
    "Rosario, Pasig City",
    "Sagad, Pasig City",
    "San Antonio, Pasig City",
    "San Joaquin, Pasig City",
    "San Jose, Pasig City",
    "San Miguel, Pasig City",
    "San Nicolas, Pasig City",
    "Santa Cruz, Pasig City",
    "Santa Lucia, Pasig City",
    "Santa Rosa, Pasig City",
    "Santo Tomas, Pasig City",
    "Santolan, Pasig City",
    "Sumilang, Pasig City",
    "Ugong, Pasig City"
];

// Function to suggest barangay names as the user types
function suggestBarangay(input) {
        var suggestions = document.getElementById("barangaySuggestions");
        suggestions.innerHTML = ""; // Clear previous suggestions

        // Filter barangay names based on input
        var filteredBarangays = barangays.filter(function(barangay) {
            return barangay.toLowerCase().startsWith(input.toLowerCase());
        });

        // Display suggestions
        filteredBarangays.forEach(function(barangay) {
            var suggestion = document.createElement("div");
            suggestion.textContent = barangay;
            suggestion.classList.add("suggestion");
            suggestion.onclick = function() {
                document.getElementById("location").value = barangay; // Set the selected barangay as the input value
                suggestions.innerHTML = ""; // Clear suggestions after selection
            };
            suggestions.appendChild(suggestion);
        });
    }

    // Update suggestions as the user types
    document.getElementById("location").addEventListener("input", function(event) {
        var input = event.target.value;
        suggestBarangay(input);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(event) {
        var suggestions = document.getElementById('barangaySuggestions');
        if (event.target !== suggestions && event.target !== document.getElementById('location')) {
            suggestions.innerHTML = ''; // Clear suggestions
        }
    });
</script>

    <div class="form-row">
    <div class="form-group">
        <label for="timerespond">Time Respond:</label>
        <input type="text" id="timerespond" name="timerespond" class="location" value="<?php echo htmlspecialchars($responder['TimeRespond'] ?? ''); ?>" readonly>
    </div>

    <div class="form-group">
        <label for="timearrived">Time Arrived:</label>
        <input type="text" id="timearrived" name="timearrived" class="location" value="<?php echo htmlspecialchars($responder['TimeArrived'] ?? ''); ?>" readonly>
    </div>

    <div class="form-group">
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" class="location" value="<?php echo htmlspecialchars($responder['RespondStatus']); ?>" readonly>
    </div>
    </div>
    
        </div> <br>

        </section>

        

</body>
</html>
