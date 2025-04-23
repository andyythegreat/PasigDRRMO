<?php
session_start();
include('connection.php');
$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

// Query to fetch the monthly fire alerts with the condition
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as alert_count 
        FROM brgy_locate 
        WHERE Caller = ? 
        GROUP BY DATE_FORMAT(date, '%Y-%m')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()) {
    $data[$row['month']] = $row['alert_count'];
}

$stmt->close();
$conn->close();

echo json_encode($data);
?>
