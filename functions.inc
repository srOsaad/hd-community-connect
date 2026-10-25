<?php
/*
Helps with a safety check of data transfer between frontend and backend
*/

date_default_timezone_set("Australia/Melbourne");

# Clean user input (form or URL): trim and escape HTML.
function clean_input($data) {
    if (!is_string($data)) {
        return "";
    }
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
    return $data;
}

# Escape database text before printing it, for team: echo "<h2>" . e($row["title"]) . "</h2>";
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

# The form data was already escaped by clean_input() before saving,
# so we undo that first, otherwise "&" would show up as "&amp;" on the page
function show($value) {
    return e(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

function post_value($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? clean_input($_POST[$key]) : "";
}

function text_length($value) {
    return mb_strlen(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

function skill_options() {
    return ["Problem solving", "Communication", "Teamwork"];
}

function allowed_skills($values) {
    if (!is_array($values)) {
        return [];
    }
    return array_values(array_intersect(skill_options(), array_filter($values, "is_string")));
}

function like_pattern($text) {
    return "%" . addcslashes($text, "\\%_") . "%";
}
?>
