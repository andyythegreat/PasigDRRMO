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

$uploadDir = "uploads/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | C3 Accounts </title>
    <link rel="stylesheet" href="C3_Account55.css">
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

if (isset($_GET['archive_id'])) {
    $archive_id = intval($_GET['archive_id']);

    // Archive the record
    $archive_sql = "INSERT INTO c3_archiveaccount (ID, Logo, Username, Email, Position, Password)
                    SELECT ID, Logo, Username, Email, Position, Password
                    FROM c3_addaccount WHERE ID = ?";
    $stmt = $conn->prepare($archive_sql);
    $stmt->bind_param("i", $archive_id);

    if ($stmt->execute()) {
        $delete_sql = "DELETE FROM c3_addaccount WHERE ID = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $archive_id);

        if ($stmt->execute()) {
            echo "<script>alert('Record archived successfully!');</script>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Error archiving record: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id = $_POST['updateId'];
    $username = $_POST['Name'];
    $email = $_POST['EmailAddress'];
    $password = $_POST['Password'];
    $position = $_POST['userRole'];

    // Handle file upload
    $logoPath = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileSize = $_FILES['logo']['size'];
        $fileType = $_FILES['logo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Check if the file extension is allowed
        $allowedExtensions = array('jpg', 'jpeg', 'png');
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $logoPath = $dest_path;
            }
        }
    } else {
        $sql = "SELECT Logo FROM c3_addaccount WHERE ID='$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $logoPath = $row['Logo'];
        }
    }

    $sql = "UPDATE c3_addaccount SET Logo='$logoPath', Username='$username', Email='$email', Password='$password', Position='$position' WHERE ID='$id'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record updated successfully!'); window.location.href='C3_Account.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$recordsPerPage = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

$totalRecordsQuery = "SELECT COUNT(*) as total FROM c3_addaccount";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

$sql = "SELECT a.ID, a.Logo, a.Username, a.Email, a.Position, a.Password, b.Status 
        FROM c3_addaccount a
        LEFT JOIN c3_barangay b ON a.Username = b.Barangay
        ORDER BY 
            CASE 
                WHEN a.Position = 'C3' THEN 0 
                WHEN a.Username = 'PCDRRMO' THEN 1 
                ELSE 2 
            END, 
            a.Username ASC 
        LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);


?>

<!-- Home Section -->
<section class="home-section">
    <h1> ACCOUNT </h1>

    <div class="account-container">
    <div class="add-account">
    <a href="C3_ArchivedAccount.php" class="add-account-link"> Archived
    </a>
        <a href="C3_AddAccount.php" class="add-account-link">
            <img src="images/Add_Account.png" alt="Icon" class="icon"> Add Account
        </a>
        <a href="C3_ResidentAccount.php" class="add-account-link">
            <img src="images/ResidentAccount.png" alt="Icon" class="icon" style="width: 25px; height: 25px; vertical-align: middle;"> Residents
        </a>
    </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Active Status</th>
                    <th>LOGO</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>POSITION</th>
                    <th style='display: none;'>PASSWORD</th>
                    <th>ACTION</th>
                </tr>
            </thead>
<tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['Logo'];
            echo "<tr>";
            echo "<td>";
            if (!empty($row['Status'])) {
                echo htmlspecialchars($row['Status']);
            } else {
                echo "No Status";
            }
            echo "</td>";        
            echo "<td>";
            echo "<img src='{$imagePath}' alt='logo' style='width: 60px; height: 60px; border-radius: 50%;'>";
            echo "</td>";
            echo "<td>" . htmlspecialchars($row["Username"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";

            // Display "Administrator" for C3 and "User" for BGRY, otherwise show the original Position
            echo "<td>";
            if ($row["Position"] === "C3") {
                echo "Administrator";
            } elseif ($row["Position"] === "Barangay") {
                echo "User";
            } else {
                echo htmlspecialchars($row["Position"]);
            }
            echo "</td>";

            echo "<td style='display: none;'>" . htmlspecialchars($row["Password"]) . "</td>";
            echo "<td> 
            <div class='btn-container'>
            <button class='update-button' data-id='" . $row["ID"] . "' onclick='openUpdateForm(this)'> <img src='images/update.png' alt='Update Icon'> </button>
            <button class='archive-button'><a href='C3_Account.php?archive_id=" . $row["ID"] . "' class='archive' type='submit'> <img src='images/archive.png' alt='Archive Icon'> 
            </button>
            </div> </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No records found</td></tr>";
    }
    $conn->close();
    ?>
</tbody>
        </table>
    </div>

    <div class="button-container">
    <div class="prev-next-buttons">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="prev-next-button"> < Prev </a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="prev-next-button">Next > </a>
        <?php endif; ?>
    </div>
</div>

<div class="popup-overlay" id="popup-overlay"></div>
<div class="fire-truck-popup" id="popup-update">
<div class="fire-truck-popup-wrapper"> 
<div class="fire-truck-popup-content">
    <div class="fire-truck-popup-header">
        <span class="close" onclick="closeUpdateForm()"> ✖ </span>
        <h2>Update Form</h2>
    </div> 
    <hr>

    <div class="fire-truck-popup-container">
        <form id="updateForm" method="post" enctype="multipart/form-data">
        
        <div class="update-form-group">
        <input type="hidden" id="updateId" name="updateId">
        <input type="hidden" id="updateReference" name="updateReference">
        </div> 

        <div class="update-form-group">
        <label for="logo">Logo:</label>
        <input type="file" id="logo" name="logo">
        <div id="logo-preview-container">
            <img id="logo-preview" alt="Logo Preview">
            <span id="default-text">+</span>
        </div>
        </div> 
        
            <div class="update-form-group">
                <label for="userRole">Role:</label>
                <div class="role-container">
                    <input type="radio" id="adminRole" name="userRole" value="C3" required> 
                    <label for="adminRole">Administrator</label>
                </div>
                <div class="role-container">
                    <input type="radio" id="regularUserRole" name="userRole" value="Barangay" required>
                    <label for="regularUserRole">User</label>
                </div>
            </div>

        <div class="update-form-group">
        <label for="username">Username:</label>
        <input type="text" id="name" name="Name" required>
        </div>

        <div class="update-form-group">
        <label for="emailadd">Email Address:</label>
        <input type="email" id="email" name="EmailAddress" required>
        </div>

        <div class="update-form-group">
        <label for="password">Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="Password" required>
            <button type="button" id="togglePassword" class="eye-button">
                <img id="eyeImage" src="images/eye_open.png" alt="Show Password" class="eye">
            </button>
        </div>
        </div> <br>

        <div class="fire-truck-popup-buttons">
        <button type="submit" class="fire-truck-add">UPDATE</button>
        </div>
    </form>
</div>
</div>
</div>
</div>

<script>
function openUpdateForm(button) {
    var row = button.closest('tr');
    var logo = row.querySelector('td img').src;
    var username = row.querySelector('td:nth-child(3)').textContent;
    var email = row.querySelector('td:nth-child(4)').textContent;
    var password = row.querySelector('td:nth-child(6)').textContent;
    var role = row.querySelector('td:nth-child(5)').textContent.trim();

    document.getElementById('logo-preview').src = logo;
    document.getElementById('logo-preview').style.display = 'block';
    document.getElementById('default-text').style.display = 'none';
    document.getElementById('name').value = username;
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.getElementById('updateId').value = button.getAttribute('data-id');

    // Set the current role as checked
    if (role === "Administrator") {
        document.getElementById('adminRole').checked = true;
    } else if (role === "User") {
        document.getElementById('regularUserRole').checked = true;
    }

    document.getElementById('popup-update').style.display = 'block';
    document.getElementById('popup-overlay').style.display = 'block';
}



function closeUpdateForm() {
    document.getElementById('popup-update').style.display = 'none';
    document.getElementById('popup-overlay').style.display = 'none';
}

// Password toggle functionality
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

// Logo preview functionality
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

document.getElementById('updateForm').addEventListener('submit', function(event) {
    // Prevent form submission until validation is checked
    event.preventDefault();

    // Get the password field and its value
    var password = document.getElementById('password').value;
    var lengthRequirement = document.getElementById('length');
    var uppercaseRequirement = document.getElementById('uppercase');
    var lowercaseRequirement = document.getElementById('lowercase');
    var numberRequirement = document.getElementById('number');
    var specialRequirement = document.getElementById('special');

    // Check password length
    var isLengthValid = password.length >= 8;
    lengthRequirement.classList.toggle('valid', isLengthValid);
    lengthRequirement.textContent = isLengthValid ? "✔️ At least 8 characters" : "❌ At least 8 characters";

    // Check if password contains at least one uppercase letter
    var isUppercaseValid = /[A-Z]/.test(password);
    uppercaseRequirement.classList.toggle('valid', isUppercaseValid);
    uppercaseRequirement.textContent = isUppercaseValid ? "✔️ At least one uppercase letter" : "❌ At least one uppercase letter";

    // Check if password contains at least one lowercase letter
    var isLowercaseValid = /[a-z]/.test(password);
    lowercaseRequirement.classList.toggle('valid', isLowercaseValid);
    lowercaseRequirement.textContent = isLowercaseValid ? "✔️ At least one lowercase letter" : "❌ At least one lowercase letter";

    // Check if password contains at least one number
    var isNumberValid = /\d/.test(password);
    numberRequirement.classList.toggle('valid', isNumberValid);
    numberRequirement.textContent = isNumberValid ? "✔️ At least one number" : "❌ At least one number";

    // Check if password contains at least one special character
    var isSpecialCharValid = /[@$!%*?&\-]/.test(password);  // Updated regex to allow hyphen
    specialRequirement.classList.toggle('valid', isSpecialCharValid);
    specialRequirement.textContent = isSpecialCharValid ? "✔️ At least one special character" : "❌ At least one special character";

    // If the password doesn't meet the requirements, stop form submission
    if (!isLengthValid || !isUppercaseValid || !isLowercaseValid || !isNumberValid || !isSpecialCharValid) {
        alert("Password does not meet the requirements. Please ensure it has at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.");
        return;
    }

    // Check if a new logo is selected or if no logo is provided and an existing logo is already there
    var fileInput = document.getElementById('logo');
    var file = fileInput.files[0];
    if (!file && !document.getElementById('logo-preview').src) {
        alert('Please select a file or use the existing logo.');
        return;  // Prevent form submission
    }

    // If all validation passes, submit the form
    document.getElementById('updateForm').submit();
});




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




</body>
</html>
