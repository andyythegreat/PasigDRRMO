<?php
include 'connection.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'], $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status'])) {
    $currentDateTime = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO c3_locate (Caller, Location, Barangay, Involve, Status, Date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status'], $currentDateTime);
    
    if ($stmt->execute()) {
        $action_accept = "C3 has just approved your mobile report!";
        $status = 'Accepted';
        
        $stmt_accept = $conn->prepare("INSERT INTO brgy_accept (EventLog, Date, Caller, Location, Involve, Status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_accept->bind_param("ssssss", $action_accept, $currentDateTime, $data['caller'], $data['location'], $data['involve'], $status);
        
        if ($stmt_accept->execute()) {
            $deleteStmt = $conn->prepare("DELETE FROM mobilelocate WHERE Caller = ? AND Location = ? AND Barangay = ? AND Involve = ? AND Status = ?");
            $deleteStmt->bind_param("sssss", $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status']);
            
            if ($deleteStmt->execute()) {
                echo json_encode(['message' => 'Mobile alert accepted successfully']);
            } else {
                echo json_encode(['message' => 'Mobile alert accepted, but failed to delete from mobilelocate.']);
            }

            $deleteStmt->close();
        } else {
            echo json_encode(['message' => 'Mobile alert accepted, but failed to insert into brgy_accept.']);
        }

        $stmt_accept->close();
    } else {
        echo json_encode(['message' => 'Error accepting mobile alert.']);
    }

    $stmt->close();
} else {
    echo json_encode(['message' => 'Invalid data.']);
}
?>
