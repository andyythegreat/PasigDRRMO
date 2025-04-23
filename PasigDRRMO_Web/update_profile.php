<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['Username'];
    $address = $_POST['address'];
    $barangayName = $_POST['barangayName'];
    
    $photoPath = null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileSize = $_FILES['logo']['size'];
        $fileType = $_FILES['logo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = array('jpg', 'jpeg', 'png');
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            // Generate a unique filename by appending a timestamp or unique ID
            $newFileName = $username . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $photoPath = $dest_path;
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type.";
        }
    }

    if ($photoPath) {
        $sql = "UPDATE c3_addaccount SET Address = ?, Logo = ? WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $address, $photoPath, $username);
    } else {
        $sql = "UPDATE c3_addaccount SET Address = ? WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $address, $username);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    header("Location: BRGY_Profile.php"); 
    exit;
}

?>
