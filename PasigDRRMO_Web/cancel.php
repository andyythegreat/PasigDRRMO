<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancelReason = isset($_POST['cancelReason']) ? trim($_POST['cancelReason']) : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $caller = isset($_POST['caller']) ? trim($_POST['caller']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $involved = isset($_POST['involved']) ? trim($_POST['involved']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $user = isset($_SESSION['Username']) ? $_SESSION['Username'] : "";

    if (!empty($cancelReason) && $id > 0 && !empty($user)) {
        $sql = "INSERT INTO brgy_cancel (ID, Barangay, Date, Caller, Location, Involved, Status, Reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("isssssss", $id, $user, $date, $caller, $location, $involved, $status, $cancelReason);

        if ($stmt->execute()) {
            // Prepare to delete the record from brgy_locate
            $sql_delete = "DELETE FROM brgy_locate WHERE ID = ?";
            $stmt_delete = $conn->prepare($sql_delete);

            if ($stmt_delete === false) {
                die("Error preparing deletion statement: " . $conn->error);
            }

            $stmt_delete->bind_param("i", $id);

            if ($stmt_delete->execute()) {
                echo "success"; // Respond with "success" for JavaScript handling
            } else {
                echo "Error: " . $stmt_delete->error;
            }

            $stmt_delete->close();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Cancellation reason, valid record ID, and current Username are required.";
    }
}

$conn->close();
?>
