<?php
/*
db_connect.inc: opens the database connection ($conn) for any page that needs it.
Usage: require_once "db_connect.inc"; then check if (!$conn) before using it.
*/

require_once "settings.php";

// Return false on errors instead of throwing exceptions
mysqli_report(MYSQLI_REPORT_OFF);

// @ hides PHP's raw warning so database details are never shown on the page (Added for security purpose)
$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
$db_error_message = "Sorry, we can't reach our database right now. Please try again later.";

if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
} else {
    // Real error goes to the server log for developers; users only see $db_error_message
    error_log("Database connection failed: " . mysqli_connect_error());
}
?>