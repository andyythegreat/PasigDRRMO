<?php
session_start();
include 'connection.php';

$user = $_SESSION['Username'];


$user_query = mysqli_query($conn, "SELECT * FROM c3_addaccount WHERE Username = '$user'");
$user_info = mysqli_fetch_assoc($user_query);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = mysqli_query($conn, "SELECT * FROM c3_incidentreport WHERE ID = '$id'");
    $c3_incidentreport = mysqli_fetch_assoc($sql);

    // Check if the record exists
    if ($c3_incidentreport) {
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
    margin-top: -25px;
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



.center-content {
    text-align: center;
    margin: 0 auto;
}

.left-content {
    text-align: left; 
    margin: 0;
  }

table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 10px;
    margin-top: -10px;
}

table th, table td {
    border: 1px solid;
    text-align: center; 
}

.incident-report {
    border: none !important; 
    border-collapse: collapse;
    border-spacing: 0; 
    width: 100%;
    background-color: transparent; 
}

.incident-report th, 
.incident-report td {
    border: none !important; 
    background-color: transparent; 
    padding: 10px; 
    outline: none !important; 
}

table.incident-report th, 
table.incident-report td {
    border: 1px solid;
    text-align: left; 
    padding: 5px; 
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

  table th:nth-child(1), 
  table td:nth-child(1) {
    width: 15%; 
  }

  table th:nth-child(2), 
  table td:nth-child(2) {
    width: 15%; 
  }

  table th:nth-child(3), 
  table td:nth-child(3) {
    width: 15%; 
  }

  table th:nth-child(4),
  table td:nth-child(4) {
    width: 55%; 
  }

  table th.remarks-column,
  table td.remarks-column {
    text-align: left; 
    padding-left: 15px; 
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
    <div class="logo"> <br> <br>
    <?php
        $imagePath1 = realpath('images/PCDRRMO_LOGO2.png');
        if ($imagePath1) {
            echo '<img src="' . $imagePath1 . '" alt="Logo" style="width:60px">';
        } else {
            echo '<p>Image not found</p>';
        }
        
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        
        $imagePath2 = realpath('images/Pasig_Text.png');
        if ($imagePath2) {
            echo '<img src="' . $imagePath2 . '" alt="Logo" style="width:90px">';
        } else {
            echo '<p>Image not found</p>';
        }
    ?>
    </div>

    <div class="main-header"> 
        <div class="header">
            <h1>Disaster Risk Reduction and Management Office</h1>
            <h2 style="font-weight: 400;"><em>WARNING</em> Division-Command Communication and Control</h2>
            </div>
    </div>
</div> <br>

<div class="form-container">
<h3>INCIDENT REPORT</h3>
</div> <br> 

<div class="left-content">
  <table class="incident-report">
    <tbody>
      <tr>
        <th>Incident Type: </th>
        <td><?php echo $brgy_incidentreport['IncidentType']; ?></td> 
      </tr>
      <tr>
        <th>Incident No:</th>
        <td> <?php echo $brgy_incidentreport['IncidentNumber']; ?> </td> 
      </tr>
      <tr>
        <th>Date and Time Reported:</th>
        <td><?php echo $brgy_incidentreport['DateTime']; ?></td> 
      </tr>
      <tr>
        <th>Location:</th>
        <td> <?php echo $brgy_incidentreport['Location']; ?> </td> 
      </tr>
      <tr>
        <th>Caller:</th>
        <td> <?php echo $brgy_incidentreport['Caller']; ?></td> 
      </tr>
    </tbody>
  </table>
</div> <br>


<h4> Dispatcher Remarks </h4>
<div class="center-content">
<table>
    <tbody>
        <tr>
        <th class="date-column">Date</th>
        <th class="time-column">Time</th>
        <th class="dispatcher-column">Dispatcher</th>
        <th class="remarks-column">Remarks</th>
        </tr>
        <?php
        $dates = explode(", ", $c3_incidentreport['Date']);
        $times = explode(", ", $c3_incidentreport['Time']);
        $dispatchers = explode(", ", $c3_incidentreport['Dispatcher']);
        $remarks = explode(", ", $c3_incidentreport['Remarks']);

        $max_count = max(count($dates), count($times), count($dispatchers), count($remarks));

        for ($i = 0; $i < $max_count; $i++) {
            echo "<tr>";
            echo "<td>" . ($i < count($dates) ? $dates[$i] : "") . "</td>";
            echo "<td>" . ($i < count($times) ? $times[$i] : "") . "</td>";
            echo "<td>" . ($i < count($dispatchers) ? $dispatchers[$i] : "") . "</td>";
            echo "<td class='remarks-column'>" . ($i < count($remarks) ? $remarks[$i] : "") . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>

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