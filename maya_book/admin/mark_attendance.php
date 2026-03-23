<?php
session_start();
include "../includes/db.php";

// 1. Security & Parameter Check
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$event_id = isset($_GET['event_id']) ? mysqli_real_escape_string($conn, $_GET['event_id']) : 0;
$event_query = mysqli_query($conn, "SELECT event_name FROM events WHERE id='$event_id'");
$event = mysqli_fetch_assoc($event_query);

if(!$event) { die("Event not found."); }

$message = "";
$msg_type = "";

// 2. Handle Punch-In (Reference Code)
if(isset($_POST['booking_ref']) && !empty($_POST['booking_ref'])){
    $ref = mysqli_real_escape_string($conn, $_POST['booking_ref']);
    $check = mysqli_query($conn, "SELECT id, is_present FROM bookings WHERE booking_ref = '$ref' AND event_id = '$event_id'");
    
    if(mysqli_num_rows($check) > 0){
        $booking = mysqli_fetch_assoc($check);
        if($booking['is_present'] == 1) {
            $message = "⚠️ Student already marked Present!";
            $msg_type = "error";
        } else {
            mysqli_query($conn, "UPDATE bookings SET is_present = 1 WHERE id = '".$booking['id']."'");
            $message = "✅ Attendance Recorded Successfully!";
            $msg_type = "success";
        }
    } else {
        $message = "❌ Invalid Reference Code!";
        $msg_type = "error";
    }
}

// 3. Handle Manual Selection (Bulk Checkboxes)
if(isset($_POST['mark_manual'])){
    if(!empty($_POST['selected_bookings'])){
        $ids = implode(',', array_map('intval', $_POST['selected_bookings']));
        mysqli_query($conn, "UPDATE bookings SET is_present = 1 WHERE id IN ($ids)");
        $message = "✅ Manual list updated successfully!";
        $msg_type = "success";
    }
}

// 4. Fetch Stats & Student List
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as present FROM bookings WHERE event_id='$event_id' AND is_present=1"));
$students_list = mysqli_query($conn, "SELECT b.id, s.name, s.reg_no, b.is_present 
                                     FROM bookings b 
                                     JOIN students s ON b.student_id = s.id 
                                     WHERE b.event_id = '$event_id' 
                                     ORDER BY s.name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Punch-In: <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #5e43f3; --success: #2ecc71; --error: #e74c3c; --bg: #0f0c29; }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: white; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        
        .container { width: 100%; max-width: 450px; background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(15px); padding: 30px 20px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); text-align: center; position: relative; }
        
        h2 { font-size: 1rem; color: var(--primary); margin: 0; }
        h3 { font-size: 1.4rem; margin: 10px 0 25px 0; }

        .msg-box { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
        .success { background: rgba(46, 204, 113, 0.2); color: var(--success); border: 1px solid var(--success); }
        .error { background: rgba(231, 76, 60, 0.2); color: var(--error); border: 1px solid var(--error); }

        input[type="text"] { width: 100%; padding: 18px; font-size: 20px; text-align: center; background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.1); border-radius: 15px; color: white; outline: none; text-transform: uppercase; }
        input:focus { border-color: var(--primary); }

        .stats-card { background: rgba(0, 0, 0, 0.2); padding: 20px; border-radius: 18px; margin-top: 25px; }
        .btn-action { display: block; width: 100%; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-top: 10px; cursor: pointer; border: none; font-family: 'Poppins'; transition: 0.3s; }
        .btn-download { background: var(--success); color: white; }
        .btn-manual { background: rgba(255,255,255,0.1); color: white; border: 1px dashed rgba(255,255,255,0.3); }

        /* Manual Overlay Styles */
        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #0f0c29; z-index: 100; padding: 20px; overflow-y: auto; }
        .overlay-card { max-width: 500px; margin: auto; background: rgba(255,255,255,0.05); border-radius: 20px; padding: 25px; }
        .student-row { display: flex; align-items: center; justify-content: space-between; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: left; }
        .student-row b { display: block; font-size: 14px; }
        .student-row span { font-size: 11px; color: #999; }
        .btn-save { background: var(--primary); color: white; font-size: 16px; margin-top: 20px; height: 55px; }
        input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--success); }
    </style>
</head>
<body>

<div class="container">
    <a href="attendance_list.php" class="back-link">← Attendance List</a>
    <h2>PUNCH-IN SYSTEM</h2>
    <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>

    <?php if($message): ?>
        <div class="msg-box <?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="booking_ref" placeholder="ENTER CODE" autofocus autocomplete="off">
        <p style="font-size: 11px; opacity: 0.5; margin-top: 10px;">Quick scan or manual type reference code</p>
    </form>

    <div class="stats-card">
        <p style="margin: 0; font-size: 12px; opacity: 0.7;">Present Students</p>
        <h1 style="margin: 5px 0; font-size: 2.5rem;"><?php echo $stats['present']; ?></h1>
        
        <button onclick="document.getElementById('manual-overlay').style.display='block'" class="btn-action btn-manual">
            📝 Open Manual Selection
        </button>

        <a href="export_attendance.php?event_id=<?php echo $event_id; ?>" class="btn-action btn-download">
            📥 Download Excel Report
        </a>
    </div>
</div>

<div id="manual-overlay" class="overlay">
    <div class="overlay-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h2 style="color:white;">Select Students</h2>
            <button onclick="document.getElementById('manual-overlay').style.display='none'" style="background:none; border:none; color:white; font-size:30px; cursor:pointer;">&times;</button>
        </div>

        <form method="POST">
            <div style="max-height: 60vh; overflow-y: auto;">
                <?php while($s = mysqli_fetch_assoc($students_list)): ?>
                    <div class="student-row">
                        <div>
                            <b><?php echo htmlspecialchars($s['name']); ?></b>
                            <span>ID: <?php echo htmlspecialchars($s['reg_no']); ?></span>
                        </div>
                        <?php if($s['is_present'] == 1): ?>
                            <span style="color:var(--success); font-weight:700;">✅</span>
                        <?php else: ?>
                            <input type="checkbox" name="selected_bookings[]" value="<?php echo $s['id']; ?>">
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
            <button type="submit" name="mark_manual" class="btn-action btn-save">Update Selected Attendance</button>
        </form>
    </div>
</div>

</body>
</html>