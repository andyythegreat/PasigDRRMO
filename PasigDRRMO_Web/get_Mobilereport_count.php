<?php
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$currentDate = date('Y-m-d');

// Query to get the count of reports for the current date
$sql = "SELECT COUNT(*) as count FROM mobilelocate WHERE DATE(Date) = '$currentDate'";
$result = $conn->query($sql);

$reportCount = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $reportCount = $row['count'];
}

// Query to get the latest report details
$latestReportQuery = "SELECT id, Caller, Location, Involve, Status FROM mobilelocate WHERE DATE(Date) = '$currentDate' ORDER BY Date DESC LIMIT 1";
$latestReportResult = $conn->query($latestReportQuery);
$latestReportRow = null;

if ($latestReportResult->num_rows > 0) {
    $latestReportRow = $latestReportResult->fetch_assoc();
}

// Construct the URL for the latest report if it exists
$lastMobileReportURL = null; 
if ($latestReportRow) {
    $lastMobileReportURL = "C3_MReports.php?id=" . $latestReportRow['id']; // Adjust the URL structure as needed
}

// Close the database connection
$conn->close();

// Return the count and the latest report details in JSON format
echo json_encode([
    'count' => $reportCount,
    'lastReportURL' => $lastMobileReportURL,
    'latestReportDetails' => [ // Changed key to 'latestReportDetails' for consistency
        'caller' => $latestReportRow ? $latestReportRow['Caller'] : 'Unknown',
        'location' => $latestReportRow ? $latestReportRow['Location'] : 'Unknown',
        'involve' => $latestReportRow ? $latestReportRow['Involve'] : 'Unknown',
        'status' => $latestReportRow ? $latestReportRow['Status'] : 'Unknown'
    ]
]);
?>
