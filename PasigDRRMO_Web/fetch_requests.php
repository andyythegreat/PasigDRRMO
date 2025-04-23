<?php
include 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT ID, Barangay, Responder, Request, BStatus
            FROM c3_request 
            WHERE OngoingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $requestDetails = [];
    while ($row = $result->fetch_assoc()) {
        $requestDetails[] = [
            'ID' => $row['ID'],
            'Barangay' => $row['Barangay'],
            'Responder' => $row['Responder'],
            'Request' => $row['Request'],
            'BStatus' => $row['BStatus']


        ];
    }

    echo json_encode($requestDetails);
    $stmt->close();
    $conn->close();
}
?>
