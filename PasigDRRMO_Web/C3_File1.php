<?php
session_start();
include 'connection.php';
date_default_timezone_set('Asia/Manila');
$referenceNumber = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | C3 File </title>
    <link rel="stylesheet" href="C3_File111.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>

<!-- navbar -->
<nav class="navbar">
    <div class="logo_item">
        <img src="images/PCDRRMO_LOGO1.png" alt=""></i>Pasig City DRRMO
    </div>


    <div class="navbar_content">
        <button class="btn" id="notifBell">
            <img src="images/Notif_Bell.png">
            <span id="notifCount" class="notif-count"> 99+ </span>
        </button>
        <img src="images/PCDRRMO_LOGO3.png" alt="Logo" class="logo2" id="customLogo" onclick="changeProfile()">
        <input type="file" id="fileInput" style="display: none;" accept="image/*">
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

<script>
    function changeProfile() {
    document.getElementById("fileInput").click();
    }

    document.getElementById("fileInput").addEventListener("change", function(event) { 
    var file = event.target.files[0];
    var reader = new FileReader();

    reader.onload = function() {
        document.getElementById("customLogo").src = reader.result;
    };

    if (file) {
        reader.readAsDataURL(file);
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
        <a href="C3_File.php" class="nav_link active">
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
        <a href="C3_Barangay.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Barangay.png" class="navlink_image">
            </span>
            <span class="navlink">Barangay</span>
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
            <span class="navlink">Reports</span>
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = isset($_POST['Date']) ? $_POST['Date'] : null;
    $type = isset($_POST['Type_Accident']) ? $_POST['Type_Accident'] : null;
    $location = isset($_POST['Location']) ? $_POST['Location'] : null;
    $barangay = isset($_POST['Barangay']) ? $_POST['Barangay'] : null;
    $involve = isset($_POST['Involve']) ? $_POST['Involve'] : null;
    $call_received_time = isset($_POST['Time_Received']) ? $_POST['Time_Received'] : null;
    $dispatch_time = isset($_POST['Dispatch_Time']) ? $_POST['Dispatch_Time'] : null;
    $arrived_time = isset($_POST['Arrived']) ? $_POST['Arrived'] : null;
    $time_finish = isset($_POST['Time_Finish']) ? $_POST['Time_Finish'] : null;
    $homebase = isset($_POST['Homebase']) ? $_POST['Homebase'] : null;
    $file_progress = isset($_POST['Action_Taken']) ? $_POST['Action_Taken'] : null;
    $remarks = isset($_POST['Remarks']) ? $_POST['Remarks'] : null;
    $resources = isset($_POST['Resources']) ? $_POST['Resources'] : null;
    $team_leader = isset($_POST['Team_Leader']) ? $_POST['Team_Leader'] : null;
    $drivers = isset($_POST['Driver']) && is_array($_POST['Driver']) ? implode(", ", $_POST['Driver']) : null;
    $fire_responders = isset($_POST['Fire_Responder']) && is_array($_POST['Fire_Responder']) ? implode(", ", $_POST['Fire_Responder']) : null;

    $username = isset($_SESSION["Username"]) ? $_SESSION["Username"] : null;
    $position = isset($_SESSION["Position"]) ? $_SESSION["Position"] : null;

    $sql_last_reference = "SELECT MAX(Reference) AS last_reference FROM c3_incidentreport";
    $result_last_reference = $conn->query($sql_last_reference);
    $row_last_reference = $result_last_reference->fetch_assoc();
    $last_reference = $row_last_reference['last_reference'];

    if ($last_reference !== null) {
        $number_part = (int) substr($last_reference, 3);
        $number_part++;
        $referenceNumber = 'PC3' . str_pad($number_part, 11, "0", STR_PAD_LEFT);
    } else {
        $referenceNumber = 'PC300000000001';
    }

    $sql_insert = "INSERT INTO c3_incidentreport (Reference, Date, Type_Accident, Location, Barangay, Involve, Time_Received, Dispatch_Time, Arrived, Time_Finish, Homebase, Action_Taken, Remarks, Resources, Team_Leader, Driver, Fire_Responder) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssssssssssssssss", $referenceNumber, $date, $type, $location, $barangay, $involve, $call_received_time, $dispatch_time, $arrived_time, $time_finish, $homebase, $file_progress, $remarks, $resources, $team_leader, $drivers, $fire_responders);

    if ($stmt_insert->execute()) {
        echo "Record inserted successfully.";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }

    $timestamp = date("Y-m-d H:i:s");
    $action = "Inserted a file";
    $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);
    $stmt2->execute();

    $stmt_insert->close();
    $stmt2->close();
    $conn->close();
} else {
    $sql_last_reference = "SELECT MAX(Reference) AS last_reference FROM c3_incidentreport";
    $result_last_reference = $conn->query($sql_last_reference);
    $row_last_reference = $result_last_reference->fetch_assoc();
    $last_reference = $row_last_reference['last_reference'];

    if ($last_reference !== null) {
        $number_part = (int) substr($last_reference, 3);
        $number_part++;
        $referenceNumber = 'PC3' . str_pad($number_part, 11, "0", STR_PAD_LEFT);
    } else {
        $referenceNumber = 'PC300000000001';
    }
}
?>



<!-- Home Section -->
<section class="home-section">

<form action="C3_File1.php" method="POST" onsubmit="return submitForm();">
        <h1>INCIDENT REPORT</h1>

        <div class="home-container">  
        <h4> Reference number: <?php echo $referenceNumber; ?> </h4>

            <label for="Date">Date:</label>
            <input type="date" id="Date" name="Date"> <br>

            <div class="Accident-Time-container">
    <div>
        <label for="accidentType">Type of Accident:</label>
        <input type="text" id="accidentType" name="Type_Accident">
    </div>
    <div>
        <label for="timeReceived">Time Received:</label>
        <input type="time" id="timeReceived" name="Time_Received">
    </div> 
</div>
<br>

<div class="location-barangay-container">
    <div>
        <label for="Location">Location:</label>
        <input type="text" id="Location" name="Location">
    </div>
    <div>
        <label for="Barangay">Barangay:</label>
        <select id="Barangay" name="Barangay">
            <option value="" selected disabled>Choose Barangay</option>
            <option value="BRGY_BAGONGILOG">BRGY. BAGONG ILOG</option>
            <option value="BRGY_BAGONGKATIPUNAN">BRGY. BAGONG KATIPUNAN</option>
            <option value="BRGY_BAMBANG">BRGY. BAMBANG</option>
            <option value="BRGY_BUTING">BRGY. BUTING</option>
            <option value="BRGY_CANIOGAN">BRGY. CANIOGAN</option>
            <option value="BRGY_DELAPAZ">BRGY. DELAPAZ</option>
            <option value="BRGY_KALAWAAN">BRGY. KALAWAAN</option>
            <option value="BRGY_KAPASIGAN">BRGY. KAPASIGAN</option>
            <option value="BRGY_KAPITOLYO">BRGY. KAPITOLYO</option>
            <option value="BRGY_MALINAO">BRGY. MALINAO</option>
            <option value="BRGY_MANGGAHAN">BRGY. MANGGAHAN</option>
            <option value="BRGY_MAYBUNGA">BRGY. MAYBUNGA</option>
            <option value="BRGY_ORANBO">BRGY. ORANBO</option>
            <option value="BRGY_PALATIW">BRGY. PALATIW</option>
            <option value="BRGY_PINAGBUHATAN">BRGY. PINAGBUHATAN</option>
            <option value="BRGY_PINEDA">BRGY. PINEDA</option>
            <option value="BRGY_ROSARIO">BRGY. ROSARIO</option>
            <option value="BRGY_SAGAD">BRGY. SAGAD</option>
            <option value="BRGY_SANANTONIO">BRGY. SAN ANTONIO</option>
            <option value="BRGY_SANJOSE">BRGY. SAN JOSE</option>
            <option value="BRGY_SANMIGUEL">BRGY. SAN MIGUEL</option>
            <option value="BRGY_SANNICOLAS">BRGY. SAN NICOLAS</option>
            <option value="BRGY_STACRUZ">BRGY. STA CRUZ</option>
            <option value="BRGY_SANTALUCIA">BRGY. SANTA LUCIA</option>
            <option value="BRGY_SANTAROSA">BRGY. SANTA ROSA</option>
            <option value="BRGY_SANTOTOMAS">BRGY. SANTO TOMAS</option>
            <option value="BRGY_SANTOLAN">BRGY. SANTOLAN</option>
            <option value="BRGY_SUMILANG">BRGY. SUMILANG</option>
            <option value="BRGY_UGONG">BRGY. UGONG</option>
        </select>
    </div>
</div>
<br>

<label for="Involve">Involve:</label>
<select id="Involve" name="Involve" class="Involve">
    <option value="not_specified">Not specified by caller</option>
    <option value="electrical">Electrical</option>
    <option value="post">Post</option>
    <option value="residential">Residential</option>
    <option value="commercial">Commercial</option>
    <option value="industrial">Industrial</option>
    <option value="grass">Grass</option>
    <option value="rubbish">Rubbish</option>
    <option value="vehicular">Vehicular</option>
</select>
<br>

<div class="Time-container">
    <div>
        <label for="dispatch">Dispatch Time:</label>
        <input type="time" id="dispatch" name="Dispatch_Time">
    </div>
    <div>
        <label for="arrived">Arrived:</label>
        <input type="time" id="arrived" name="Arrived">
    </div>
    <div> 
        <label for="time_finish">Time Finish:</label>
        <input type="time" id="time_finish" name="Time_Finish">  
    </div>
</div>
<br>

<label for="homebase">Homebase:</label>
<input type="text" id="homebase" name="Homebase">
<br>

<label for="action_taken">Action Taken:</label>
<textarea id="action_taken" name="Action_Taken"></textarea>
<br>

<label for="remarks">Remarks:</label>
<textarea id="remarks" name="Remarks"></textarea>
<br>

<label for="resources">Resources:</label>
<textarea id="resources" name="Resources"></textarea>
<br>

<h2>List of Personnel:</h2>

<label for="team_leader">Team Leader:</label>
<input type="text" id="team_leader" name="Team_Leader">
<br>


    <div class="location-barangay-container">
        <div class="input-container">
            <div class="input-group" id="driverContainer">
                <label for="Driver">Driver/s:</label>
                <input type="text" class="driver-input" name="Driver[]">
        </div> <br>
            <div class="button-container"> 
                <button type="button" class="add-button" onclick="addInput('driverContainer')"> <img src="images/Add.png" alt="Add Icon"> Add One (1)</button>
            </div>
        </div>

        <div class="input-container">
    <div class="input-group" id="fireRespondersContainer">
        <label for="Fire_responder">Fire Responders:</label>
        <input type="text" class="fire-responder-input" name="Fire_Responder[]">
    </div> <br>
    <div class="button-container">
        <button type="button" class="add-button" onclick="addInput('fireRespondersContainer')"> <img src="images/Add.png" alt="Add Icon"> Add One (1)</button>
    </div>
</div>

    </div>

    <script>
        function submitForm() {
        alert("I have completed filling out the form.");
        return true; // Allow the form submission
    }

        function addInput(containerId) {
        const container = document.getElementById(containerId);
        
        // Create a new input element
        const newInput = document.createElement('input');
        newInput.setAttribute('type', 'text');
        newInput.setAttribute('class', `${containerId === 'driverContainer' ? 'driver-input' : 'fire-responder-input'}`);
        newInput.setAttribute('name', `${containerId === 'driverContainer' ? 'Driver[]' : 'Fire_Responder[]'}`);

        // Apply margin-top style
        newInput.style.marginTop = '15px'; 
        
        // Create a remove button
        const removeButton = document.createElement('button');
        removeButton.setAttribute('type', 'button');
        removeButton.textContent = 'Remove';
        removeButton.style.marginRight = '10px'; 
        removeButton.style.marginTop = '15px'; 
        removeButton.style.padding = '5px 10px'; 
        removeButton.style.float = 'left'; 
        removeButton.onclick = function() {
            container.removeChild(newInput);
            container.removeChild(removeButton);
        };
        
        // Append the new input field and remove button to the container
        container.appendChild(newInput);
        container.appendChild(removeButton);
    }
    </script>


        </div> <br>
        
        <button type="submit">Submit</button>
    </form>

    </section>

</body>
</html>
