<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Delete the session cookie
if (isset($_COOKIE['user_session'])) {
    setcookie('user_session', '', time() - 3600, "/", "", false, true); // Expire the cookie
}

// Redirect to the login page
header("Location: login.php");
exit();
?>
