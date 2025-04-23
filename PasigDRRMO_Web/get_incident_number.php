<?php
include 'connection.php'; 

$query = "SELECT IncidentNumber FROM c3_incidentreport ORDER BY ID DESC LIMIT 1";
$result = $conn->query($query);
$lastIncidentNumber = 'PFB-119-' . date('Y') . '-000';

if ($result && $row = $result->fetch_assoc()) {
    $lastIncidentNumber = $row['IncidentNumber'];
}

$parts = explode('-', $lastIncidentNumber);
$increment = (int)$parts[3] + 1;
$newIncidentNumber = 'PFB-119-' . date('Y') . '-' . str_pad($increment, 3, '0', STR_PAD_LEFT);

// Return the new incident number
echo json_encode(['newIncidentNumber' => $newIncidentNumber]);
