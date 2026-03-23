<?php
session_start();
include "../includes/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

date_default_timezone_set('Asia/Kolkata');

// Function to generate your specific MAYA-0000 format
function generateBookingRef($length = 4) {
    $chars = '0123456789';
    $random = substr(str_shuffle($chars), 0, $length);
    return "MAYA-" . $random;
}

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);

// 1. Delete the current student's booking
$delete_sql = "DELETE FROM bookings WHERE id = '$booking_id' AND student_id = '$student_id'";

if (mysqli_query($conn, $delete_sql)) {
    
    // 2. WAITLIST LOGIC: Find the next student
    $waitlist_sql = "SELECT w.id AS wait_id, w.student_id, s.email, s.name, e.event_name 
                     FROM waitlist w 
                     JOIN students s ON w.student_id = s.id 
                     JOIN events e ON w.event_id = e.id
                     WHERE w.event_id = '$event_id' 
                     ORDER BY w.id ASC LIMIT 1";
    
    $waitlist_result = mysqli_query($conn, $waitlist_sql);

    if (mysqli_num_rows($waitlist_result) > 0) {
        $next_student = mysqli_fetch_assoc($waitlist_result);
        $waiting_student_id = $next_student['student_id'];
        $waitlist_entry_id = $next_student['wait_id'];
        $next_email = $next_student['email'];
        $next_name = $next_student['name'];
        $event_name = $next_student['event_name'];

        // 3. Generate the NEW Reference using your MAYA- function
        $new_ref = generateBookingRef(4);

        // 4. Move from Waitlist to Bookings
        $move_sql = "INSERT INTO bookings (event_id, student_id, booking_ref, seat_no) 
                     VALUES ('$event_id', '$waiting_student_id', '$new_ref', '0')";
        
        if (mysqli_query($conn, $move_sql)) {
            // Delete from waitlist
            mysqli_query($conn, "DELETE FROM waitlist WHERE id = '$waitlist_entry_id'");

            // 5. SEND THE EMAIL
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'manojsm692@gmail.com';
                $mail->Password   = 'eyxevpddpywzzgep'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('manojsm692@gmail.com', 'Maya Book Events');
                $mail->addAddress($next_email);

                $mail->isHTML(true);
                $mail->Subject = 'Confirmed: Your Spot for ' . $event_name;
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 30px; border: 2px solid #5e43f3; border-radius: 15px; max-width: 500px; margin: auto;'>
                        <h2 style='color: #5e43f3;'>Hi $next_name,</h2>
                        <p>A spot just opened up for <b>$event_name</b>!</p>
                        
                        <div style='background: #f4f2ff; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: center;'>
                            <span style='display: block; font-size: 12px; color: #5e43f3; text-transform: uppercase; font-weight: bold;'>Your New Reference Code</span>
                            <span style='font-size: 22px; font-weight: bold; color: #333; letter-spacing: 2px;'>$new_ref</span>
                        </div>

                        <div style='text-align: center;'>
                            <a href='http://localhost/maya_book/student/dashboard.php' 
                               style='background: #5e43f3; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>
                               Go to My Dashboard →
                            </a>
                        </div>
                    </div>";

                $mail->send();
            } catch (Exception $e) {
                // Silently handle mail errors
            }
        }
    }
    
    header("Location: dashboard.php?msg=cancelled");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>