<?php
include('connection.php');


// Query to fetch the monthly fire alerts
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as alert_count 
        FROM c3_locate 
        GROUP BY DATE_FORMAT(date, '%Y-%m')";

        
$result = $conn->query($sql);

$data = [];
while($row = $result->fetch_assoc()) {
    $data[$row['month']] = $row['alert_count'];
}

$conn->close();

echo json_encode($data);
?>

