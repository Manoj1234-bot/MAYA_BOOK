<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --light: #f8f9fa; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--light); height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: var(--primary); color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 1.2rem; text-transform: uppercase; border-bottom: 1px solid #555; padding-bottom: 20px; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px; margin: 5px 0; border-radius: 4px; transition: 0.3s; }
        .sidebar a:hover { background: #34495e; color: white; }
        
        /* Main Content */
        .main-content { flex-grow: 1; padding: 40px; overflow-y: auto; }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-top: 20px;
        }
        .card { 
            background: white; padding: 25px; border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; 
            text-decoration: none; color: #333; transition: 0.2s;
            border-top: 4px solid var(--accent);
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card h3 { margin: 10px 0; font-size: 1.1rem; }
        .card p { color: #777; font-size: 0.9rem; }
        .icon { font-size: 2rem; display: block; margin-bottom: 10px; }
        

        
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
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h1>
        <p>Manage your events and student approvals from here.</p>
    </div>

    <div class="stats-grid">
        <a href="attendance_list.php" class="card" style="border-top-color: #f39c12;">
            <span class="icon">📍</span>
            <h3>Mark Attendance</h3>
            <p>Punch in students for specific events.</p>
        </a>
        <a href="approve_students.php" class="card">
            <span class="icon">✅</span>
            <h3>Student Approvals</h3>
            <p>Review and verify pending registrations.</p>
        </a>
        <a href="create_event.php" class="card" style="border-top-color: #c12;">
            <span class="icon">➕</span>
            <h3>New Event</h3>
            <p>Set up a new auditorium event.</p>
        </a>
        <a href="view_bookings.php" class="card">
            <span class="icon">📋</span>
            <h3>Event Bookings</h3>
            <p>Track seats and attendee lists.</p>
        </a>
         <a href="manage_events.php" class="card">
            <span class="icon">🔖</span>
            <h3>Manage events</h3>
            <p>Track the events.</p>
        </a>
        <a href="manage_approvals.php" class="card">
            <span class="icon">🔑</span>
            <h3>approve required students </h3>
            <p>approve your students</p>
        </a>
        <div style="position: fixed; bottom: 20px; right: 20px; text-align: right; background: rgba(255, 255, 255, 0.9); padding: 10px 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #ddd; z-index: 9999;">
    <div style="font-weight: bold; font-size: 1rem; color: var(--primary);">Maya Book</div>
    <div style="font-size: 0.7rem; color: #95a5a6; text-transform: uppercase; letter-spacing: 0.5px;">Developed by MANOJ S</div>
</div>
    </div>
</div>

</body>
</html>