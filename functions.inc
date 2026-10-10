<?php
/*
Helps with a safety check of data transfer between frontend and backend
*/

# Clean user input (form or URL): trim, strip slashes, escape HTML.
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
    return $data;
}

# Escape database text before printing it, for team: echo "<h2>" . e($row["title"]) . "</h2>";
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>