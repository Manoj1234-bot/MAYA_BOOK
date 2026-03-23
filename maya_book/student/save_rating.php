<?php
session_start();
include "../includes/db.php";

if(isset($_POST['booking_id']) && isset($_POST['stars'])){
    $bid = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $stars = mysqli_real_escape_string($conn, $_POST['stars']);
    $sid = $_SESSION['student_id'];

    // Update only if it belongs to this student
    $query = "UPDATE bookings SET rating = '$stars' WHERE id = '$bid' AND student_id = '$sid'";
    if(mysqli_query($conn, $query)){
        echo "success";
    }
}
?>