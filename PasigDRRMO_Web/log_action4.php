<?php
session_start();
include 'connection.php';
date_default_timezone_set('Asia/Manila');


if(isset($_POST['action'])) {
    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];
    $timestamp = date("Y-m-d H:i:s");
    $action = $_POST['action'];

    $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);

    if($stmt2->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt2->error]);
    }
}
?>
