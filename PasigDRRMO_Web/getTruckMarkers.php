<?php
// Include database connection
require_once 'connection.php'; // Adjust the path to your database connection file

if (isset($_GET['ongoingID'])) {
    $ongoingID = $_GET['ongoingID'];
    $firetruckSql = "SELECT Username, RespondersBarangay, Latitude, Longitude FROM mobile_respond WHERE ongoingID = '$ongoingID'";
    $firetruckResult = $conn->query($firetruckSql);

    $firetruckLocations = [];
    if ($firetruckResult->num_rows > 0) {
        while ($firetruckRow = $firetruckResult->fetch_assoc()) {
            $firetruckLocations[] = [
                'Username' => $firetruckRow['Username'], 
                'RespondersBarangay' => $firetruckRow['RespondersBarangay'], 
                'lat' => (float)$firetruckRow['Latitude'],
                'lng' => (float)$firetruckRow['Longitude']
            ];
        }
    }

    // Return data as JSON
    header('Content-Type: application/json');
    echo json_encode($firetruckLocations);
    exit;
} else {
    // If ongoingID is not provided, return an error response
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing ongoingID']);
    exit;
}
?>
