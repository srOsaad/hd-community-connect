<?php
/*
auth.inc: protects staff-only pages. Put require_once "auth.inc"; on the first line.
Visitors who aren't logged in go to stafflogin.php. Logs out after 30 minutes of inactivity.
*/

require_once "session.inc";

// Inactive too long: end the session and ask to log in again
if (isset($_SESSION["last_activity"]) && time() - $_SESSION["last_activity"] > SESSION_TIMEOUT) {
    end_staff_session();
    header("Location: stafflogin.php?expired=1");
    exit();
}

// Not logged in: send to the login page
if (!isset($_SESSION["username"]) || ($_SESSION["role"] ?? "") !== "staff") {
    header("Location: stafflogin.php");
    exit();
}

// Logged in: record this visit for the inactivity timer
$_SESSION["last_activity"] = time();
?>