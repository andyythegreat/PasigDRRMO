<?php
include('connection.php');

if (isset($_GET['token'])) {
    $verificationToken = $_GET['token'];

    $query = "SELECT is_verified, token_used FROM pasigresident WHERE verification_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $verificationToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['is_verified'] == 1 || $row['token_used'] == 1) {
            echo '
            <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; height: 100vh; background-image: url(\'https://pasigdrrmo.site/images/bgverif.png\'); background-size: cover; background-position: center;">
                <div style="font-family: Arial, sans-serif; max-width: 80%; width: 100%; padding: 40px; border: 1px solid #ddd; border-radius: 15px; background-color: white; text-align: center; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); transform: translateY(-15%);">
                    <img src="https://pasigdrrmo.site/images/warning1.png" alt="Warning Icon" style="width: 100px; height: auto; margin-bottom: 20px;">
                    <h2 style="color: #8B0000; font-size: 48px; margin: 0;">Verification Link Already Used</h2>
                    <p style="color: #333; font-size: 24px; line-height: 1.8; margin: 20px 0;">
                        This verification link has already been used or the email is already verified. Please log in to your account.
                    </p>
                    <footer style="margin-top: 30px; font-size: 18px; color: #777;">
                        © 2024 Pasig City DRRMO. All rights reserved.
                    </footer>
                </div>
                <div style="text-align: center; width: 100%; padding: 1px; margin-top: -20px;">
                    <img src="https://pasigdrrmo.site/images/Pasig_Text.png" alt="Pasig City DRRMO" style="max-width: 15%; height: auto;">
                </div>
            </div>';
            exit;
        }

        $updateQuery = "UPDATE pasigresident SET is_verified = 1, token_used = 1 WHERE verification_token = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $verificationToken);
        $updateStmt->execute();

        echo '
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; height: 100vh; background-image: url(\'https://pasigdrrmo.site/images/bgverif.png\'); background-size: cover; background-position: center;">
            <div style="font-family: Arial, sans-serif; max-width: 80%; width: 100%; padding: 40px; border: 1px solid #ddd; border-radius: 15px; background-color: white; text-align: center; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); transform: translateY(-15%);">
                <img src="https://pasigdrrmo.site/images/verified.png" alt="Verified Icon" style="width: 100px; height: auto; margin-bottom: 20px;">
                <h2 style="color: #062b82; font-size: 48px; margin: 0;">Email Verified Successfully!</h2>
                <p style="color: #333; font-size: 24px; line-height: 1.8; margin: 20px 0;">
                    Your email address has been verified successfully. <br> You can now log in to your account.
                </p>
                <footer style="margin-top: 30px; font-size: 18px; color: #777;">
                    © 2024 Pasig City DRRMO. All rights reserved.
                </footer>
            </div>
            <div style="text-align: center; width: 100%; padding: 1px; margin-top: -20px;">
                <img src="https://pasigdrrmo.site/images/Pasig_Text.png" alt="Pasig City DRRMO" style="max-width: 15%; height: auto;">
            </div>
        </div>';
    } else {
        echo '
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; height: 100vh; background-image: url(\'https://pasigdrrmo.site/images/bgverif.png\'); background-size: cover; background-position: center;">
            <div style="font-family: Arial, sans-serif; max-width: 80%; width: 100%; padding: 40px; border: 1px solid #ddd; border-radius: 15px; background-color: white; text-align: center; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); transform: translateY(-15%);">
                <img src="https://pasigdrrmo.site/images/warning1.png" alt="Warning Icon" style="width: 100px; height: auto; margin-bottom: 20px;">
                <h2 style="color: #8B0000; font-size: 48px; margin: 0;">Invalid or Expired Verification Token</h2>
                <p style="color: #333; font-size: 24px; line-height: 1.8; margin: 20px 0;">
                    The verification token provided is either invalid or has expired. Please request a new token to proceed with verification.
                </p>
                <footer style="margin-top: 30px; font-size: 18px; color: #777;">
                    © 2024 Pasig City DRRMO. All rights reserved.
                </footer>
            </div>
            <div style="text-align: center; width: 100%; padding: 1px; margin-top: -20px;">
                <img src="https://pasigdrrmo.site/images/Pasig_Text.png" alt="Pasig City DRRMO" style="max-width: 15%; height: auto;">
            </div>
        </div>';
    }
} else {
    echo '
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; height: 100vh; background-image: url(\'https://pasigdrrmo.site/images/bgverif.png\'); background-size: cover; background-position: center;">
            <div style="font-family: Arial, sans-serif; max-width: 80%; width: 100%; padding: 40px; border: 1px solid #ddd; border-radius: 15px; background-color: white; text-align: center; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); transform: translateY(-15%);">
                <img src="https://pasigdrrmo.site/images/warning1.png" alt="Warning Icon" style="width: 100px; height: auto; margin-bottom: 20px;">
                <h2 style="color: #8B0000; font-size: 48px; margin: 0;">No Verification Token Provided</h2>
                <p style="color: #333; font-size: 24px; line-height: 1.8; margin: 20px 0;">
                    A verification token is required to verify your email address.
                </p>
                <footer style="margin-top: 30px; font-size: 18px; color: #777;">
                    © 2024 Pasig City DRRMO. All rights reserved.
                </footer>
            </div>
            <div style="text-align: center; width: 100%; padding: 1px; margin-top: -20px;">
                <img src="https://pasigdrrmo.site/images/Pasig_Text.png" alt="Pasig City DRRMO" style="max-width: 15%; height: auto;">
            </div>
        </div>';
}

?>
