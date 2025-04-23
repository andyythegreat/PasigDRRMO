<?php
include 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['ID'];
    $date = $_POST['Date'];
    $caller = $_POST['Caller'];
    $location = $_POST['Location'];
    $involve = $_POST['Involve'];
    $status = $_POST['Status'];
    $barangay = $_SESSION['Username'];

    $sql = "UPDATE brgy_locate SET Date=?, Caller=?, Location=?, Involve=?, Status=? WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $date, $caller, $location, $involve, $status, $id);

    if ($stmt->execute()) {
        echo "Record updated successfully.";

        $checkSql = "SELECT * FROM brgy_report_update WHERE ID=?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $updateSql = "UPDATE brgy_report_update SET Date=?, Caller=?, Location=?, Barangay=?, Involve=?, Status=? WHERE ID=?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssssi", $date, $caller, $location, $barangay, $involve, $status, $id);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            $insertSql = "INSERT INTO brgy_report_update (ID, Date, Caller, Location, Barangay, Involve, Status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("issssss", $id, $date, $caller, $location, $barangay, $involve, $status);
            $insertStmt->execute();
            $insertStmt->close();
        }

        $checkStmt->close();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
?>
