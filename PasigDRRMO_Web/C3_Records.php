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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | C3 Records </title>
    <link rel="stylesheet" href="C3_Records55.css">
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
        <a href="C3_Records.php" class="nav_link active">
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

<!-- Home Section -->
<section class="home-section">
    <h1>FIRE ALARM RECORDS</h1>

    
    <div class="account-container">
    <div class="add-account">
        <a href="C3_ArchivedRecords.php" class="add-account-link"> Archived
        </a>
    </div>
    </div>

    <div class="main-container">
    <div class="search-container">
        <label for="search">Search by Incident Number:</label>
        <input type="text" id="search" placeholder="Search">
    </div>

    <!-- Select -->
    <div class="container-select">
    <div class="select-group">
    <label for="year">YEAR:</label>
    <select id="year">
    <option value="All">All</option>

    </select>

    <script>
        // Define the start year
        var startYear = 2024;
  
        // Get the current year
        var currentYear = 2500;
  
        // Select the year dropdown element
        var yearDropdown = document.getElementById("year");
  
        // Loop to generate options from the start year to the current year
        for (var i = startYear; i <= currentYear; i++) {
        var option = document.createElement("option");
        option.text = i;
        option.value = i;
        yearDropdown.appendChild(option);
    }
    </script>


    </div>

    <div class="select-group">
        <label for="quarter">QUARTER:</label>
        <select id="quarter">
            <option value="All">All</option>
            <option value="FirstQuarter">First Quarter</option>
            <option value="SecondQuarter">Second Quarter</option>
            <option value="ThirdQuarter">Third Quarter</option>
            <option value="FourthQuarter">Fourth Quarter</option>
        </select>
    </div>

    <div class="select-group">
        <label for="month">MONTH:</label>
        <select id="month">
            <option value="All">All</option>
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
    </div>
    </div>
    </div>

<script>
    var yearDropdown = document.getElementById("year");
    var quarterDropdown = document.getElementById("quarter");
    var monthDropdown = document.getElementById("month");
    var searchInput = document.getElementById("search");

    yearDropdown.addEventListener("change", filterTable);
    quarterDropdown.addEventListener("change", filterTable);
    monthDropdown.addEventListener("change", filterTable);
    searchInput.addEventListener("input", filterTable);

    function filterTable() {
        var selectedYear = yearDropdown.value;
        var selectedQuarter = quarterDropdown.value;
        var selectedMonth = monthDropdown.value;
        var searchValue = searchInput.value.toLowerCase();

        var rows = document.querySelectorAll("tbody tr:not(#no-records)");
        var visibleRowCount = 0;

        var noRecordsRow = document.getElementById("no-records");
        if (noRecordsRow) {
            noRecordsRow.remove();
        }

        rows.forEach(function (row) {
            var dateText = row.cells[1].innerText;
            var rowData = {
                year: null,
                month: null,
                quarter: null,
                incidentNumber: row.cells[0].innerText.toLowerCase(),
            };

            var dateParts = dateText.split(" ")[0].split("-");
            if (dateParts.length >= 2) {
                rowData.year = parseInt(dateParts[0]);
                rowData.month = parseInt(dateParts[1]);
                rowData.quarter = getQuarter(rowData.month);
            }

            var showRow = true;

            if (selectedYear !== "All" && rowData.year !== parseInt(selectedYear)) {
                showRow = false;
            }
            if (selectedQuarter !== "All" && rowData.quarter !== selectedQuarter) {
                showRow = false;
            }
            if (selectedMonth !== "All" && rowData.month !== parseInt(selectedMonth)) {
                showRow = false;
            }
            if (searchValue && !rowData.incidentNumber.includes(searchValue)) {
                showRow = false;
            }

            if (showRow) {
                row.style.display = "table-row";
                visibleRowCount++;
            } else {
                row.style.display = "none";
            }
        });

        if (visibleRowCount === 0) {
            var tbody = document.querySelector("tbody");
            var noRecordRow = document.createElement("tr");
            noRecordRow.id = "no-records";
            noRecordRow.innerHTML = "<td colspan='6' style='text-align: center;'>No records found</td>";
            tbody.appendChild(noRecordRow);
        }
    }

    function getQuarter(month) {
        if (month >= 1 && month <= 3) {
            return "FirstQuarter";
        } else if (month >= 4 && month <= 6) {
            return "SecondQuarter";
        } else if (month >= 7 && month <= 9) {
            return "ThirdQuarter";
        } else {
            return "FourthQuarter";
        }
    }
</script>




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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status_id'])) {
    include 'connection.php';

    $updateId = $_POST['update_status_id'];
    $incidentType = $_POST['Itype'];
    $incidentNumber = $_POST['Inumber'];
    $reported = $_POST['reported'];
    $location = $_POST['Location'];
    $barangay = $_POST['Barangay'];
    $caller = $_POST['phone'];

    $updateQuery = "UPDATE c3_incidentreport SET 
        IncidentType = ?, 
        IncidentNumber = ?, 
        DateTime = ?, 
        Location = ?, 
        Barangay = ?, 
        Caller = ? 
        WHERE ID = ?";

    if ($stmt = $conn->prepare($updateQuery)) {
        $stmt->bind_param("ssssssi", $incidentType, $incidentNumber, $reported, $location, $barangay, $caller, $updateId);
        
        if ($stmt->execute()) {
            echo "<script>alert('Record updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }

    $conn->close();
}


if (isset($_GET['archive_id'])) {
    $archive_id = $_GET['archive_id'];

    $archive_sql = "INSERT INTO c3_archiverecords (ID, IncidentType, IncidentNumber, DateTime, Location, Barangay, Caller, Date, Time, Dispatcher, Remarks)
    SELECT ID, IncidentType, IncidentNumber, DateTime, Location, Barangay, Caller, Date, Time, Dispatcher, Remarks
    FROM c3_incidentreport
    WHERE ID = $archive_id
    ";



    if ($conn->query($archive_sql) === TRUE) {
        $delete_sql = "DELETE FROM c3_incidentreport WHERE ID = $archive_id";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<script>alert('Record archived successfully!');</script>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Error archiving record: " . $conn->error;
    }
}

?>


<!-- Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Incident Number</th>
                <th>Date and Time Reported</th>
                <th>Location</th>
                <th>Barangay</th>
                <th>Phone Number</th>
                <th>Action</th>
            </tr>
        </thead>
 <tbody>
            <?php
            include 'connection.php';

            $query = "SELECT ID, IncidentType, IncidentNumber, DateTime, Location, Barangay, Caller, Date, Time, Dispatcher, Remarks FROM c3_incidentreport";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['ID'];
                    $incidentType = htmlspecialchars($row['IncidentType']);
                    $incidentNumber = htmlspecialchars($row['IncidentNumber']);
                    $dateTimeReported = htmlspecialchars($row['DateTime']);
                    $location = htmlspecialchars($row['Location']);
                    $barangay = htmlspecialchars($row['Barangay']);
                    $caller = htmlspecialchars($row['Caller']);

                    echo "<tr>";
                    echo "<td>$incidentNumber</td>";
                    echo "<td>$dateTimeReported</td>";
                    echo "<td>$location</td>";
                    echo "<td>$barangay</td>";
                    echo "<td>$caller</td>";
                    echo "<td>
                    
                    
                            <div class='btn-container'>
                                <button class='view-button' 
                                    onclick='showDataInfo(this)' 
                                    data-pdf-id='$id'>
                                    <img src='images/view.png' alt='View Icon'> 
                                </button>
                                
                                
                                
                            <button class='update-button' 
                                onclick='openUpdateForm(this)' 
                                data-status-id='$id' 
                                data-incident-type='".htmlspecialchars($row['IncidentType'])."' 
                                data-incident-number='".htmlspecialchars($row['IncidentNumber'])."' 
                                data-reported='".htmlspecialchars($row['DateTime'])."' 
                                data-location='".htmlspecialchars($row['Location'])."' 
                                data-barangay='".htmlspecialchars($row['Barangay'])."' 
                                data-phone='".htmlspecialchars($row['Caller'])."'>
                                <img src='images/update.png' alt='Update Icon'> 
                            </button>
                            
                            
                                <button class='archive-button'>
                                    <a href='C3_Records.php?archive_id=" . $row["ID"] . "' class='archive' type='submit'> 
                                        <img src='images/archive.png' alt='Archive Icon'> 
                                    </a>
                                </button>
                            </div>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No records found.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</div>

<!-- Data Information Popup -->
<div id="dataInfoPopup" class="fire-truck-popup"> 
<div class="fire-truck-popup-wrapper"> 
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Data Information</h2> 
            <span class="close" onclick="closePopup()">✖</span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
            <div id="dataInfoContent" class="info-content"></div>

            <div class="fire-truck-popup-buttons">
                <input type="hidden" id="pdfId">
                <a id="pdfLink" target="_blank" href="#" class="fire-truck-add">PDF</a>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function showDataInfo(button) {
    var pdfId = button.getAttribute('data-pdf-id'); // Retrieve pdfId from button attribute
    document.getElementById('pdfId').value = pdfId;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_incident_data.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var responseData = JSON.parse(xhr.responseText);
            
            var contentDiv = document.getElementById('dataInfoContent');
            contentDiv.innerHTML = `
                <p><strong>Incident Type:</strong> ${responseData.IncidentType}</p>
                <p><strong>Incident Number:</strong> ${responseData.IncidentNumber}</p>
                <p><strong>Date and Time Reported:</strong> ${responseData.DateTime}</p>
                <p><strong>Location:</strong> ${responseData.Location}</p>
                <p><strong>Barangay:</strong> ${responseData.Barangay}</p>
                <p><strong>Caller:</strong> ${responseData.Caller}</p>
                <p><strong>Date:</strong> ${responseData.Date.join(', ')}</p>
                <p><strong>Time:</strong> ${responseData.Time.join(', ')}</p>
                <p><strong>Dispatcher:</strong> ${responseData.Dispatcher.join(', ')}</p>
                <p><strong>Remarks:</strong> ${responseData.Remarks.join(', ')}</p>
            `;

            // Set the PDF link URL based on the pdfId
            var pdfLink = document.getElementById('pdfLink');
            pdfLink.href = "print-details2.php?pdf=true&id=" + pdfId;

            // Open the popup
            document.getElementById('dataInfoPopup').style.display = 'block';
        }
    };
    
    xhr.send('pdfId=' + encodeURIComponent(pdfId));
}


function closePopup() {
    document.getElementById('dataInfoPopup').style.display = 'none';
}


function closePopup() {
    const popup = document.getElementById('dataInfoPopup');
    popup.style.display = 'none';
}

function openPDF() {
    const pdfId = document.getElementById('pdfId').value; // Get the PDF ID
    const pdfUrl = `path/to/pdf/${pdfId}.pdf`;
    
    window.open(pdfUrl, '_blank'); // Open the PDF in a new tab
}
</script>

<!-- Update Popup -->
<div id="updateTruckPopup" class="fire-truck-popup"> 
<div class="fire-truck-popup-wrapper"> 
    <div class="fire-truck-popup-content">
        <div class="fire-truck-popup-header">
            <h2>Update Record</h2> 
            <span class="close" onclick="closeUpdatePopup()">✖</span>
        </div>
        <hr>

        <div class="fire-truck-popup-container">
            <form class="fire-truck-popup-form" id="updateTruckForm" method="POST" action="C3_Records.php">
                <input type="hidden" name="update_status_id" id="update_status_id"> 
                
                <div class="update-form-group">
                    <label for="Itype">Incident Type:</label>
                    <input type="text" id="Itype" name="Itype" required readonly>
                </div>

                <div class="update-form-group">
                    <label for="Inumber">Incident Number:</label>
                    <input type="text" id="Inumber" name="Inumber" required>
                </div>

                <div class="update-form-group">
                    <label for="reported">Date and Time Reported:</label>
                    <input type="datetime-local" id="reported" name="reported">
                </div>

                <div class="update-form-group">
                    <label for="Location">Location:</label>
                    <input type="text" id="Location" name="Location" required>
                </div>
           <div class="update-form-group">
                    <label for="Barangay">Barangay:</label>
                    <select id="Barangay" name="Barangay">
                        <option value="" selected disabled>Choose Barangay</option>
    <?php foreach ($usernames as $username): ?>
        <option value="<?php echo htmlspecialchars($username); ?>"><?php echo htmlspecialchars($username); ?></option>
    <?php endforeach; ?>                    
    </select>
                </div>

                <div class="update-form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" maxlength="13" placeholder="+63" oninput="validatePhoneNumber(this)" required>
                </div> <br>
                
                <div class="fire-truck-popup-buttons">
                    <button type="submit" class="fire-truck-add">Update</button> 
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
function validatePhoneNumber(input) {
    if (!input.value.startsWith("+63")) {
        input.value = "+63";
    }
    
    input.value = input.value.replace(/[^0-9+]/g, '');

    if (input.value.length > 13) {
        input.value = input.value.slice(0, 13);
    }
}
</script>

<script>
function openUpdateForm(button) {
    const id = button.getAttribute('data-status-id');
    const incidentType = button.getAttribute('data-incident-type');
    const incidentNumber = button.getAttribute('data-incident-number');
    const reported = button.getAttribute('data-reported');
    const location = button.getAttribute('data-location');
    const barangay = button.getAttribute('data-barangay');
    const phone = button.getAttribute('data-phone');

    document.getElementById('update_status_id').value = id;
    document.getElementById('Itype').value = incidentType;
    document.getElementById('Inumber').value = incidentNumber;
    document.getElementById('reported').value = reported;
    document.getElementById('Location').value = location;
    document.getElementById('phone').value = phone;

    const barangaySelect = document.getElementById('Barangay');
    for (let option of barangaySelect.options) {
        if (option.value === barangay) {
            option.selected = true;
            break;
        }
    }

    // Show the update popup
    document.getElementById('updateTruckPopup').style.display = 'block';
}


function closeUpdatePopup() {
    const popup = document.getElementById('updateTruckPopup');
    popup.style.display = 'none'; 
}

function validatePhoneNumber(input) {
    if (!input.value.startsWith("+63")) {
        input.value = "+63";
    }

    input.value = input.value.replace(/[^0-9+]/g, '');

    if (input.value.length > 13) {
        input.value = input.value.slice(0, 13);
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

</body>
</html>
