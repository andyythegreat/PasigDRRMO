<?php
error_reporting(0); // Suppress PHP errors

include('connection.php');
require 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count 
        FROM brgy_locate ";

if ($currentYear !== date("Y")) {
    $sql .= " WHERE YEAR(date) = $currentYear";
}

$sql .= " GROUP BY DATE_FORMAT(date, '%Y-%m')";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$row["month"]] = $row["count"];
    }
}

$conn->close();

// Initialize categories
$categories = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];;

// Generate PDF
$options = new Options();
$options->set('chroot', realpath(__DIR__)); // Adjust this if needed
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
    padding: 8px;
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
        // Check if the image file exists
        $imagePath = realpath('images/brgy.png');
        if ($imagePath) {
            echo '<img src="' . $imagePath . '" alt="Logo">';
        } else {
            echo '<p>Image not found</p>';
        }
        ?>
    </div>
<div class="main-header">
  <div class="header">
    <h1>PCDRRMO - Pasig City Fire Rescue Section</h1>
    <h2>Incident Report for Year <?php echo $currentYear; ?></h2>
</div>
</div>

</div> <br> <br> 
    <table border="1">
        <thead>
            <tr>
                <th>Month</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $month): ?>
                <tr>
                    <td><?php echo $month; ?></td>
                    <td><?php echo isset($data[$currentYear . '-' . str_pad(array_search($month, $categories) + 1, 2, '0', STR_PAD_LEFT)]) ? $data[$currentYear . '-' . str_pad(array_search($month, $categories) + 1, 2, '0', STR_PAD_LEFT)] : 0; ?></td>
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
