<?php
session_start();

/*1800 seconds is 30 min*/
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