<?php
$servername = "localhost";
$username = "u832597832_root";
$password = "Pasigdrrmo#2024";
$dbname = "u832597832_pasigdrrmo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>