<?php
session_start();
include 'connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $username = $_SESSION["Username"];
    $position = $_SESSION["Position"];
    $action = $_POST["action"];

    date_default_timezone_set('Asia/Manila');
    $logout_time = time();

    $stmt1 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, NOW())");
    $stmt1->bind_param("sss", $username, $position, $action);
    
    $status = "🔴 OFFLINE";
    $stmt2 = $conn->prepare("UPDATE c3_barangay SET Status = ?, Last_Seen = ? WHERE Barangay = ?");
    $stmt2->bind_param("sss", $status, $logout_time, $username);

    $success = $stmt1->execute() && $stmt2->execute();
    
    if ($success) {
        echo "Logout action logged successfully.";
    } else {
        echo "Error logging logout action or updating status: " . $conn->error;
    }

    $stmt1->close();
    $stmt2->close();
}

$conn->close();
?>