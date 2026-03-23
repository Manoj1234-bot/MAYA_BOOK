<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$search = "";

if(isset($_GET['search'])){
    $search = trim(mysqli_real_escape_string($conn,$_GET['search']));
}

$query = "SELECT 
            students.name,
            students.reg_no,
            events.event_name,
            bookings.seat_no,
            bookings.booking_ref,
            bookings.is_present,
            bookings.booking_time  /* ADDED THIS */
          FROM bookings
          JOIN students ON bookings.student_id = students.id
          JOIN events ON bookings.event_id = events.id";

if(!empty($search)){
    $query .= " WHERE 
                bookings.booking_ref LIKE '%$search%' OR
                students.name LIKE '%$search%' OR
                students.reg_no LIKE '%$search%' OR
                events.event_name LIKE '%$search%'";
}

$query .= " ORDER BY events.event_name ASC, bookings.seat_no ASC";

$result = mysqli_query($conn,$query);

if(!$result){
    die("Query Error: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Bookings</title>

<style>

:root{
--primary:#2c3e50;
--accent:#3498db;
--bg:#f8f9fa;
}

body{
font-family:'Segoe UI',sans-serif;
margin:0;
display:flex;
background:var(--bg);
height:100vh;
}

.sidebar{
width:250px;
background:var(--primary);
color:white;
padding:20px;
display:flex;
flex-direction:column;
}

.sidebar h2{
font-size:18px;
text-transform:uppercase;
border-bottom:1px solid #555;
padding-bottom:20px;
margin-bottom:20px;
}

.sidebar a{
color:#bdc3c7;
text-decoration:none;
padding:12px;
margin:5px 0;
border-radius:4px;
}

.sidebar a:hover,
.sidebar a.active{
background:#34495e;
color:white;
}

.main-content{
flex-grow:1;
padding:40px;
overflow-y:auto;
}

.header-flex{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

.search-box{
display:flex;
gap:10px;
background:white;
padding:10px;
border-radius:8px;
box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.search-box input{
padding:10px;
border:1px solid #ddd;
border-radius:4px;
width:250px;
outline:none;
}

.search-box button{
background:var(--accent);
color:white;
border:none;
padding:10px 20px;
border-radius:4px;
cursor:pointer;
font-weight:bold;
}

.table-container{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

table{
width:100%;
border-collapse:collapse;
}

th{
text-align:left;
background:#f4f7f6;
padding:15px;
color:var(--primary);
border-bottom:2px solid #ddd;
}

td{
padding:15px;
border-bottom:1px solid #eee;
color:#444;
}

.ref-code{
font-family:'Courier New';
background:#f0f4f8;
padding:5px 10px;
border-radius:4px;
font-weight:bold;
border:1px solid #d1d9e0;
}

.seat-badge{
background:#e1f5fe;
color:#0288d1;
padding:4px 10px;
border-radius:20px;
font-weight:bold;
font-size:14px;
}

.event-tag{
color:var(--accent);
font-weight:600;
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

<div class="header-flex">

<div>
<h1>Event Bookings</h1>
<p>Verify attendance and registration details.</p>
</div>

<div style="display:flex;gap:15px;">



<form method="GET" class="search-box">

<input type="text" name="search"
placeholder="Search..."
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">Search</button>

<?php if(!empty($search)){ ?>

<a href="view_bookings.php"
style="align-self:center;color:#999;text-decoration:none;">
Clear
</a>

<?php } ?>

</form>

</div>

</div>

<div class="table-container">

<?php if(mysqli_num_rows($result)>0){ ?>

<table>

<thead>

<thead>
    <tr>
        <th>Ref Code</th>
        <th>Student Name</th>
        <th>Reg No</th>
        <th>Event</th>
        <th>Booking Date & Time</th> <th>Attendance</th>
        
    </tr>
</thead>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td>
    <span class="ref-code">
        <?php echo htmlspecialchars($row['booking_ref']); ?>
    </span>
</td>

<td>
    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
</td>

<td>
    <?php echo htmlspecialchars($row['reg_no']); ?>
</td>

<td>
    <span class="event-tag">
        <?php echo htmlspecialchars($row['event_name']); ?>
    </span>
</td>

<td>
    <div style="font-size: 13px; color: #555;">
        <span style="display:block; font-weight:600;">
            <?php echo date('d M Y', strtotime($row['booking_time'])); ?>
        </span>
        <span style="font-size: 11px; color: #7f8c8d;">
            🕒 <?php echo date('h:i A', strtotime($row['booking_time'])); ?>
        </span>
    </div>
</td>
<td>
    <?php if($row['is_present']==1){ ?>
        <span style="color:#27ae60;font-weight:bold;">✅ Present</span>
    <?php } else { ?>
        <span style="color:#95a5a6;">❌ Absent</span>
    <?php } ?>
</td>
</tr>

<?php } ?>

</tbody>

</table>

<?php } else { ?>

<div style="text-align:center;padding:40px;">
No bookings found.
</div>

<?php } ?>

</div>

</div>

</body>
</html>