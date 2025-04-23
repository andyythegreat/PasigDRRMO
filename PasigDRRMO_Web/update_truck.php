<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['truckId'])) {
    $truckId = $_POST['truckId'];

    $unitName = $_POST['unitName'];
    $plateNumber = $_POST['plateNumber'];
    $truckType = $_POST['truckType'];
    $availability = $_POST['availability'];
        $status = $_POST['status'];

    $photoPath = '';

    // Get the existing photo path if not uploading a new one
    $stmt = $conn->prepare("SELECT Photo FROM brgy_profile WHERE ID = ?");
    $stmt->bind_param("i", $truckId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $photoPath = $row['Photo'];
    }
    
    if (isset($_FILES['photos']) && $_FILES['photos']['error'] == 0) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["photos"]["name"]);

        if (move_uploaded_file($_FILES["photos"]["tmp_name"], $targetFile)) {
            $photoPath = $targetFile;
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    }

   $sql = "UPDATE brgy_profile SET 
                UnitName = ?, 
                PlateNumber = ?, 
                TypeOfTruck = ?, 
                Photo = ?, 
                Availability = ? 
            WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $unitName, $plateNumber, $truckType, $photoPath, $availability, $truckId);

    if ($stmt->execute()) {
        echo "<script>alert('Truck information updated successfully!'); window.location.href='BRGY_Profile.php';</script>";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>