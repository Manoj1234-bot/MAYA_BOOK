<?php
session_start();
include "../includes/db.php";

// 1. Security Check
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$message = "";

// 2. Handle Deletion
if(isset($_GET['delete_id'])){
    $event_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Step A: Delete all bookings for this event first
    $delete_bookings = "DELETE FROM bookings WHERE event_id = '$event_id'";
    mysqli_query($conn, $delete_bookings);

    // Step B: Delete the event itself
    $delete_event = "DELETE FROM events WHERE id = '$event_id'";
    
    if(mysqli_query($conn, $delete_event)){
        $message = "✅ Event and all associated bookings deleted successfully.";
    } else {
        $message = "❌ Error deleting event: " . mysqli_error($conn);
    }
}

// 3. Fetch all events
$query = "SELECT e.*, (SELECT COUNT(id) FROM bookings WHERE event_id = e.id) as total_booked 
          FROM events e 
          ORDER BY e.event_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - Maya Admin</title>
    <style>
        :root { --primary: #5e43f3; --dark: #1a1a2e; --bg: #f4f7f6; --danger: #ff7675; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark); color: white; min-height: 100vh; padding: 30px 20px; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); margin-bottom: 40px; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active { background: var(--primary); color: white; }

        /* Main Content */
        .main-content { flex-grow: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; color: #7f8c8d; }
        td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }

        .btn-add { background: var(--primary); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-delete { color: var(--danger); text-decoration: none; font-weight: 600; padding: 5px 10px; border: 1px solid var(--danger); border-radius: 5px; transition: 0.3s; }
        .btn-delete:hover { background: var(--danger); color: white; }
        
        .badge { background: #eee; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: 700; }
        .badge-booked { background: #e0e6ff; color: var(--primary); }

        .btn-analytics { 
    color: var(--primary); 
    text-decoration: none; 
    font-weight: 600; 
    padding: 5px 10px; 
    border: 1px solid var(--primary); 
    border-radius: 5px; 
    margin-right: 5px; 
    transition: 0.3s; 
    font-size: 13px;
}
.btn-analytics:hover { 
    background: var(--primary); 
    color: white; 
}
.btn-quiz {
    background: #5e43f3;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    margin-right: 5px;
}
.btn-quiz:hover {
    background: #4e36d3;
}
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
        <div>
            <h1>Event Management</h1>
            <p>View, track bookings, and remove events.</p>
        </div>
        <a href="add_event.php" class="btn-add">+ Create New Event</a>
    </div>

    <?php if($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Bookings</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($row['event_name']); ?></b></td>
                        <td><?php echo date('d M Y', strtotime($row['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['venue']); ?></td>
                        <td>
                            <span class="badge badge-booked">
                                <?php echo $row['total_booked']; ?> Seats Booked
                            </span>
                        </td>
                       <td>
    <a href="admin_event_analytics.php?id=<?php echo $row['id']; ?>" class="btn-analytics">
        📊 Analytics
    </a>
    
    <a href="admin_quiz.php?event_id=<?php echo $row['id']; ?>" class="btn-quiz">
        🎮 Start Quiz
    </a>


    <a href="?delete_id=<?php echo $row['id']; ?>" 
       class="btn-delete" 
       onclick="return confirm('WARNING: Deleting this event will also delete ALL student bookings for it. Proceed?')">
       Delete
    </a>
</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>