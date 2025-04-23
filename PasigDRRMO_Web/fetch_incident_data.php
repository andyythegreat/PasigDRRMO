<?php
// Include your database connection
include 'connection.php';

if (isset($_POST['pdfId'])) {
    $pdfId = $_POST['pdfId'];

    // Query to fetch the data for the specific ID
    $query = "SELECT IncidentType, IncidentNumber, DateTime, Location, Barangay, Caller, Date, Time, Dispatcher, Remarks 
              FROM c3_incidentreport 
              WHERE ID = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $pdfId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Decode the comma-separated values into arrays
            $dates = explode(", ", $row['Date']);
            $times = explode(", ", $row['Time']);
            $dispatchers = explode(", ", $row['Dispatcher']);
            $remarks = explode(", ", $row['Remarks']);
            
            // Reformat the data for JSON output
            $row['Date'] = $dates;
            $row['Time'] = $times;
            $row['Dispatcher'] = $dispatchers;
            $row['Remarks'] = $remarks;

            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'No record found']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Query failed']);
    }

    $conn->close();
}
?>
