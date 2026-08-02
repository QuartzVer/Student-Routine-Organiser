<?php
require('auth.php');

//check if it is admin
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}


include "layout.php";

?>

