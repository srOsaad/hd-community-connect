<?php
/*
Helps with a safety check of data transfer between frontend and backend
*/

date_default_timezone_set("Australia/Melbourne");

require_once "validation.inc";
require_once "security.inc";

// Clean user input (form or URL): strip slashes, trim, escape HTML.
function clean_input($data) {
    if (!is_string($data)) {
        return "";
    }
    $data = stripslashes($data);
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    return $data;
}

// Escape database text before printing it, for team: echo "<h2>" . e($row["title"]) . "</h2>";
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

// The form data was already escaped by clean_input() before saving,
// so we undo that first, otherwise "&" would show up as "&amp;" on the page
function show($value) {
    return e(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

// Reads one text value from the URL or a form. If it's missing (or someone sends an array), we just get ""
function field($source, $key) {
    return (isset($source[$key]) && is_string($source[$key])) ? clean_input($source[$key]) : "";
}

function post_value($key) {
    return field($_POST, $key);
}

function text_length($value) {
    return mb_strlen(str_replace("\r\n", "\n", html_entity_decode((string) $value, ENT_QUOTES, "UTF-8")));
}

function like_pattern($text) {
    return "%" . addcslashes($text, "\\%_") . "%";
}

function show_errors($messages) {
    if (!$messages) {
        return;
    }
    echo "<div class=\"message message-error\" role=\"alert\">\n<ul>\n";
    foreach ($messages as $message) {
        echo "<li>" . e($message) . "</li>\n";
    }
    echo "</ul>\n</div>\n";
}
?>
