<?php
session_start();
// Use ../ to go back to the maya_book folder, then into includes
include "../includes/db.php"; 

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);

// Final check to prevent duplicate entries
$check = mysqli_query($conn, "SELECT * FROM waitlist WHERE event_id='$event_id' AND student_id='$student_id'");

if (mysqli_num_rows($check) == 0) {
    $query = "INSERT INTO waitlist (event_id, student_id) VALUES ('$event_id', '$student_id')";
    mysqli_query($conn, $query);
}

header("Location: dashboard.php?msg=Waitlisted");
exit();
?>