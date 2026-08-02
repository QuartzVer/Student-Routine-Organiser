<?php
// Setup DB connection (host, username, password, database name)
$con = mysqli_connect("localhost","root","","student_routine");

// Check for connection errors
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
  
?>
