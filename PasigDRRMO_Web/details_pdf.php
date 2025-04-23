<?php
session_start();
include 'connection.php';

$user = $_SESSION['Username'];

$user_query = mysqli_query($conn, "SELECT * FROM c3_addaccount WHERE Username = '$user'");
$user_info = mysqli_fetch_assoc($user_query);

if ($user_info) {
    $user_logo_path = $user_info['Logo'];
} else {
    $user_logo_path = 'images/default_logo.png';
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = mysqli_query($conn, "SELECT * FROM brgy_incidentreport WHERE ID = '$id'");
    $brgy_incidentreport = mysqli_fetch_assoc($sql);

    if ($brgy_incidentreport) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Incident Report Form</title>
</head>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}





.container {
    width: 800px;
    background: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
}

.form-container {
    margin-top: -19px;
}

.main-header {
    width: 75%;
    background-color: #033679; 
    padding: 10px; 
    border-top-left-radius: 60px; 
    border-bottom-left-radius: 60px; 
    margin-left: 150px;
    margin-top: -150px;
    text-align: center; 
    display: flex;
    align-items: center;
}

.header {
    color: white;
    margin-top: -10px;
    margin-bottom: -10px;
    font-size: 10px;
    flex: 1;
}

.logo {
    flex-shrink: 0;
}

.logo img {
    width: 150px;
    height: auto;
    margin-top: -35px;
    margin-left: -30px;
}

h3 {
    text-align: center;
    font-size: 25px;
}

p {
    font-size: 15px;
    text-align: center;
}





table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 10px; 
}

table th, table td {
    border: 1px solid;
    text-align: left;
}

label {
    font-weight: bold;
}


th, td {
    width: 50%;
    text-align: left;
    padding: 5px;
    box-sizing: border-box;
}

thead th {
    background-color: #f2f2f2;
}

ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

ul::before {
    margin-right: 10px;
}

.no-border {
    border: none;
}

.balanced-margin td {
    padding: 10px 20px;
    text-align: center;
}

.balanced-margin p {
    margin: 1px 0 0;
    padding: 0;
}

.p1 {
    width: 350px;
}





.confidentiality-notice-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        margin: 20px 0;
    }
    
    .confidentiality-notice {
        flex: 1;
        width: 70%;
        font-family: Arial, sans-serif;
        font-size: 11.4px;
        margin-top: 5px;
        color: #8f8f8f;
    }
    
    .confidentiality-title {
        font-weight: bold;
        color: #8f8f8f;
    }

    .images-container {
        width: 30%;
        display: flex;
        margin-left: 530px;
        margin-top: -100px;
    }

    .blue-line {
        height: 38px;
        background-color: #033679;
        width: 1000px;
        margin-left: -100px;
        margin-top: -20px;
    }


</style>
<body>

<div class="container">
    <div class="logo">
    <?php
        // Use the user-specific logo path
        if (file_exists($user_logo_path)) {
            echo '<img src="' . $user_logo_path . '" alt="Logo">';
        } else {
            echo '<p>Image not found</p>';
        }
        ?>
    </div>
    <div class="main-header"> 
        <div class="header">
        <h1>PCDRRMO - <?php echo str_replace('BRGY_', 'BARANGAY ', htmlspecialchars($user)); ?> </h1>
        <h2>Fire And Rescue Section Incident Report</h2>
        </div>
    </div>
</div> <br>

<div class="form-container">
        <h3>INCIDENT REPORT</h3>
</div>

<table>
    <tbody>
        <tr>
        <td><label for="date">Date: <?php echo $brgy_incidentreport['Date']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td><label for="type">Type of Incident: <?php echo $brgy_incidentreport['Type_Accident']; ?></label></td>
            <td><label for="timeReceived">Time Received: <?php echo $brgy_incidentreport['Time_Received']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td><label for="location">Location: <?php echo $brgy_incidentreport['Location']; ?></label></td>
            <td><label for="barangay">Barangay: <?php echo $brgy_incidentreport['Barangay']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td><label for="involved">Involved: <?php echo $brgy_incidentreport['Involve']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td><label for="dispatched">Dispatched Time: <?php echo $brgy_incidentreport['Dispatch_Time']; ?></label></td>
            <td><label for="arrived">Arrived: <?php echo $brgy_incidentreport['Arrived']; ?></label></td>
            <td><label for="timeFinish">Time Finish: <?php echo $brgy_incidentreport['Time_Finish']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td><label for="homesave">Homebase: <?php echo $brgy_incidentreport['Homebase']; ?></label></td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th> <center> <label for="action">Action Taken </label> </center> </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td> <?php echo $brgy_incidentreport['Action_Taken']; ?> </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th> <center> <label for="remarks">Remarks/Recommendations </label> </center> </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td> <?php echo $brgy_incidentreport['Remarks']; ?> </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th> <center> <label for="resources">Resources </label> </center></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td> <?php echo $brgy_incidentreport['Resources']; ?> </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="2">List of Personnel</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan=2><label for="Team-Leader">Team Leader:  <span style="font-weight: normal;"> <?php echo $brgy_incidentreport['Team_Leader']; ?> </span> </label></td>
        </tr>
        <tr>
    <td class="driver/s">Driver/s</td>
    <td class="fire">Fire Responders</td>
</tr>
<?php
$drivers_array = explode(", ", $brgy_incidentreport['Driver']);
$fire_responders_array = explode(", ", $brgy_incidentreport['Fire_Responder']);
$max_count = max(count($drivers_array), count($fire_responders_array));

for ($i = 0; $i < $max_count; $i++) {
    echo "<tr>";
    echo "<td><ul>" . ($i < count($drivers_array) ? "<li>" . ($i + 1) . ". " . $drivers_array[$i] . "</li>" : "") . "</ul></td>";
    echo "<td><ul>" . ($i < count($fire_responders_array) ? "<li>" . ($i + 1) . ". " . $fire_responders_array[$i] . "</li>" : "") . "</ul></td>";
    echo "</tr>";
}
?>

    </tbody>
</table> <br>

<table>
    <tbody>
        <tr>
            <td class="no-border"><label for="preparedBy">Prepared by:</label></td>
            <td class="no-border"><label for="receivedBy">Received and verified by:</label></td>
        </tr>
    </tbody>
</table> <br>


<table class="balanced-margin">
        <tbody>
            <tr>
                <td class="no-border"><u>_______________________________</u> <p>JOHN ERNEST V. GUMARAM, EMT</p> <p>Fire and Rescue Section Chief</p> </td>
                <td class="no-border"><u>_______________________________</u><p class="p1">MARIA THERESA P. FRANCO / GINA A. LOPEZ</p> <p>Admin & Records Officer</p></td>
            </tr>
        </tbody>
    </table>


<table class="balanced-margin">
        <tbody>
            <tr>
                <td class="no-border"><u>_______________________________</u><p>FIRE SUPERVISOR / TEAM LEADER</p></td>
            </tr>
        </tbody>
</table>

</body>
</html>



<?php
    } else {
        echo "Record with ID $id not found.";
    }
} else {
    echo "ID parameter is missing.";
}
?>