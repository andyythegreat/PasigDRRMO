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
        $sql = "UPDATE c3_addaccount SET Password = ? WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $forgot_email);

        if ($stmt->execute()) {
            echo '<script>alert("Password reset successful!");</script>';
            unset($_SESSION['forgot_email']);

            echo '<script>window.location.href = "index.php";</script>';
        } else {
            echo '<script>alert("Failed to reset password. SQL Error: ' . $conn->error . '");</script>';
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
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-image: url('images/PCDRRMO_BG.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
            position: relative;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.5); 
            z-index: -1;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .form-container img {
            width: 100px;
            height: 100px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .form-container label {
            display: block;
            text-align: left;
            font-size: 14px;
            color: #333;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .form-container input {
            width: 95%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: #f8f8f8;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background-color: #062474;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #1f74be;
        }
        
        /* CSS FOR INPUT TYPE PASSWORD AND EYE ICON FOR SHOW AND HIDE PASSWORD */
        .password-group {
            position: relative;
            margin-bottom: 15px;
        }

        .eye_button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            position: absolute;
            top: 58%;
            right: 10px;
            transform: translateY(-50%);
        }

        .eye {
            width: 27px !important;
            height: 27px !important;
            max-width: 27px !important;
            max-height: 27px !important;
            filter: invert(0) !important; 
        }

        /* For Tablets */
        @media (max-width: 768px) {
            .form-container {
                max-width: 90%;
                padding: 35px;
            }

            .form-container h2 {
                font-size: 26px;
            }

            .form-container label {
                font-size: 15px; 
            }

            .form-container input {
                font-size: 18px; 
                padding: 12px; 
            }

            .form-container button {
                font-size: 18px; 
                padding: 12px;
            }
        }

        /* For Phones */
        @media (max-width: 480px) {
            .form-container {
                max-width: 95%; 
                padding: 30px;
            }

            .form-container h2 {
                font-size: 24px; 
            }

            .form-container label {
                font-size: 14px;
            }

            .form-container input {
                font-size: 16px;
                padding: 10px;
            }

            .form-container button {
                font-size: 16px;
                padding: 10px;
            }
        }

        /* For Large Screens (Laptops and Desktops) */
        @media (min-width: 1024px) {
            .form-container {
                max-width: 600px; 
                padding: 40px; 
            }

            .form-container h2 {
                font-size: 28px; 
            }

            .form-container label {
                font-size: 16px;
            }

            .form-container input {
                font-size: 18px;
                padding: 15px;
            }

            .form-container button {
                font-size: 18px;
                padding: 15px;
            }
        }

    </style>
</head>

<body>

    <div class="form-container">
        <img src="images/password.png" alt="Protection Icon">
        <h2>Change Your Password</h2>
        <form action="reset_password.php" method="post">
            <input type="hidden" name="forgot_email" value="<?php echo $_SESSION['forgot_email']; ?>">

            <div class="password-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
            <span class="eye_button" onclick="togglePasswordVisibility('new_password', 'eyeNewPassword')">
                <img id="eyeNewPassword" src="images/eye_open.png" alt="Show Password" class="eye">
            </span>
            </div>

            <div class="password-group">
            <label for="confirm_password">Confirm Password</label>
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

