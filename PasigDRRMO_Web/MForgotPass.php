<?php
session_start();


?>

<!DOCTYPE html>
<html lang="en">
<head>
        <script type="text/javascript">
            window.history.forward()
        </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCD | Forgot Password</title>
    <link rel="stylesheet" href="MForgotPass.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>

<!-- Forgot Password Section -->
<div class="form-container">
<div id="forgotPasswordSection">
    <h2>Forgot Password</h2>
    <form action="MForgotPass.php" method="post">
        <label> Email Address: </label>
        <input type="email" id="Email" name="forgot_email" required>
        <button type="submit" name="send_code">Send Code</button>
    </form>
    <p>Disclaimer: Always ensure to remember your password and keep it secure.</p>
</div>
</div>

<?php
include('smtp/PHPMailerAutoload.php');
include 'connection.php';

if (isset($_POST['send_code'])) {
    $forgot_email = $_POST['forgot_email'];
    $_SESSION['forgot_email'] = $forgot_email;

    $sql_resident = "SELECT Username FROM pasigresident WHERE Email = '$forgot_email'";
    $sql_fire_responder = "SELECT Username FROM firerespondersaccount WHERE EmailAddress = '$forgot_email'";

    $result_resident = $conn->query($sql_resident);
    $result_fire_responder = $conn->query($sql_fire_responder);

    if ($result_resident->num_rows > 0 || $result_fire_responder->num_rows > 0) {
        $verification_code = rand(100000, 999999);
        $_SESSION['verification_code'] = $verification_code;

        $_SESSION['code_generation_time'] = time();

        $subject = "Your Password Recovery Verification Code";
        $message = "Good day!\n\nWe received a request to reset the password for your account. To proceed, please use the code below to reset your password:\n\n𝗣𝗮𝘀𝘀𝘄𝗼𝗿𝗱 𝗥𝗲𝘀𝗲𝘁 𝗖𝗼𝗱𝗲: $verification_code\n\nFor security reasons, this code will expire in 20 seconds. If you did not request a password reset, you can safely ignore this email.\n\nIf you have any issues or need further assistance, feel free to contact us at pasigdrrmo1@gmail.com.\n\n\nThank you,\nPasig City DRRMO Support Team";

        if (smtp_mailer($forgot_email, $subject, $message)) {
            echo "<script>
                    alert('Verification code sent successfully!');
                    window.location.href = 'MVerificationPass.php';  // Redirect to verification page
                  </script>";
        } else {
            echo '<script>alert("Failed to send verification code. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Email address not found!");</script>';
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
