<?php
/*
auth.inc: protects staff-only pages. Put require_once "auth.inc"; on the first line.
Visitors who aren't logged in go to stafflogin.php. Logs out after 30 minutes of inactivity.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_timeout = 30 * 60; # 30 minutes, in seconds

# Inactive too long: end the session and ask to log in again
if (isset($_SESSION["last_activity"]) && time() - $_SESSION["last_activity"] > $session_timeout) {
    session_unset();
    session_destroy();
    header("Location: stafflogin.php?expired=1");
    exit();
}

# Not logged in: send to the login page
if (!isset($_SESSION["username"]) || ($_SESSION["role"] ?? "") !== "staff") {
    header("Location: stafflogin.php");
    exit();
}

# Logged in: record this visit for the inactivity timer
$_SESSION["last_activity"] = time();
?>