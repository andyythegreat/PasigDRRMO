<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM c3_addaccount WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo 'exists';
    } else {
        echo 'not_exists';
    }

    $stmt->close();
    $conn->close();
}
?>
