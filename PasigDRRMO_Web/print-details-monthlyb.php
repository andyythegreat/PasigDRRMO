<?php
error_reporting(0);

include('connection.php');
require 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date("m");

$sql_usernames = "SELECT Username FROM c3_addaccount WHERE Position = 'Barangay' AND Username LIKE 'BRGY_%' ORDER BY Username ASC";
$result_usernames = $conn->query($sql_usernames);

$categories = array();
if ($result_usernames->num_rows > 0) {
    while ($row = $result_usernames->fetch_assoc()) {
        $categories[] = $row["Username"];
    }
}

$sql = "SELECT 
            types.Barangay AS Barangay, 
            IFNULL(COUNT(incident.Barangay), 0) AS count 
        FROM 
            (SELECT DISTINCT Barangay FROM c3_locate) AS types
        LEFT JOIN 
            c3_locate AS incident 
        ON 
            types.Barangay = incident.Barangay";

$sql .= " WHERE YEAR(incident.date) = $currentYear AND MONTH(incident.date) = $currentMonth";

$sql .= " GROUP BY 
            types.Barangay";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$row["Barangay"]] = $row["count"];
    }
}

$conn->close();

foreach ($categories as $category) {
    if (!isset($data[$category])) {
        $data[$category] = 0;
    }
}

$options = new Options();
$options->set('chroot', realpath(__DIR__));
$dompdf = new Dompdf($options);
ob_start();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report PDF</title>
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

table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    border: 1px solid white;
    text-align: center;
    padding: 5px;
    background-color: #d9d9d9;
    color: black;
}

th {
    background-color: #3a61bb;
    color: white;
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
            <h1>PCDRRMO - Pasig City Fire Rescue Section</h1>
            <h2>Incident Report for <?php echo date("F", mktime(0, 0, 0, $currentMonth, 10)); ?> <?php echo $currentYear; ?></h2>
        </div>
    </div> 
</div> <br> <br>

    <table border="1">
        <thead>
            <tr>
                <th>Type</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo $category; ?></td>
                    <td><?php echo isset($data[$category]) ? $data[$category] : 0; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table> 

</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF directly to the browser
$dompdf->stream("incident_report_$currentYear.pdf", array("Attachment" => false));
?>
