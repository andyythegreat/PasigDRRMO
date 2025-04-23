<?php
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$currentDate = date('Y-m-d');

$sql = "SELECT COUNT(*) as count FROM brgy_locate WHERE DATE(Date) = '$currentDate'";
$result = $conn->query($sql);

$reportCount = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $reportCount = $row['count'];
}

$latestReportQuery = "SELECT id, Caller, Location, Involve, Status FROM brgy_locate WHERE DATE(Date) = '$currentDate' ORDER BY Date DESC LIMIT 1";
$latestReportResult = $conn->query($latestReportQuery);
$latestReportRow = null;

if ($latestReportResult->num_rows > 0) {
    $latestReportRow = $latestReportResult->fetch_assoc();
    $latestReportId = $latestReportRow['id'];
}

$lastBarangayReportURL = null; 
if ($latestReportRow) {
    $lastBarangayReportURL = "C3_BReports.php?id=" . $latestReportRow['id'];
}

$conn->close();

echo json_encode([
    'count' => $reportCount,
    'lastReportURL' => $lastBarangayReportURL,
    'latestReportDetails' => [
        'caller' => $latestReportRow ? $latestReportRow['Caller'] : 'Unknown',
        'location' => $latestReportRow ? $latestReportRow['Location'] : 'Unknown',
        'involve' => $latestReportRow ? $latestReportRow['Involve'] : 'Unknown',
        'status' => $latestReportRow ? $latestReportRow['Status'] : 'Unknown'
    ]
]);

?>
