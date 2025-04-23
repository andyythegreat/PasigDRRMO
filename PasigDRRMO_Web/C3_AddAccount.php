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
    <title> PCDRRMO | C3 Create Account </title>
    <link rel="stylesheet" href="C3_AddAccount25.css">
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
            <a href="C3_Account.php" class="nav_link active">
                <span class="navlink_icon">
                    <img src="images/Account.png" class="navlink_image">
                </span>
                <span class="navlink">  Accounts </span>
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $logo = $_FILES['logo']['tmp_name'];
    $logoName = $_FILES['logo']['name'];
    $username = $_POST['Name'];
    $email = $_POST['EmailAddress'];
    $password = $_POST['Password'];
    $position = $_POST['Position'];
    
    if (empty($username) || empty($email) || empty($password) || empty($position)) {
        die("All fields are required.");
    }

    $emailCheckStmt = $conn->prepare("SELECT * FROM c3_addaccount WHERE Email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckResult = $emailCheckStmt->get_result();

    if ($emailCheckResult->num_rows > 0) {
        echo "This email address is already in use. Please choose a different email.";
    } else {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($logoName);

        if (move_uploaded_file($logo, $uploadFile)) {
            $logoPath = $uploadFile;
        } else {
            die("Error uploading file.");
        }

        $stmt = $conn->prepare("INSERT INTO c3_addaccount (Logo, Username, Email, Password, Position) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $logoPath, $username, $email, $password, $position);

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $emailCheckStmt->close();
    $conn->close();
}
?>



<!-- Home Section -->
<section class="home-section">
    <h1> ADD ACCOUNT </h1>

    <div class="x-container">
        <div class="x-account">
            <a href="C3_Account.php" class="x-link"> X </a>
        </div>
    </div>

    <form action="C3_AddAccount.php" method="post" enctype="multipart/form-data">
    <div class="home-container">  

    <label for="logo">Logo:</label>
    <input type="file" id="logo" name="logo" required> <br> 

    <div id="logo-preview-container" style="width: 180px; height: 180px; border-radius: 50%; background-color: #0056b3; display: flex; align-items: center; justify-content: center; color: white; font-size: 170px; text-align: center;">
    <img id="logo-preview" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;" alt="">
    <span id="default-text">+</span>
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
    </script>
        <br> 

    <label for="position">Position:</label>
    <select id="position" name="Position" required>
        <option value="">Select type</option>
        <option value="Barangay">User</option>
        <option value="C3">Administrator</option>
    </select>

    <br> 
    <label for="username">Username:</label>
<input type="text" id="name" name="Name" required pattern="^[^\s]+$" title="Username cannot contain spaces." oninput="this.value = this.value.replace(/\s/g, '')"> <br>

<script>
    // Prevent spaces from being inputted into the username field dynamically
    document.getElementById('name').addEventListener('input', function(e) {
        if (e.target.value.includes(' ')) {
            alert('Spaces are not allowed in the username.');
            e.target.value = e.target.value.replace(/\s/g, '');  // Remove all spaces
        }
    });
</script>





    <label for="emailadd">Email Address:</label>
    <input type="email" id="email" name="EmailAddress" required pattern=".+@gmail\.com" title="Please enter a valid Gmail address."> <br> 


    <label for="password">Password:</label>
    <div class="password-container">
        <input type="password" id="password" name="Password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" title="Password must be at least 8 characters long, with at least one uppercase letter, one lowercase letter, one number, and one special character.">
    <button type="button" id="togglePassword" class="eye-button">
    <img id="eyeImage" src="images/eye_open.png" alt="Show Password" class="eye">
    </button>
    </div>
    
    <br>
    
    
    <div id="password-requirements" class="password-policy">
    <p id="length" class="requirement">✔️ At least 8 characters</p>
    <p id="uppercase" class="requirement">✔️ At least one uppercase letter</p>
    <p id="lowercase" class="requirement">✔️ At least one lowercase letter</p>
    <p id="number" class="requirement">✔️ At least one number</p>
    <p id="special" class="requirement">✔️ At least one special character (@$!%*?&)</p>
    </div>

    <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
    var passwordInput = document.getElementById("password");
    var eyeImage = document.getElementById("eyeImage");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeImage.src = "images/eye_closed.png";
    } else {
        passwordInput.type = "password";
        eyeImage.src = "images/eye_open.png";
    }
    });
    
    
    const passwordInput = document.getElementById("password");
    const lengthRequirement = document.getElementById("length");
    const uppercaseRequirement = document.getElementById("uppercase");
    const lowercaseRequirement = document.getElementById("lowercase");
    const numberRequirement = document.getElementById("number");
    const specialRequirement = document.getElementById("special");

passwordInput.addEventListener("input", function() {
    const password = passwordInput.value;

    lengthRequirement.classList.toggle("valid", password.length >= 8);
    lengthRequirement.textContent = password.length >= 8 ? "✔️ At least 8 characters" : "❌ At least 8 characters";

    uppercaseRequirement.classList.toggle("valid", /[A-Z]/.test(password));
    uppercaseRequirement.textContent = /[A-Z]/.test(password) ? "✔️ At least one uppercase letter" : "❌ At least one uppercase letter";

    lowercaseRequirement.classList.toggle("valid", /[a-z]/.test(password));
    lowercaseRequirement.textContent = /[a-z]/.test(password) ? "✔️ At least one lowercase letter" : "❌ At least one lowercase letter";

    numberRequirement.classList.toggle("valid", /\d/.test(password));
    numberRequirement.textContent = /\d/.test(password) ? "✔️ At least one number" : "❌ At least one number";

    specialRequirement.classList.toggle("valid", /[@$!%*?]/.test(password));
    specialRequirement.textContent = /[@$!%*?&-]/.test(password) ? "✔️ At least one special character" : "❌ At least one special character";
});

    </script> 

 <br><br>

    <button type="submit" id="createButton"> CREATE </button>
</form>

<script>
document.getElementById('createButton').addEventListener('click', function(event) {
    event.preventDefault();  // Prevent the form from submitting
    
    // Check if password meets all requirements
    const password = document.getElementById("password").value;
    
    const isLengthValid = password.length >= 8;
    const isUppercaseValid = /[A-Z]/.test(password);
    const isLowercaseValid = /[a-z]/.test(password);
    const isNumberValid = /\d/.test(password);
    const isSpecialCharValid = /[@$!%*?&]/.test(password);
    
    if (!isLengthValid || !isUppercaseValid || !isLowercaseValid || !isNumberValid || !isSpecialCharValid) {
        alert("Password does not meet the requirements. Please ensure it has at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.");
        return;
    }
    
    const emailInput = document.getElementById('email').value;
    if (!emailInput.match(/.+@gmail\.com/)) {
        alert('Please enter a valid Gmail address.');
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'check_email.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = xhr.responseText;
            if (response === 'exists') {
                alert('This email address is already in use. Please choose a different email.');
            } else {
                if (confirm('Are you sure you want to create this account?')) {
                    document.querySelector('form').submit();  // Submit the form if everything is valid
                }
            }
        } else {
            alert('Error checking email.');
        }
    };
    xhr.send('email=' + encodeURIComponent(emailInput));
});



</script>



    </div>
</section>

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


</body>
</html>
