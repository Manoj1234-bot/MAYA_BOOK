<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$query = "SELECT * FROM events ORDER BY event_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Event for Attendance</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 250px; background: var(--primary); color: white; height: 100vh; padding: 20px; }
        .sidebar a { color: #bdc3c7; text-decoration: none; display: block; padding: 12px; margin: 5px 0; border-radius: 4px; }
        .main-content { flex-grow: 1; padding: 40px; }
        .event-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .btn-checkin { background: var(--accent); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
   <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="approve_students.php">✅ Approve Students</a>
    <a href="create_event.php">➕ Create Event</a>
    <a href="view_bookings.php">📋 View Bookings</a>
    <a href="attendance_list.php">📍 Attendance List</a>
    <a href="manage_approvals.php">Manage approval</a>
    <a href="manage_events.php">Manage events</a>
    <a href="admin_logout.php" style="margin-top:auto; color:var(--danger);">🚪 Logout</a>
</div>

    <div class="main-content">
        <h1>Select Event for Punch-In</h1>
        <p>Click on an event to start marking student attendance.</p>

        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="event-card">
            <div>
                <h3 style="margin:0;"><?php echo $row['event_name']; ?></h3>
                <small style="color:#666;">Date: <?php echo $row['event_date']; ?></small>
            </div>
            <a href="mark_attendance.php?event_id=<?php echo $row['id']; ?>" class="btn-checkin">Start Punch-In →</a>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>