<?php
include('connection.php');

date_default_timezone_set('Asia/Manila');
$today = date("Y-m-d");


// brgy_cancel
$sqlCancel = "
    SELECT 
        ID 
    FROM 
        brgy_cancel 
    WHERE 
        DATE(Date) = '$today'
";

// brgy_helpout
$sqlHelpOut = "
    SELECT 
        ID 
    FROM 
        brgy_helpout 
    WHERE 
        DATE(Date) = '$today'
";

// brgy_report_update
$sqlReportUpdate = "
    SELECT 
        ID 
    FROM 
        brgy_report_update 
    WHERE 
        DATE(Date) = '$today'
";


$sqlMobileRespond = "
    SELECT 
        ID
    FROM
        mobile_respond
    WHERE
        DATE(DateForRequest) = '$today'
";


$sqlMobileRespondArrived = "
    SELECT 
        ID
    FROM
        mobile_respond
    WHERE
        DATE(TimeArrived) = '$today'
";

$sqlMobileRespondArriving = "
    SELECT 
        ID
    FROM
        mobile_respond
    WHERE
        DATE(TimeRespond) = '$today'
        AND RespondStatus = 'Arriving'
";





$resultCancel = $conn->query($sqlCancel);
$resultHelpOut = $conn->query($sqlHelpOut);
$resultReportUpdate = $conn->query($sqlReportUpdate);
$resultMobileRespond = $conn->query($sqlMobileRespond);
$resultMobileRespondArrived = $conn->query($sqlMobileRespondArrived);
$resultMobileRespondArriving = $conn->query($sqlMobileRespondArriving);


$fireAlerts = 0;


// Count results from brgy_cancel
if ($resultCancel->num_rows > 0) {
    $fireAlerts += $resultCancel->num_rows;
}

// Count results from brgy_helpout
if ($resultHelpOut->num_rows > 0) {
    $fireAlerts += $resultHelpOut->num_rows;
}

// Count results from brgy_report_update
if ($resultReportUpdate->num_rows > 0) {
    $fireAlerts += $resultReportUpdate->num_rows;
}

// Count results from brgy_report_update
if ($resultMobileRespond->num_rows > 0) {
    $fireAlerts += $resultMobileRespond->num_rows;
}

// Count results from brgy_report_update
if ($resultMobileRespondArrived->num_rows > 0) {
    $fireAlerts += $resultMobileRespondArrived->num_rows;
}

// Count results from mobile_respond for Arriving
if ($resultMobileRespondArriving->num_rows > 0) {
    $fireAlerts += $resultMobileRespondArriving->num_rows;
}






// Return the fire alerts count
echo $fireAlerts;
?>
