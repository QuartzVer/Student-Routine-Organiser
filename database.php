<?php
$con = mysqli_connect("localhost","root","","student_routine");

//check for connection errors
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
  
?>
