<?php
session_start();

include 'connection.php';

if (isset($_POST['submit'])) {
    $username = $_POST['text'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM c3_addaccount WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Your account doesn\'t exist in our record!');</script>";
    } else {
        $row = $result->fetch_assoc();
        
        if ($row['Password'] !== $password) {
            echo "<script>alert('Your password is incorrect');</script>";
        } else {
            $position = $row['Position'];
            $username = $row['Username'];

            $_SESSION['Username'] = $username;
            $_SESSION['Position'] = $position;

            $stmt_check = $conn->prepare("SELECT * FROM c3_barangay WHERE Barangay = ?");
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            $status = "🟢 ONLINE";

            if ($result_check->num_rows > 0) {
                $stmt_update = $conn->prepare("UPDATE c3_barangay SET Status = ? WHERE Barangay = ?");
                $stmt_update->bind_param("ss", $status, $username);
                $stmt_update->execute();
                $stmt_update->close();
            } elseif ($position == 'Barangay' || $position == 'C3') {
                $stmt_insert = $conn->prepare("INSERT INTO c3_barangay (Barangay, Status) VALUES (?, ?)");
                $stmt_insert->bind_param("ss", $username, $status);
                $stmt_insert->execute();
                $stmt_insert->close();
            }

            date_default_timezone_set('Asia/Manila');
            $timestamp = date("Y-m-d H:i:s");        
            $action = "Logged in";
            $stmt2 = $conn->prepare("INSERT INTO reports (ACCOUNT_NAME, ROLE, ACTION, TIMESTAMP) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("ssss", $username, $position, $action, $timestamp);
            $stmt2->execute();
            $stmt2->close();

            switch ($position) {
                case 'C3':
                    echo "<script>alert('Welcome to $username!'); window.location.href='C3_Dashboard.php';</script>";
                    exit;
                case 'Barangay':
                    echo "<script>alert('Welcome to $username!'); window.location.href='BRGY_Locate.php';</script>";
                    exit;
                default:
                    echo "<script>alert('Position is unknown'); window.location.href='default.php';</script>";
                    exit;
            }
        }
    }
}



?>







<!DOCTYPE html>
<html lang="en">
<head>
        <script type="text/javascript">
            window.history.forward()
        </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasig City DRRMO</title>
    <link rel="stylesheet" href="Sign_in65.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>
    
    
<?php
$query = "SELECT DISTINCT Photo FROM c3_background";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$bground = '';
if ($row = $result->fetch_assoc()) {
    $bground = $row['Photo'];
}

if (empty($bground)) {
    $bground = 'images/PCDRRMO_BG.png';
}
?>

<div class="background-overlay">
                <img src="<?php echo htmlspecialchars($bground); ?>" alt="Background Image" class="background-image">

</div>


<?php
$query = "SELECT DISTINCT Logo FROM c3_logo";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$logo = '';
if ($row = $result->fetch_assoc()) {
    $logo = $row['Logo'];
}

if (empty($logo)) {
    $logo = 'images/PCDRRMO_LOGO1.png';
}
?>

<div class="container">
    <div class="sign-in-form">
<div class="logo">
    <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo1" class="logo-image">
</div>

        <div class="form-content">
            <h2>Sign In</h2>
            <form method="post" action="index.php">
                <div class="form-group">
                    <img src="images/Mail.png" alt="Symbol1">
                    <input type="text" id="text" name="text" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <img src="images/Padlock.png" alt="Symbol2">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    
                    <button type="button" id="togglePassword" class="eye-button">
                    <img id="eyeImage" src="images/eye_open.png" alt="Show Password" class="eye">
                    </button>
                </div>

    <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
    var passwordInput = document.getElementById("password");
    var eyeImage = document.getElementById("eyeImage");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeImage.src = "images/eye_closed.png";
    } else {
        passwordInput.type = "password";
        eyeImage.src = "images/eye_open.png";
    }
    });
    </script> 

<br>
<div class="forgot-password">
    <a onclick="showForgotPasswordPopup()">Forgot your Password?</a>
</div>

                <div class="form-group"> </div>
                <button type="submit" name="submit" class="sign-in-button">Sign In</button>
            </form>
        </div>
    </div> <br> <br> <br> <br> <br>
    
<div class="main-bottom-container">
<div class="bottom-image">
    <img src="images/Pasig_Text.png" alt="Bottom Image">
</div>
</div>

<div class="container">
<div class="sign-in-form">

<!-- Pop-up overlay -->
<div id="popupOverlay" onclick="hideForgotPasswordPopup()"></div>

    <!-- Forgot Password popup -->
    <div id="forgotPasswordPopup">
        <button class="x-button" onclick="hideForgotPasswordPopup()"> ✖ </button>
        <form action="index.php" method="post">
            <h2>Forgot Password</h2>
            <input type="email" id="Email" name="forgot_email" placeholder="Email Address" required>
            <button type="submit" name="send_code" onclick="hideForgotPasswordPopup()">Send Code</button>
        </form>
        <p> Disclaimer: Always ensure to remember your password and keep it secure...</p>
    </div>
    
</div>
</div>
    
<div class="container">
<div class="sign-in-form">
    
<!-- Verification code popup -->
<div id="verifyCodePopup" style="display: none;">
    <button class="x-button" onclick="hideVerifyCodePopup()"> ✖ </button>
    <form action="index.php" method="post">
        <h2>Enter Verification Code</h2>
        <input type="text" id="verification_code" name="verification_code" placeholder="Enter Code" required>
        <input type="hidden" name="forgot_email" id="hiddenEmail">
        <button type="submit" name="verify_code">Verify Code</button>
    </form>
    <p>Code expires in <span id="timer">20</span> seconds.</p>
</div>

</div>
</div>
    
        <div id="popupOverlay" onclick="hideAllPopups()"></div>
</div>
</div>

<script>
function showForgotPasswordPopup() {
    document.getElementById('forgotPasswordPopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
}

function hideForgotPasswordPopup() {
    document.getElementById('forgotPasswordPopup').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
}

function showVerifyCodePopup(email) {
    document.getElementById('verifyCodePopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('hiddenEmail').value = email;

    // Start the timer
    startTimer();
}

function hideVerifyCodePopup() {
    document.getElementById('verifyCodePopup').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
    clearInterval(timerInterval); // Stop the timer when the popup is closed
}

// Timer related variables
let timer = 20;
let timerInterval;

function startTimer() {
    // Update the timer every second
    timerInterval = setInterval(function() {
        timer--;
        document.getElementById('timer').textContent = timer;

        // When timer expires, alert the user and close the popup
        if (timer <= 0) {
            clearInterval(timerInterval);
            alert('Your verification code has expired. Please request a new one.');
            hideVerifyCodePopup();
        }
    }, 1000); // 1000ms = 1 second
}

function showResetPasswordPopup() {
    document.getElementById('resetPasswordPopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('hiddenEmail').value = '<?php echo $_SESSION['forgot_email']; ?>'; // Pass the email to the form
}


function hideResetPasswordPopup() {
    document.getElementById('resetPasswordPopup').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
}

function hideAllPopups() {
    hideForgotPasswordPopup();
    hideVerifyCodePopup();
}
</script>

</body>
</html>



<?php

include('smtp/PHPMailerAutoload.php');

include 'connection.php';



if (isset($_POST['send_code'])) {
    $forgot_email = $_POST['forgot_email'];
    $_SESSION['forgot_email'] = $forgot_email;

    $sql = "SELECT Username FROM c3_addaccount WHERE Email = '$forgot_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['forgot_email'] = $forgot_email;

        // Store the timestamp of when the code was generated
        $_SESSION['code_generation_time'] = time();

        $subject = "Your Password Recovery Verification Code";
        $message = "Good day!\n\nWe received a request to reset the password for your account. To proceed, please use the code below to reset your password:\n\n𝗣𝗮𝘀𝘀𝘄𝗼𝗿𝗱 𝗥𝗲𝘀𝗲𝘁 𝗖𝗼𝗱𝗲: $verification_code\n\nFor security reasons, this code will expire in 20 seconds. If you did not request a password reset, you can safely ignore this email.\n\nIf you have any issues or need further assistance, feel free to contact us at pasigdrrmo1@gmail.com.\n\n\nThank you,\nPasig City DRRMO Support Team";

        if (smtp_mailer($forgot_email, $subject, $message)) {
            echo "<script>showVerifyCodePopup('$forgot_email');</script>";
        } else {
            echo '<script>alert("Failed to send verification code. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Email address not found!");</script>';
    }
}


if (isset($_POST['verify_code'])) {
    $entered_code = $_POST['verification_code'];
    $forgot_email = $_SESSION['forgot_email'];

    // Get the time when the code was generated
    $code_generation_time = $_SESSION['code_generation_time'];
    $current_time = time();

    // Check if the code expired (20 seconds)
    if (($current_time - $code_generation_time) > 20) {
        echo '<script>alert("Your code has expired. Please request a new verification code.");</script>';
    } elseif ($entered_code == $_SESSION['verification_code']) {
        // Open reset_password.php in a new tab if the code is correct and not expired
        echo '<script>window.open("reset_password.php", "_blank");</script>';
    } else {
        echo '<script>alert("Incorrect verification code.");</script>';
    }
}




if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!isset($_SESSION['forgot_email'])) {
        echo '<script>alert("Session expired. Please try the forgot password process again.");</script>';
        exit;
    }

    $forgot_email = $_SESSION['forgot_email'];

    if ($new_password === $confirm_password) {
        $sql = "UPDATE c3_addaccount SET Password = '$new_password' WHERE Email = '$forgot_email'";
        if ($conn->query($sql) === TRUE) {
            echo '<script>alert("Password reset successful!");</script>';
            echo '<script>hideResetPasswordPopup();</script>';

            unset($_SESSION['forgot_email']);
        } else {
            echo '<script>alert("Failed to reset password. SQL Error: ' . $conn->error . '");</script>';
        }
    } else {
        echo '<script>alert("Passwords do not match. Please try again.");</script>';
    }
}


function smtp_mailer($to, $subject, $msg)
{
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(false);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "pasigdrrmo1@gmail.com";
    $mail->Password = "ifdu tgef zrqp oqzc";
    $mail->SetFrom("pasigdrrmo1@gmail.com", "Pasig City DRRMO Support");
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->AddAddress($to);
    $mail->SMTPOptions = array('ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => false
    ));

    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

?>
