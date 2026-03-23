<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])) exit();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Attendance_Report.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add the column headers to the CSV
fputcsv($output, ['Student Name', 'Reg No', 'Event', 'Status']);

// Logic: Check if a specific event was requested
$where_clause = "";
if(isset($_GET['event_id']) && !empty($_GET['event_id'])){
    $event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
    $where_clause = " WHERE events.id = '$event_id'";
}

$query = "SELECT students.name, students.reg_no, events.event_name, bookings.is_present 
          FROM bookings 
          JOIN students ON bookings.student_id = students.id 
          JOIN events ON bookings.event_id = events.id" . $where_clause;

$result = mysqli_query($conn, $query);

// Loop through the data and write each row to the CSV
while($row = mysqli_fetch_assoc($result)){
    $status = ($row['is_present'] == 1) ? "Present" : "Absent";
    
    // Add row to CSV
    fputcsv($output, [
        $row['name'],
        $row['reg_no'],
        $row['event_name'],
        $status
    ]);
}

fclose($output);
exit();
?>