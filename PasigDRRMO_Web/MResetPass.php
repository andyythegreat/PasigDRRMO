<?php
session_start();
include('connection.php');

if (!isset($_SESSION['forgot_email']) || !isset($_SESSION['verification_code'])) {
    echo '<script>alert("Session expired. Please try the forgot password process again.");</script>';
    exit;
}

if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $forgot_email = $_SESSION['forgot_email'];

    if ($new_password === $confirm_password) {
        $reset_successful = false;

        // Update in pasigresident table
        $sql = "UPDATE pasigresident SET Password = ? WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $forgot_email);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $reset_successful = true;
        }

        // Update in firerespondersaccount table (if not already updated)
        if (!$reset_successful) {
            $sql = "UPDATE firerespondersaccount SET Password = ? WHERE EmailAddress = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_password, $forgot_email);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $reset_successful = true;
            }
        }

        if ($reset_successful) {
            echo '<script>alert("Password reset successful!");</script>';
            unset($_SESSION['forgot_email']);
            echo '<script>window.location.href = "index.php";</script>';
        } else {
            echo '<script>alert("Failed to reset password. Either the email does not exist or an error occurred.");</script>';
        }
    } else {
        echo '<script>alert("Passwords do not match. Please try again.");</script>';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCD | Change Password</title>
    <link rel="stylesheet" href="MResetPass.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>

<body>

    <div class="form-container">
        <img src="images/password.png" alt="Protection Icon">
        <h2>Change Your Password</h2>
        <form action="MResetPass.php" method="post">
            <input type="hidden" name="forgot_email" value="">

            <div class="password-group">
            <label for="new_password">New Password: </label>
            <input type="password" id="new_password" name="new_password" required>
            <span class="eye_button" onclick="togglePasswordVisibility('new_password', 'eyeNewPassword')">
                <img id="eyeNewPassword" src="images/eye_open.png" alt="Show Password" class="eye">
            </span>
            </div>

            <div class="password-group">
            <label for="confirm_password">Confirm Password: </label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span class="eye_button" onclick="togglePasswordVisibility('confirm_password', 'eyeConfirmPassword')">
                <img id="eyeConfirmPassword" src="images/eye_open.png" alt="Show Password" class="eye">
            </span>
            </div>

            <button type="submit" name="reset_password">Change Password</button>
        </form>
    </div>
    
<script>
    function togglePasswordVisibility(inputId, eyeId) {
        var input = document.getElementById(inputId);
        var eyeIcon = document.getElementById(eyeId);

    if (input.type === "password") {
        input.type = "text"; 
        eyeIcon.src = "images/eye_closed.png";  
        eyeIcon.alt = "Hide Password"; 
    } else {
        input.type = "password"; 
        eyeIcon.src = "images/eye_open.png";  
        eyeIcon.alt = "Show Password";  
    }
}



    document.querySelector('form').addEventListener('submit', function(event) {
        var newPassword = document.getElementById('new_password').value;
        var confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            alert("Passwords do not match. Please try again.");
            event.preventDefault();
            return;
        }

        var passwordValid = true;
        var errorMessage = "";
        var missingRequirements = [];

        if (newPassword.length < 8) {
            missingRequirements.push("at least 8 characters long");
        }

        if (!/[A-Z]/.test(newPassword)) {
            missingRequirements.push("at least one uppercase letter");
        }

        if (!/[a-z]/.test(newPassword)) {
            missingRequirements.push("at least one lowercase letter");
        }

        if (!/\d/.test(newPassword)) {
            missingRequirements.push("at least one number");
        }

        if (!/[@$!%*?&\-]/.test(newPassword)) {
            missingRequirements.push("at least one special character (@$!%*?&-)");
        }

        if (missingRequirements.length > 0) {
            errorMessage = "Password does not meet the following requirements:\n" + missingRequirements.join("\n");
            alert(errorMessage);
            event.preventDefault();
        }
    });
</script>

</body>
</html>