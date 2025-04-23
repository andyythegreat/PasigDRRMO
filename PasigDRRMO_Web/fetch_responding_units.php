<?php
include 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT Username AS Responder, RespondersBarangay AS Barangay, TimeRespond, TimeArrived, RespondStatus AS Status 
            FROM mobile_respond 
            WHERE OngoingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $respondingUnits = [];
    while ($row = $result->fetch_assoc()) {
        $respondingUnits[] = [
            'Responder' => $row['Responder'],
            'Barangay' => $row['Barangay'],
            'TimeRespond' => $row['TimeRespond'] ?? 'N/A',
            'TimeArrived' => $row['TimeArrived'] ?? 'N/A',
            'Status' => $row['Status']
        ];
    }

    echo json_encode($respondingUnits);
    $stmt->close();
    $conn->close();
}
?>
