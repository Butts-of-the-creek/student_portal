<?php
// --- LOGOUT SCRIPT ---
// This script securely ends the user's session.

// Initialize the session.
session_start();

// Unset all of the session variables.
// $_SESSION = array(); is a quick way to clear all session data.
$_SESSION = [];

// Destroy the session cookie.
// This will completely destroy the session, not just the session data!
session_destroy();

// Redirect the user to the login page.
// The user is now logged out and must log in again to access protected pages.
header("location: login.php");
exit;
?>
