<?php
date_default_timezone_set('Asia/Manila');

include 'connection.php';

$currentDate = date('Y-m-d');

$sql = "SELECT COUNT(*) as count FROM c3_request WHERE DATE(Date) = '$currentDate'";
$result = $conn->query($sql);

$requestCount = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $requestCount = $row['count'];
}

$latestRequestQuery = "SELECT ID, Responder, Barangay, Request, BStatus FROM c3_request WHERE DATE(Date) = '$currentDate' ORDER BY Date DESC LIMIT 1";
$latestRequestResult = $conn->query($latestRequestQuery);
$latestRequestRow = null;

if ($latestRequestResult->num_rows > 0) {
    $latestRequestRow = $latestRequestResult->fetch_assoc();
    $latestRequestId = $latestRequestRow['ID'];
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
        'responder' => $latestRequestRow ? $latestRequestRow['Responder'] : 'Unknown',
        'barangay' => $latestRequestRow ? $latestRequestRow['Barangay'] : 'Unknown',
        'request' => $latestRequestRow ? $latestRequestRow['Request'] : 'Unknown',
        'status' => $latestRequestRow ? $latestRequestRow['BStatus'] : 'Unknown'
    ]
]);
?>
