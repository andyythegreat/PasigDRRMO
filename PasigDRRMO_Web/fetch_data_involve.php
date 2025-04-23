<?php
include('connection.php');

$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

$sql = "SELECT 
            types.Involve AS Involve, 
            IFNULL(COUNT(incident.Involve), 0) AS count 
        FROM 
            (SELECT DISTINCT Involve FROM c3_locate WHERE YEAR(date) = $currentYear) AS types
        LEFT JOIN 
        c3_locate AS incident 
        ON 
            types.Involve = incident.Involve AND YEAR(incident.date) = $currentYear
        GROUP BY 
            types.Involve";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = array(
            'type' => $row["Involve"],
            'count' => $row["count"]
        );
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
