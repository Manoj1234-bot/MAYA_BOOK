
<?php
include "../includes/db.php";

$id = $_GET['id'];

$query = "UPDATE students SET status='approved' WHERE id='$id'";

mysqli_query($conn,$query);

header("Location: approve_students.php");

?>