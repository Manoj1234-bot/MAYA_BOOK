<?php
include "includes/db.php"; // Adjust path if needed

if(!isset($_GET['id'])){
    die("Invalid Access");
}

$booking_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch details to verify
$query = "SELECT s.name, e.event_name, e.event_date, b.is_present 
          FROM bookings b 
          JOIN students s ON b.student_id = s.id 
          JOIN events e ON b.event_id = e.id 
          WHERE b.id = '$booking_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Certificate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background: #f4f4f9; }
        .card { background: white; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #5e43f3; }
        .valid { color: green; font-weight: bold; font-size: 24px; }
        .invalid { color: red; font-weight: bold; font-size: 24px; }
    </style>
</head>
<body>

<div class="card">
    <?php if($data && $data['is_present'] == 1): ?>
        <div class="valid">✅ Verified Authentic</div>
        <hr>
        <p><strong>Student:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
        <p><strong>Event:</strong> <?php echo htmlspecialchars($data['event_name']); ?></p>
        <p><strong>Date:</strong> <?php echo date("M d, Y", strtotime($data['event_date'])); ?></p>
        <p style="font-size: 12px; color: gray;">Certificate ID: <?php echo $booking_id; ?></p>
    <?php else: ?>
        <div class="invalid">❌ Invalid Certificate</div>
        <p>This certificate could not be verified in our records.</p>
    <?php endif; ?>
</div>

</body>
</html>