<?php
session_start();

/*
Session timeout
30 minutes = 1800 seconds
*/
$timeout_duration = 1800;

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Check inactivity */
if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $timeout_duration
) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();
}

/* Update last activity time */
$_SESSION['last_activity'] = time();
?>