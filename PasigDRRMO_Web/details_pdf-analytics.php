<?php
include 'connection.php';

// Check if the ID parameter is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the specific record based on the provided ID
    $sql = mysqli_query($conn, "SELECT * FROM c3_locate WHERE ID = '$id'");
    $c3_locate = mysqli_fetch_assoc($sql);

    // Check if the record exists
    if ($c3_locate) {
        // Extract year from the date
        $year = date("Y", strtotime($c3_locate['Date']));

        // Fetch incident counts for each type for the specified year
        $sql = "SELECT 
                    Involve, 
                    COUNT(*) AS count 
                FROM 
                    c3_locate 
                WHERE 
                    YEAR(Date) = $year 
                GROUP BY 
                    Involve";

        $result = $conn->query($sql);

        $counts = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['Involve']] = $row['count'];
            }
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details PDF</title>
</head>
<body>

    <style>
        .container {
            max-width: 100%; 
            padding: 10px; 
        }

        .container-details {
            width: 80%;
            height: 13%;
            margin-left: -60px;
            font-family: Arial, sans-serif;
            background-color: #062B82; 
            color: white;
            margin-bottom: 25px;
            border-top-right-radius: 55px; 
            border-bottom-right-radius: 55px;
            position: relative; 
        }

        .details h1{
            font-weight: bold;
            font-size: 50px; 
            margin: 10px;
            text-align: center;
        }

        .details h2 {
            font-weight: none;
            font-size: 30px;
            margin: -8px;
            text-align: center;
        }

        .text p {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.3;
            color: black; 
            margin-bottom: 30px;
        }

        table {
            font-family: Arial, sans-serif;
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 15px;
            border: 1px solid black;
            text-align: left;
            font-weight: bold;
        }

        .container-text {
            max-width: 75%;
        }

        .text1 {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #8F8F8F;
            margin-left: -30px;
            margin-bottom: 10px;
        }

        .bold-text {
            font-weight: bold;
        }

        .box {
            width: 1000px;
            background-color: #062B82;
            height: 35px;
            margin-left: -100px;
        }
    </style>

<div class="container">

    <div class="container-details">
        <div class="details"> 
            <h1><?php echo $year; ?></h1>
            <h2>INCIDENT REPORT DETAILS</h2>
        </div>
    </div>
    
    <table width="100%">
        <tr>
            <td><b>Grass:</b></td>
            <td><?php echo isset($counts['grass']) ? $counts['grass'] : 0; ?></td>
        </tr>
        <tr>
            <td><b>Residential:</b></td>
            <td><?php echo isset($counts['residential']) ? $counts['residential'] : 0; ?></td>
        </tr>
        <tr>
            <td><b>Commercial:</b></td>
            <td><?php echo isset($counts['commercial']) ? $counts['commercial'] : 0; ?></td>
        </tr>
    </table>

    <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>

    <div class="container-text">
        <div class="text1">
            <p> <span class="bold-text">Confidentiality Notice:</span> 
            This Fire Incident Report contains sensitive information intended solely for 
            internal use. Distribution, dissemination, or sharing of this document, in part or in whole, is strictly
            prohibited without prior authorization. Please treat this report with the utmost confidentiality 
            to uphold the integrity of our safety protocols and protect the privacy of individuals involved. 
            Thank you for your cooperation.</p>
        </div>
    </div>

    <div class="box"> </div>
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
