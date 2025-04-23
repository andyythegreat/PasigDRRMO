<?php

header('Content-Type: application/json');
include 'connection.php'; 

try {
    date_default_timezone_set('Asia/Manila');
    $current_date = date('Y-m-d');

    $sql = "
        SELECT m.Latitude, m.Longitude 
        FROM mobile_respond m
        JOIN c3_locate c ON m.OngoingID = c.ID
        WHERE c.Status != 'Resolved'
          AND DATE(c.resolved_time) = '$current_date';
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database Query Failed: " . $conn->error);
    }

    $locations = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
    }

    echo json_encode($locations);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();

?>
