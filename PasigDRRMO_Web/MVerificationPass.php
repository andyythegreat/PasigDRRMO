<?php
session_start();

include 'connection.php';


if (isset($_POST['verify_code'])) {
    $entered_code = $_POST['verification_code'];
    $forgot_email = $_SESSION['forgot_email'];

    $code_generation_time = $_SESSION['code_generation_time'];
    $current_time = time();

    if (($current_time - $code_generation_time) > 300) {
        echo '<script>alert("Your code has expired. Please request a new verification code.");</script>';
    } elseif ($entered_code == $_SESSION['verification_code']) {
        echo '<script>
                alert("Verification code is correct.");
                window.location.href = "MResetPass.php";
              </script>';
    } else {
        echo '<script>alert("Incorrect verification code.");</script>';
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
    <title>PCD | Verification Password</title>
    <link rel="stylesheet" href="MVerificationPass.css">
    <link rel="shortcut icon" type="image/png" href="images/Title.png">
</head>
<body>

<!-- Verification Code Section -->
<div class="form-container">
    <div id="verifyCodeSection">
        <h2>Enter Verification Code</h2>
        <form action="MVerificationPass.php" method="post">
            <label for="verification_code">Verification Code:</label>
            <input type="text" id="verification_code" name="verification_code" required>
            <input type="hidden" name="forgot_email" id="hiddenEmail" value="<?php echo $_SESSION['forgot_email']; ?>"> 
            <button type="submit" name="verify_code">Verify Code</button>
        </form>
        <p>Code expires in <span id="timer"> remaining </span> minutes.</p>
    </div>
</div>

<script type="text/javascript">
    var expirationTime = localStorage.getItem('expirationTime') || 5 * 60; 
    var expired = false; 

    function updateTimer() {
        if (expired) return; 

        var minutes = Math.floor(expirationTime / 60);
        var seconds = expirationTime % 60;
        document.getElementById('timer').textContent = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);

        expirationTime--;

        if (expirationTime < 0) {
            expired = true; 
            alert("Your verification code has expired. Please request a new code.");
        }

        localStorage.setItem('expirationTime', expirationTime);
    }

    window.addEventListener('beforeunload', function() {
        localStorage.removeItem('expirationTime');
    });

    setInterval(updateTimer, 1000);
</script>


</body>
</html>