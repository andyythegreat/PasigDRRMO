<?php
session_start();
include('connection.php');
$user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

$sql = "SELECT 
            types.Involve AS Involve, 
            IFNULL(COUNT(incident.Involve), 0) AS count 
        FROM 
            (SELECT DISTINCT Involve FROM brgy_locate WHERE YEAR(date) = ? AND Caller = ?) AS types
        LEFT JOIN 
        brgy_locate AS incident 
        ON 
            types.Involve = incident.Involve AND YEAR(incident.date) = ? AND incident.Caller = ?
        GROUP BY 
            types.Involve";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $currentYear, $user, $currentYear, $user);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = array(
            'type' => $row["Involve"],
            'count' => $row["count"]
        );
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
