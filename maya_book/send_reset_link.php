<?php
// 1. Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Load the database connection
require_once __DIR__ . '/includes/db.php';

// 3. Set Timezone (Crucial for the 1-hour expiry to work)
date_default_timezone_set('Asia/Kolkata');

// 4. Load PHPMailer files using absolute paths
require __DIR__ . '/includes/PHPMailer/src/Exception.php';
require __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/src/SMTP.php';

if (isset($_POST['reset_request'])) {
    // Sanitize input
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    // Check if email exists
    $user_check = mysqli_query($conn, "SELECT id FROM students WHERE email='$email'");
    
    if (mysqli_num_rows($user_check) > 0) {
        // Generate a secure unique token and 1-hour expiry
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Sync MySQL timezone with PHP for this session
        mysqli_query($conn, "SET time_zone = '+05:30'");

        // Update the student record
        $update_query = "UPDATE students SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'";
        mysqli_query($conn, $update_query);

        $mail = new PHPMailer(true);

        try {
            // Server settings
            // $mail->SMTPDebug = 2; // Uncomment this only if you need to see technical errors
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'manojsm692@gmail.com';     
            $mail->Password   = 'eyxevpddpywzzgep';         
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Fixes for common XAMPP connection issues
            $mail->SMTPAutoTLS = false; 
            $mail->AuthType = 'LOGIN';

            // Recipients - setFrom MUST match your Username
            $mail->setFrom('manojsm692@gmail.com', 'Maya Book Support');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - Maya Book';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px; max-width: 500px;'>
                    <h2 style='color: #5e43f3;'>Maya Book</h2>
                    <p>Hi there,</p>
                    <p>We received a request to reset your password. Click the button below to choose a new one. <b>This link expires in 1 hour.</b></p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/maya_book/reset_password.php?token=$token' 
                           style='background: #5e43f3; color: white; padding: 14px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>
                           Reset My Password
                        </a>
                    </div>
                    <p style='color: #777; font-size: 12px;'>If you did not request this, you can safely ignore this email.</p>
                </div>";

            $mail->send();
            echo "<script>alert('A reset link has been sent to your email!'); window.location.href='login.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Error: {$mail->ErrorInfo}'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that email address.'); window.history.back();</script>";
    }
} else {
    header("Location: login.php");
    exit();
}