<?php
include 'connection.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'], $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status'])) {
    $currentDateTime = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO c3_breports_declined (Caller, Location, Barangay, Involve, Status, Date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status'], $currentDateTime);
    
    if ($stmt->execute()) {
        $action_decline = "C3 has just rejected your report!";
        $status_decline = 'Declined';
        
        $stmt_decline = $conn->prepare("INSERT INTO brgy_decline (EventLog, Date, Caller, Location, Involve, Status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_decline->bind_param("ssssss", $action_decline, $currentDateTime, $data['caller'], $data['location'], $data['involve'], $status_decline);

        if ($stmt_decline->execute()) {
            $deleteStmt = $conn->prepare("DELETE FROM brgy_locate WHERE Caller = ? AND Location = ? AND Barangay = ? AND Involve = ? AND Status = ?");
            $deleteStmt->bind_param("sssss", $data['caller'], $data['location'], $data['barangay'], $data['involve'], $data['status']);
            
            if ($deleteStmt->execute()) {
                echo json_encode(['message' => 'Alert declined successfully']);
            } else {
                echo json_encode(['message' => 'Alert declined, but failed to delete from brgy_locate.']);
            }

            $deleteStmt->close();
        } else {
            echo json_encode(['message' => 'Failed to insert into brgy_decline: ' . $stmt_decline->error]);
        }

        $stmt_decline->close();
    } else {
        echo json_encode(['message' => 'Error inserting into c3_breports_declined: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['message' => 'Invalid data.']);
}
?>
