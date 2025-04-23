<?php
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$currentDate = date('Y-m-d');

$c3Sql = "SELECT COUNT(*) as count FROM c3_locate WHERE DATE(Date) = '$currentDate'";
$c3Result = $conn->query($c3Sql);

$c3ReportCount = 0;
if ($c3Result->num_rows > 0) {
    $c3Row = $c3Result->fetch_assoc();
    $c3ReportCount = $c3Row['count'];
}

$c3LatestReportQuery = "SELECT id, Caller, Location, Involve, Status FROM c3_locate WHERE DATE(Date) = '$currentDate' ORDER BY Date DESC LIMIT 1";
$c3LatestReportResult = $conn->query($c3LatestReportQuery);
$c3LatestReportRow = null;
$c3LastReportURL = null;

if ($c3LatestReportResult->num_rows > 0) {
    $c3LatestReportRow = $c3LatestReportResult->fetch_assoc();
    $c3LastReportURL = "BRGY_Ongoing.php?id=" . $c3LatestReportRow['id']; 
}

$conn->close();

echo json_encode([
    'count' => $c3ReportCount,
    'lastReportURL' => $c3LastReportURL,
    'latestReportDetails' => [
        'caller' => $c3LatestReportRow ? $c3LatestReportRow['Caller'] : 'Unknown',
        'location' => $c3LatestReportRow ? $c3LatestReportRow['Location'] : 'Unknown',
        'involve' => $c3LatestReportRow ? $c3LatestReportRow['Involve'] : 'Unknown',
        'status' => $c3LatestReportRow ? $c3LatestReportRow['Status'] : 'Unknown'
    ]
]);
