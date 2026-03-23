<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['student_id'])){
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
date_default_timezone_set('Asia/Kolkata');

/**
 * 1. Function to generate a random Reference Code 
 * (Stored in DB now, but only shown to student after approval)
 */
function generateBookingRef($length = 4) {
    $chars = '0123456789';
    $random = substr(str_shuffle($chars), 0, $length);
    return "MAYA-" . $random;
}

// 2. SECURITY CHECK: Is the student already in the bookings table for this event?
$check_booking = mysqli_query($conn, "SELECT id, status FROM bookings WHERE student_id='$student_id' AND event_id='$event_id'");
if(mysqli_num_rows($check_booking) > 0){
    die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#0f0c29; color:white; height:100vh;'>
            <h2 style='color:#e74c3c;'>Action Prohibited</h2>
            <p>You already have a confirmed seat or a pending request for this event.</p>
            <a href='events.php' style='color:#5e43f3;'>Go Back to Events</a>
         </div>");
}

// 3. FETCH EVENT DETAILS
$event_query = mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'");
$event = mysqli_fetch_assoc($event_query);

if(!$event) { die("Error: Event not found."); }

// 4. DEADLINE CHECK (Date + Time)
$current_now = time();
$deadline_str = $event['booking_deadline'] . ' ' . $event['booking_time'];
$deadline_time = strtotime($deadline_str);

if($current_now > $deadline_time){
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Registration Closed</h2><p>The deadline for this event has passed.</p><a href='events.php'>Back</a></div>");
}

// 5. SEAT AVAILABILITY CHECK
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE event_id='$event_id' AND status != 'rejected'");
$count = mysqli_fetch_assoc($count_query);
$occupied = $count['total'];

if($occupied >= $event['total_seats']){
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>House Full!</h2><p>No seats remaining. Please check for other events.</p><a href='events.php'>Back</a></div>");
}

// 6. PREPARE DATA
$booking_ref = generateBookingRef();
$seat_no = $occupied + 1;

// Determine status based on Admin's setting for this specific event
// approval_required = 1 means AUTO APPROVE
// approval_required = 0 means MANUAL ADMIN APPROVAL
$status = ($event['approval_required'] == 1) ? 'approved' : 'pending';

// 7. INSERT INTO DATABASE
$insert_query = "INSERT INTO bookings(student_id, event_id, seat_no, booking_ref, status) 
                 VALUES('$student_id', '$event_id', '$seat_no', '$booking_ref', '$status')";

if(mysqli_query($conn, $insert_query)){
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Status - Maya Book</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <style>
            body { 
                font-family: 'Poppins', sans-serif; 
                background: #0f0c29; 
                background: linear-gradient(135deg, #1a1a2e, #0f0c29);
                display: flex; justify-content: center; align-items: center; 
                height: 100vh; margin: 0; color: white; 
            }
            .success-card { 
                background: rgba(255,255,255,0.05); 
                backdrop-filter: blur(15px); 
                padding: 40px; border-radius: 30px; 
                border: 1px solid rgba(255,255,255,0.1); 
                text-align: center; max-width: 420px; width: 90%; 
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            }
            .icon { font-size: 70px; margin-bottom: 20px; display: block; }
            
            .status-box { 
                background: rgba(255,255,255,0.03); 
                border: 2px dashed <?php echo ($status == 'approved') ? '#2ecc71' : '#f39c12'; ?>; 
                padding: 25px; margin: 25px 0; border-radius: 20px; 
            }
            
            .ref-code { 
                font-size: 32px; letter-spacing: 3px; font-weight: 700; 
                color: #fff; display: block; margin-bottom: 5px; 
            }
            
            .msg { font-size: 14px; color: #bdc3c7; line-height: 1.6; margin-bottom: 20px; }
            
            .btn { 
                display: block; padding: 16px; 
                background: #5e43f3; color: white; 
                text-decoration: none; border-radius: 14px; 
                font-weight: 600; transition: 0.3s; 
            }
            .btn:hover { background: #4a32c3; transform: translateY(-2px); }
            
            .pending-text { color: #f39c12; font-weight: 600; font-size: 18px; }
        </style>
    </head>
    <body>
        <div class="success-card">
            <?php if($status == 'approved'): ?>
                <span class="icon">🎟️</span>
                <h2 style="color: #2ecc71; margin: 0;">Booking Confirmed!</h2>
                <p class="msg">Your seat is secured for <b><?php echo htmlspecialchars($event['event_name']); ?></b>. Show this reference at the venue.</p>
                
                <div class="status-box">
                    <span style="font-size: 11px; color: #aaa; text-transform: uppercase; display: block;">Ticket Reference</span>
                    <span class="ref-code"><?php echo $booking_ref; ?></span>
                    <span style="font-size: 13px; color: #2ecc71;">Seat Number: <?php echo $seat_no; ?></span>
                </div>
            <?php else: ?>
                <span class="icon">⏳</span>
                <h2 style="color: #f39c12; margin: 0;">Request Sent!</h2>
                <p class="msg">Admin approval is required for <b><?php echo htmlspecialchars($event['event_name']); ?></b>. We will notify you once confirmed.</p>
                
                <div class="status-box">
                    <span class="pending-text">Status: Pending Approval</span>
                    <p style="font-size: 11px; color: #aaa; margin-top: 10px;">Your ticket reference and seat number will appear here after the admin approves your request.</p>
                </div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn">Return to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>