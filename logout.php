<?php
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
 $params = session_get_cookie_params();
 setcookie(
 session_name(),
 '',
 time() - 42000, 


 //session cookie destroy
 $params["path"],
 $params["domain"],
 $params["secure"],
 $params["httponly"]
 );
}
//data in server destroyed
session_destroy();
$cookie_name = "user";
//user cookie destroy
setcookie($cookie_name, "", time() - 3600, "/");
header("Location: login.php");
exit();
?>