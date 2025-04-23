<?php
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$currentDate = date('Y-m-d');

// Get the count of requests for the current date
$sql = "SELECT COUNT(*) as count FROM c3_request WHERE DATE(Date) = '$currentDate'";
$result = $conn->query($sql);

$requestCount = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $requestCount = $row['count'];
}

$latestRequestQuery = "SELECT r.ID, r.Responder, r.Barangay, r.Request, r.BStatus, m.OngoingID
                       FROM c3_request r
                       LEFT JOIN mobile_respond m ON r.OngoingID = m.OngoingID
                       WHERE DATE(r.Date) = '$currentDate' 
                       ORDER BY r.Date DESC LIMIT 1";

$latestRequestResult = $conn->query($latestRequestQuery);
$latestRequestRow = null;

if ($latestRequestResult->num_rows > 0) {
    $latestRequestRow = $latestRequestResult->fetch_assoc();
    $latestRequestId = $latestRequestRow['ID'];
    $ongoingID = $latestRequestRow['OngoingID'];
}

$lastRequestURL = null;
if ($latestRequestRow) {
    $lastRequestURL = "C3_Requests.php?id=" . $latestRequestRow['ID'];
}

$conn->close();

echo json_encode([
    'count' => $requestCount,
    'lastRequestURL' => $lastRequestURL,
    'latestRequestDetails' => [
        'OngoingID' => $ongoingID ? $ongoingID : null, // Pass the OngoingID here
        'responder' => $latestRequestRow ? $latestRequestRow['Responder'] : 'Unknown',
        'barangay' => $latestRequestRow ? $latestRequestRow['Barangay'] : 'Unknown',
        'request' => $latestRequestRow ? $latestRequestRow['Request'] : 'Unknown',
        'status' => $latestRequestRow ? $latestRequestRow['BStatus'] : 'Unknown'
    ]
]);
?>
