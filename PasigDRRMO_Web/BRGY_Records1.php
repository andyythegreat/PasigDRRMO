<?php
session_start();
include 'connection.php';
date_default_timezone_set('Asia/Manila');

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PCDRRMO | BRGY Records </title>
    <link rel="stylesheet" href="BRGY_Records11.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>

<!-- navbar -->
<nav class="navbar">
    <div class="logo_item">
        <img src="images/PCDRRMO_LOGO1.png" alt=""></i>Pasig City DRRMO 
    </div>

    <div class="navbar_content">
        <h2>


        </h2>
    </div>
</nav>

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
        <a href="BRGY_Ongoing.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Ongoing.png" class="navlink_image">
            </span>
            <span class="navlink">Ongoing</span>
        </a>
    </li>
    <!-- COMPLETED -->
    <li class="item">
        <a href="BRGY_Completed.php" class="nav_link submenu_item">
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
        <a href="BRGY_File.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/File.png" class="navlink_image">
            </span>
            <span class="navlink">File</span>
        </a>
    </li>
    <!-- RECORDS -->
    <li class="item">
        <a href="BRGY_Records.php" class="nav_link active">
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

<!-- OVERVIEW -->
<ul class="menu_items">
    <div class="menu_title menu_overview"></div>
    <!-- DATA ANALYTICS -->
    <li class="item">
        <a href="BRGY_DataAnalytics.php" class="nav_link submenu_item">
            <span class="navlink_icon">
                <img src="images/Data_Analytics.png" class="navlink_image">
            </span>
            <span class="navlink">Analytics</span>
        </a>
    </li>
    <!-- REPORTS -->
    <li class="item">
        <a href="BRGY_Reports.php" class="nav_link">
            <span class="navlink_icon">
                <img src="images/Reports.png" class="navlink_image">
            </span>
            <span class="navlink">Reports</span>
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
    if(isset($_POST['updateId']) && isset($_POST['updateReference']) && isset($_POST['updateDate']) && isset($_POST['updateLocation']) && isset($_POST['updateInvolved']) && isset($_POST['updateTimeReceived']) && isset($_POST['updateTimeFinish'])) {
        
        $id = $_POST['updateId'];
        $reference = $_POST['updateReference'];
        $date = $_POST['updateDate'];
        $location = $_POST['updateLocation'];
        $involved = $_POST['updateInvolved'];
        $timeReceived = $_POST['updateTimeReceived'];
        $timeFinish = $_POST['updateTimeFinish'];


        $username = $_SESSION["Username"];
        $position = $_SESSION["Position"];
        
        $updateQuery = "UPDATE brgy_incidentreport SET Reference='$reference', Date='$date', Location='$location', Involve='$involved', Time_Received='$timeReceived', Time_Finish='$timeFinish' WHERE ID='$id'";

        
        $timestamp = date("Y-m-d H:i:s");
        $action = "Updated a record";
        $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);
    
        $success = $stmt2->execute();

        if ($conn->query($updateQuery) === TRUE) {
            // Alert message for successful update
            echo "<script>alert('Record updated successfully');</script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "All fields are required";
    }
}


if (isset($_GET['archive_id'])) {
    $archive_id = $_GET['archive_id'];

    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];
    $archive_sql = "INSERT INTO brgy_archiverecords (ID, Reference, Date, Location, Involve, Time_Received, Time_Finish)
    SELECT ID, Reference, Date, Location, Involve, Time_Received, Time_Finish
    FROM brgy_incidentreport
    WHERE ID = $archive_id
    ";

    $timestamp = date("Y-m-d H:i:s");
    $action = "Archived a file";
    $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);

    $success = $stmt2->execute();

    if ($conn->query($archive_sql) === TRUE) {
        $delete_sql = "DELETE FROM brgy_incidentreport WHERE ID = $archive_id";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<script>alert('Record archived successfully!');</script>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Error archiving record: " . $conn->error;
    }
}

$sql = "SELECT ID, Reference, Date, Type_Accident, Location, Barangay, Involve, Time_Received, Dispatch_Time, Arrived, Time_Finish, Homebase, Action_Taken, Remarks, Resources, Team_Leader, Driver, Fire_Responder FROM brgy_incidentreport";
$result = $conn->query($sql);

$conn->close();



?>


<!-- Home Section -->
<section class="home-section">
    <h1>FIRE ALARM RECORDS</h1>

    <div class="account-container">
    <div class="add-account">
        <a href="BRGY_ArchivedRecords.php" class="add-account-link"> Archived
        </a>
    </div>
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

    <div class="select-group">
        <label for="involved">INVOLVED:</label>
        <select id="involved">
            <option value="All">All</option>
            <option value="residential">Residential</option>
            <option value="electrical"> Electrical </option>
            <option value="post"> Post </option>
            <option value="commerical"> Commercial </option>
            <option value="industrial"> Industrial </option>
            <option value="grass"> Grass </option>
            <option value="rubbish"> Rubbish </option>
            <option value="vehicular"> Vehicular </option>
        </select>
    </div>
    </div>

    <script>
var yearDropdown = document.getElementById("year");
var quarterDropdown = document.getElementById("quarter");
var monthDropdown = document.getElementById("month");
var involvedDropdown = document.getElementById("involved");

yearDropdown.addEventListener("change", filterTable);
quarterDropdown.addEventListener("change", filterTable);
monthDropdown.addEventListener("change", filterTable);
involvedDropdown.addEventListener("change", filterTable);

function filterTable() {
    var selectedYear = yearDropdown.value;
    var selectedQuarter = quarterDropdown.value;
    var selectedMonth = monthDropdown.value;
    var selectedInvolved = involvedDropdown.value;

    var rows = document.querySelectorAll("tbody tr");

    rows.forEach(function(row) {
        var rowData = {
            year: parseInt(row.cells[2].innerText.split("-")[0]),
            month: parseInt(row.cells[2].innerText.split("-")[1]),
            quarter: getQuarter(parseInt(row.cells[2].innerText.split("-")[1])), 
            involved: row.cells[4].innerText
        };

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
        if (selectedInvolved !== "All" && rowData.involved !== selectedInvolved) {
            showRow = false;
        }

        if (showRow) {
            row.style.display = "table-row";
        } else {
            row.style.display = "none";
        }
    });
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


    <!-- Table -->
    <div class="table-container">
    <table>
        <thead>
            <tr>
            <th class="id-column">ID</th>
                <th>Reference No.</th>
                <th>Date</th>
                <th>Location</th>
                <th>Involved</th>
                <th>Time Received</th>
                <th>Time Finish</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
                $rows = array();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }
                    $rows = array_reverse($rows);
                    foreach ($rows as $row) {
                        echo "<tr>";
                        echo "<td class='id-column'><span>" . $row["ID"] . "</span></td>";
                        echo "<td>" . $row["Reference"] . "</td>";
                        echo "<td>" . $row["Date"] . "</td>";
                        echo "<td>" . $row["Location"] . "</td>";
                        echo "<td>" . $row["Involve"] . "</td>";
                        echo "<td>" . $row["Time_Received"] . "</td>";
                        echo "<td>" . $row["Time_Finish"] . "</td>";
                        echo "<td> 
                        <div class='btn-container'>
                        <button class='view-button' onclick='showDataInfo(this)'> <img src='images/view.png' alt='View Icon'> </button>
                        <button class='update-button' onclick='openUpdateForm(this)'> <img src='images/update.png' alt='Update Icon'> </button>
                        <button class='archive-button'><a href='BRGY_Records.php?archive_id=" . $row["ID"] . "' class='archive' type='submit'> <img src='images/Archive1.png' alt='Archive Icon'> 
                        </button>
                                </div>
                             </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No records found</td></tr>";
                }
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

<div class="overlay" id="overlay"></div>

<div class="popup">
    <button class="close-button" onclick="closePopup()">X</button>
    <h2>Data Information</h2>
    <div id="dataInfoPopup"></div>
    <div class="button-container">

    <input type="hidden" id="pdfId">

    <a id="pdfLink" target="_blank" href="#" class="view-button1">PDF</a>
    </div>
</div>


<div class="popup-update">
    <button class="close-button" onclick="closeUpdateForm()">X</button>
    <h2>Update Form</h2>
    <form id="updateForm" method="post" action="BRGY_Records.php"> <!-- You need to specify the action for the form -->
        <!-- Input fields for updating -->
        <input type="hidden" id="updateId" name="updateId">
<input type="hidden" id="updateReference" name="updateReference">
<label for="updateDate">Date:</label>
<input type="text" id="updateDate" name="updateDate">

<label for="updateLocation">Location:</label>
<input type="text" id="updateLocation" name="updateLocation">

<label for="updateInvolved">Involved:</label>
<input type="text" id="updateInvolved" name="updateInvolved">

<label for="updateTimeReceived">Time Received:</label>
<input type="text" id="updateTimeReceived" name="updateTimeReceived">

<label for="updateTimeFinish">Time Finish:</label>
<input type="text" id="updateTimeFinish" name="updateTimeFinish">


        <!-- Submit button -->
        <button type="submit" class="update-submit">Update</button>
    </form>
</div>

<script>
    // Hide the popup and overlay initially
    document.querySelectorAll('.popup').forEach(popup => popup.style.display = 'none');
    document.querySelector('.popup-update').style.display = 'none';

    document.getElementById('overlay').style.display = 'none';

    var rowData;

    function showDataInfo(button) {
    var row = button.closest('tr'); // Find the closest row to the clicked button

    // Get data from the row cells
    var reference = row.cells[1].innerText;
    var date = row.cells[2].innerText;
    var location = row.cells[3].innerText;
    var involved = row.cells[4].innerText;
    var timeReceived = row.cells[5].innerText;
    var timeFinish = row.cells[6].timeFinish;
    var remarks = row.cells[7].innerText;

    // Store the row data in the rowData variable
    rowData = {
        id: row.cells[0].innerText,
        reference: reference,
        date: date,
        location: location,
        involved: involved,
        timeReceived: timeReceived,
        timeFinish: timeFinish,
    };

    // Construct HTML for displaying data information
    var dataInfoHTML = `
        <p><strong>Reference:</strong> ${reference}</p>
        <p><strong>Date:</strong> ${date}</p>
        <p><strong>Location:</strong> ${location}</p>
        <p><strong>Involved:</strong> ${involved}</p>
        <p><strong>Call Received:</strong> ${timeReceived}</p>
        <p><strong>File Progress:</strong> ${timeFinish}</p>
    `;

    document.getElementById('dataInfoPopup').innerHTML = dataInfoHTML;
    document.querySelector('.popup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    logAction("Viewed data information");
}

function closePopup() {
        document.querySelectorAll('.popup').forEach(popup => popup.style.display = 'none');
        document.getElementById('overlay').style.display = 'none';
    }


function openUpdateForm(button) {
    var row = button.closest('tr');
    var id = row.cells[0].innerText;
    var reference = row.cells[1].innerText;
    var date = row.cells[2].innerText;
    var location = row.cells[3].innerText;
    var involved = row.cells[4].innerText;
    var timeReceived = row.cells[5].innerText;
    var timeFinish = row.cells[6].innerText;

    document.getElementById('updateId').value = id;
    document.getElementById('updateReference').value = reference;
    document.getElementById('updateDate').value = date;
    document.getElementById('updateLocation').value = location;
    document.getElementById('updateInvolved').value = involved;
    document.getElementById('updateTimeReceived').value = timeReceived;
    document.getElementById('updateTimeFinish').value = timeFinish;

    document.querySelector('.popup-update').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeUpdateForm() {
document.querySelector('.popup-update').style.display = 'none';
document.getElementById('overlay').style.display = 'none';
}







function logAction(action) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "log_action3.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (!response.success) {
                        console.error('Error logging action:', response.error);
                    }
                } else {
                    console.error('Error making request:', xhr.statusText);
                }
            }
        };

        xhr.send("action=" + encodeURIComponent(action));
    }

    document.getElementById('pdfLink').addEventListener('click', function() {
        logAction("Generated pdf file");
    });

</script>



</body>
</html>
