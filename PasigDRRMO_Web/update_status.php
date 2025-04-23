<?php
session_start();
include 'connection.php';

if (isset($_POST['unitName']) && isset($_POST['status'])) {
    $unitName = $_POST['unitName'];
    $status = $_POST['status'];

    $update_sql = "UPDATE brgy_profile SET Status = ? WHERE UnitName = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $status, $unitName);

    if ($stmt->execute()) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid data provided.";
}
?>
