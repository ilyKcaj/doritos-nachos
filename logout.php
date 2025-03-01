<?php
session_start();

// Set a session message for successful logout
$_SESSION['logout_success'] = "Successfully logged out!";

// Destroy all session data AFTER setting the message
session_unset();
session_destroy();

// Redirect to the login page
header("Location: index.php");
exit();
?>