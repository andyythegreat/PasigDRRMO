<?php

include('connection.php');

$currentDate = date('Y-m-d');

session_start();
$currentUsername = $_SESSION['Username'];

// c3_locate 
$sql1 = "SELECT EventLog, Date, 'c3_locate' AS source FROM c3_locate WHERE DATE(Date) = '$currentDate'";
$result1 = $conn->query($sql1);

// c3_announcement 
$sql2 = "SELECT EventLog, Date, 'c3_announcement' AS source FROM c3_announcement WHERE DATE(Date) = '$currentDate'";
$result2 = $conn->query($sql2);

// brgy_accept 
$sql3 = "SELECT EventLog, Date, 'brgy_accept' AS source FROM brgy_accept WHERE DATE(Date) = '$currentDate' AND Caller = '$currentUsername'";
$result3 = $conn->query($sql3);

// brgy_decline
$sql4 = "SELECT EventLog, Date, 'brgy_decline' AS source FROM brgy_decline WHERE DATE(Date) = '$currentDate' AND Caller = '$currentUsername'";
$result4 = $conn->query($sql4);

// c3_request
$sql5 = "SELECT BStatus, Date, 'c3_request' AS source FROM c3_request WHERE DATE(Date) = '$currentDate'";
$result5 = $conn->query($sql5);

$notifications = [];

function formatTo12Hour($date) {
    return date("Y-m-d h:i:s A", strtotime($date));
}

// c3_locate
if ($result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $notifications[] = [
            'text' => $row['EventLog'] . ': ' . formatTo12Hour($row['Date']),
            'date' => formatTo12Hour($row['Date']),
            'source' => $row['source']
        ];
    }
}

// c3_announcement
if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $notifications[] = [
            'text' => $row['EventLog'] . ': ' . formatTo12Hour($row['Date']),
            'date' => formatTo12Hour($row['Date']),
            'source' => $row['source']
        ];
    }
}

// brgy_accept
if ($result3->num_rows > 0) {
    while ($row = $result3->fetch_assoc()) {
        $notifications[] = [
            'text' => $row['EventLog'] . ': ' . formatTo12Hour($row['Date']),
            'date' => formatTo12Hour($row['Date']),
            'source' => $row['source']
        ];
    }
}

// brgy_decline
if ($result4->num_rows > 0) {
    while ($row = $result4->fetch_assoc()) {
        $notifications[] = [
            'text' => $row['EventLog'] . ': ' . formatTo12Hour($row['Date']),
            'date' => formatTo12Hour($row['Date']),
            'source' => $row['source']
        ];
    }
}

// c3_request
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $notifications[] = [
            'text' => $row['BStatus'] . ': ' . formatTo12Hour($row['Date']),
            'date' => formatTo12Hour($row['Date']),
            'source' => $row['source']
        ];
    }
}

if (empty($notifications)) {
    $notifications[] = [
        'text' => "No notifications yet",
        'source' => ''
    ];
} else {
    usort($notifications, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

$conn->close();

echo json_encode($notifications);
?>
